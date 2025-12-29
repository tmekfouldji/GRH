<?php

namespace App\Models;

use App\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employe extends Model
{
    use HasFactory, TracksUserActions;

    protected $table = 'employes';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'email',
        'telephone',
        'poste',
        'categorie',
        'echelon',
        'departement',
        'date_embauche',
        'date_naissance',
        'salaire_base',
        'prime_transport_defaut',
        'prime_panier_defaut',
        'type_contrat',
        'statut',
        'adresse',
        'cin',
        'cnss',
        'numero_cnas',
        'mode_paiement',
        'rib',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'date_naissance' => 'date',
        'salaire_base' => 'decimal:2',
        'prime_transport_defaut' => 'decimal:2',
        'prime_panier_defaut' => 'decimal:2',
    ];

    protected $appends = ['nom_complet', 'anciennete', 'salaire_preview'];

    // ==================== RELATIONS ====================

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    public function fichesPaie()
    {
        return $this->hasMany(FichePaie::class);
    }

    // ==================== ACCESSEURS ====================

    public function getNomCompletAttribute()
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getStatutBadgeAttribute()
    {
        return match($this->statut) {
            'actif' => '<span class="badge bg-success">Actif</span>',
            'inactif' => '<span class="badge bg-danger">Inactif</span>',
            'conge' => '<span class="badge bg-warning">En congé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }

    /**
     * Calculer l'ancienneté en années
     */
    public function getAncienneteAttribute()
    {
        if (!$this->date_embauche) {
            return 0;
        }
        return $this->date_embauche->diffInYears(now());
    }

    /**
     * Prime d'ancienneté (désactivée - toujours 0)
     */
    public function getPrimeAncienneteAttribute()
    {
        return 0;
    }

    /**
     * Prévisualisation du salaire net (pour affichage rapide)
     */
    public function getSalairePreviewAttribute()
    {
        $preview = $this->calculerSalairePreview();
        return [
            'salaire_brut' => $preview['salaire_brut'],
            'cotisation_cnas' => $preview['cotisation_cnas'],
            'irg' => $preview['irg'],
            'salaire_net' => $preview['salaire_net'],
        ];
    }

    // ==================== MÉTHODES DE CALCUL ====================

    /**
     * Calculer une prévisualisation du salaire avec les primes par défaut
     */
    public function calculerSalairePreview($primes_supplementaires = 0)
    {
        $salaire_base = $this->salaire_base ?? 0;
        $prime_transport = $this->prime_transport_defaut ?? 0;
        $prime_panier = $this->prime_panier_defaut ?? 0;
        
        // Salaire brut (sans prime d'ancienneté)
        $salaire_brut = $salaire_base + $prime_transport + $prime_panier + $primes_supplementaires;
        
        // CNAS 9%
        $cotisation_cnas = $salaire_brut * 0.09;
        
        // SNI (Salaire Net Imposable)
        $sni = $salaire_brut - $cotisation_cnas;
        
        // IRG
        $irg = $this->calculerIRG($sni);
        
        // Salaire net
        $salaire_net = $salaire_brut - $cotisation_cnas - $irg;
        
        return [
            'salaire_base' => round($salaire_base, 2),
            'prime_transport' => round($prime_transport, 2),
            'prime_panier' => round($prime_panier, 2),
            'salaire_brut' => round($salaire_brut, 2),
            'cotisation_cnas' => round($cotisation_cnas, 2),
            'sni' => round($sni, 2),
            'irg' => round($irg, 2),
            'total_deductions' => round($cotisation_cnas + $irg, 2),
            'salaire_net' => round($salaire_net, 2),
        ];
    }

    /**
     * Générer une fiche de paie pour un mois donné
     */
    public function genererFichePaie($mois, $annee, $options = [])
    {
        // Vérifier si une fiche existe déjà
        $existing = $this->fichesPaie()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->first();
            
        if ($existing) {
            return $existing;
        }

        // Calculer les heures du mois
        $pointages = $this->pointages()
            ->whereMonth('date_pointage', $mois)
            ->whereYear('date_pointage', $annee)
            ->get();

        $heures_normales = $pointages->sum('heures_travaillees');
        $heures_sup = $pointages->sum('heures_supplementaires');

        // Créer la fiche de paie
        $fichePaie = new FichePaie([
            'employe_id' => $this->id,
            'mois' => $mois,
            'annee' => $annee,
            'salaire_base' => $this->salaire_base ?? 0,
            'heures_normales' => $heures_normales,
            'heures_supplementaires' => $heures_sup,
            'prime_anciennete' => 0,
            'prime_rendement' => $options['prime_rendement'] ?? 0,
            'prime_transport' => $this->prime_transport_defaut ?? 0,
            'autres_primes' => ($this->prime_panier_defaut ?? 0) + ($options['autres_primes'] ?? 0),
            'autres_deductions' => $options['autres_deductions'] ?? 0,
        ]);

        // Calculate presences from pointages for this specific month/year
        $fichePaie->calculerPresences();
        // Calculate salary based on presence ratio
        $fichePaie->calculerSalaire();
        // Now save with all calculated values
        $fichePaie->save();

        return $fichePaie;
    }

    /**
     * Calculer l'IRG (copie simplifiée de FichePaie pour prévisualisation)
     */
    private function calculerIRG($salaire_imposable)
    {
        if ($salaire_imposable <= 30000) {
            return 0;
        }
        
        $irg_brut = $this->calculerIRGBareme($salaire_imposable);
        
        if ($salaire_imposable > 30000 && $salaire_imposable < 35000) {
            $irg_avec_abattement = $this->appliquerAbattement40($irg_brut);
            $irg_final = ($irg_avec_abattement * (137 / 51)) - (27925 / 8);
            return max(0, round($irg_final, 2));
        }
        
        return round($this->appliquerAbattement40($irg_brut), 2);
    }

    private function calculerIRGBareme($salaire_mensuel)
    {
        $irg = 0;
        
        if ($salaire_mensuel <= 20000) return 0;
        
        if ($salaire_mensuel > 20000) {
            $irg += (min($salaire_mensuel, 40000) - 20000) * 0.23;
        }
        if ($salaire_mensuel > 40000) {
            $irg += (min($salaire_mensuel, 80000) - 40000) * 0.27;
        }
        if ($salaire_mensuel > 80000) {
            $irg += (min($salaire_mensuel, 160000) - 80000) * 0.30;
        }
        if ($salaire_mensuel > 160000) {
            $irg += (min($salaire_mensuel, 320000) - 160000) * 0.33;
        }
        if ($salaire_mensuel > 320000) {
            $irg += ($salaire_mensuel - 320000) * 0.35;
        }
        
        return $irg;
    }

    private function appliquerAbattement40($irg_brut)
    {
        $abattement = max(1000, min($irg_brut * 0.40, 1500));
        return max(0, $irg_brut - $abattement);
    }

    // ==================== SCOPES ====================

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeParDepartement($query, $departement)
    {
        return $query->where('departement', $departement);
    }
}
