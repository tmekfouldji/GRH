<?php

namespace App\Models;

use App\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaieMensuelle extends Model
{
    use HasFactory, TracksUserActions;

    protected $table = 'paies_mensuelles';

    protected $fillable = [
        'reference',
        'mois',
        'annee',
        'nombre_employes',
        'total_salaires_base',
        'total_primes',
        'total_brut',
        'total_cotisations_cnas',
        'total_irg',
        'total_deductions',
        'total_net',
        'total_charges_patronales',
        'cout_total_employeur',
        'statut',
        'statut_cnas',
        'statut_irg',
        'date_creation',
        'date_validation',
        'date_cloture',
        'notes',
    ];

    protected $casts = [
        'total_salaires_base' => 'decimal:2',
        'total_primes' => 'decimal:2',
        'total_brut' => 'decimal:2',
        'total_cotisations_cnas' => 'decimal:2',
        'total_irg' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_charges_patronales' => 'decimal:2',
        'cout_total_employeur' => 'decimal:2',
        'date_creation' => 'date',
        'date_validation' => 'date',
        'date_cloture' => 'date',
    ];

    protected $appends = ['periode', 'statut_label', 'progression_paiement'];

    // ==================== RELATIONS ====================

    public function fichesPaie()
    {
        return $this->hasMany(FichePaie::class);
    }

    // ==================== ACCESSEURS ====================

    public function getPeriodeAttribute()
    {
        $mois_noms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $mois_noms[$this->mois] . ' ' . $this->annee;
    }

    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'valide' => 'Validé',
            'en_paiement' => 'En cours de paiement',
            'cloture' => 'Clôturé',
            default => $this->statut,
        };
    }

    public function getProgressionPaiementAttribute()
    {
        $total = $this->fichesPaie()->count();
        if ($total === 0) return 0;
        
        $remis = $this->fichesPaie()->where('statut_reception', '!=', 'en_attente')->count();
        return round(($remis / $total) * 100);
    }

    // ==================== MÉTHODES ====================

    /**
     * Générer la référence unique pour la paie mensuelle
     */
    public static function genererReference($mois, $annee)
    {
        return sprintf('PAIE-%04d-%02d', $annee, $mois);
    }

    /**
     * Générer la paie mensuelle avec toutes les fiches de paie
     */
    public static function genererPaieMensuelle($mois, $annee, $options = [])
    {
        // Vérifier si une paie existe déjà
        $existing = self::where('mois', $mois)->where('annee', $annee)->first();
        if ($existing) {
            return ['success' => false, 'message' => 'Une paie existe déjà pour cette période', 'paie' => $existing];
        }

        // Créer la paie mensuelle
        $paie = new self([
            'reference' => self::genererReference($mois, $annee),
            'mois' => $mois,
            'annee' => $annee,
            'date_creation' => now(),
            'statut' => 'brouillon',
        ]);
        $paie->save();

        // Récupérer tous les employés actifs
        $employes = Employe::where('statut', 'actif')->get();
        $count = 0;

        $errors = [];
        
        foreach ($employes as $employe) {
            // Vérifier que l'employé a un salaire de base défini
            if (!$employe->salaire_base || $employe->salaire_base <= 0) {
                $errors[] = $employe->nom_complet . ' (pas de salaire de base)';
                continue;
            }

            // Vérifier si une fiche existe déjà pour cet employé ce mois
            $ficheExistante = FichePaie::where('employe_id', $employe->id)
                ->where('mois', $mois)
                ->where('annee', $annee)
                ->first();

            if ($ficheExistante) {
                // Lier la fiche existante à cette paie
                $ficheExistante->paie_mensuelle_id = $paie->id;
                // Recalculate presences and salary for this specific month
                $ficheExistante->calculerPresences();
                $ficheExistante->calculerSalaire();
                $ficheExistante->save();
                $count++;
            } else {
                // Générer une nouvelle fiche
                $fiche = $employe->genererFichePaie($mois, $annee, $options);
                $fiche->paie_mensuelle_id = $paie->id;
                $fiche->save();
                $count++;
            }
        }
        
        // Si aucune fiche n'a pu être générée, supprimer la paie et retourner une erreur
        if ($count === 0 && count($errors) > 0) {
            $paie->delete();
            return [
                'success' => false, 
                'message' => 'Aucune fiche générée. Employés sans salaire: ' . implode(', ', $errors),
            ];
        }

        // Recalculer les totaux
        $paie->recalculerTotaux();

        // Message de succès avec avertissements si nécessaire
        $message = "{$count} fiches de paie générées";
        if (count($errors) > 0) {
            $message .= ". ⚠️ Employés ignorés (sans salaire): " . implode(', ', $errors);
        }

        return ['success' => true, 'message' => $message, 'paie' => $paie, 'warnings' => $errors];
    }

    /**
     * Recalculer tous les totaux de la paie mensuelle
     */
    public function recalculerTotaux()
    {
        $fiches = $this->fichesPaie()->with('employe')->get();

        $this->nombre_employes = $fiches->count();
        $this->total_salaires_base = $fiches->sum('salaire_base');
        $this->total_primes = $fiches->sum(function($f) {
            return $f->prime_anciennete + $f->prime_rendement + $f->prime_transport + $f->autres_primes;
        });
        $this->total_brut = $fiches->sum('salaire_brut');
        $this->total_cotisations_cnas = $fiches->sum('cotisation_cnss');
        $this->total_irg = $fiches->sum('ir');
        $this->total_deductions = $fiches->sum('total_deductions');
        $this->total_net = $fiches->sum('salaire_net');
        
        // Charges patronales (25% du brut)
        $this->total_charges_patronales = $this->total_brut * 0.25;
        $this->cout_total_employeur = $this->total_brut + $this->total_charges_patronales;

        $this->save();

        return $this;
    }

    /**
     * Valider la paie mensuelle
     */
    public function valider()
    {
        $this->statut = 'valide';
        $this->date_validation = now();
        $this->fichesPaie()->update(['statut' => 'valide']);
        $this->save();
        return $this;
    }

    /**
     * Passer en mode paiement
     */
    public function demarrerPaiement()
    {
        $this->statut = 'en_paiement';
        $this->save();
        return $this;
    }

    /**
     * Clôturer la paie mensuelle
     */
    public function cloturer()
    {
        $this->statut = 'cloture';
        $this->date_cloture = now();
        $this->fichesPaie()->update(['statut' => 'paye']);
        $this->save();
        return $this;
    }

    /**
     * Annuler la paie mensuelle (supprime les fiches non payées)
     */
    public function annuler()
    {
        if ($this->statut === 'cloture') {
            return false;
        }

        // Supprimer les fiches en brouillon liées
        $this->fichesPaie()->where('statut', 'brouillon')->delete();
        
        // Délier les autres fiches
        $this->fichesPaie()->update(['paie_mensuelle_id' => null]);
        
        // Supprimer la paie
        $this->delete();
        
        return true;
    }

    // ==================== SCOPES ====================

    public function scopeAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['brouillon', 'valide', 'en_paiement']);
    }
}
