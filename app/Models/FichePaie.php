<?php

namespace App\Models;

use App\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichePaie extends Model
{
    use HasFactory, TracksUserActions;

    protected $table = 'fiches_paie';

    protected $fillable = [
        'paie_mensuelle_id',
        'employe_id',
        'mois',
        'annee',
        'salaire_base',
        'heures_normales',
        'heures_supplementaires',
        'jours_travailles',
        'jours_absence',
        'jours_justifies',
        'prime_anciennete',
        'prime_rendement',
        'prime_transport',
        'autres_primes',
        'salaire_brut',
        'cotisation_cnss',
        'cotisation_amo',
        'ir',
        'autres_deductions',
        'total_deductions',
        'salaire_net',
        'statut',
        'statut_validation',
        'date_validation',
        'valide_par',
        'notes_validation',
        'ajustement_heures',
        'motif_ajustement',
        'statut_reception',
        'date_remise',
        'remis_par',
        'date_paiement',
    ];

    protected $casts = [
        'salaire_base' => 'decimal:2',
        'heures_normales' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
        'prime_anciennete' => 'decimal:2',
        'prime_rendement' => 'decimal:2',
        'prime_transport' => 'decimal:2',
        'autres_primes' => 'decimal:2',
        'salaire_brut' => 'decimal:2',
        'cotisation_cnss' => 'decimal:2',
        'cotisation_amo' => 'decimal:2',
        'ir' => 'decimal:2',
        'autres_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'date_paiement' => 'date',
        'date_remise' => 'datetime',
        'date_validation' => 'datetime',
        'ajustement_heures' => 'decimal:2',
    ];

    protected $appends = ['statut_validation_label'];

    public function paieMensuelle()
    {
        return $this->belongsTo(PaieMensuelle::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function getPeriodeAttribute()
    {
        $mois_noms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $mois_noms[$this->mois] . ' ' . $this->annee;
    }

    public function getStatutBadgeAttribute()
    {
        return match($this->statut) {
            'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
            'valide' => '<span class="badge bg-warning">Validé</span>',
            'paye' => '<span class="badge bg-success">Payé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }

    public function getStatutValidationLabelAttribute()
    {
        return match($this->statut_validation) {
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'ajuste' => 'Ajusté',
            default => $this->statut_validation,
        };
    }

    /**
     * Récupérer les pointages du mois pour cet employé
     */
    public function getPointagesDuMois()
    {
        return Pointage::where('employe_id', $this->employe_id)
            ->whereMonth('date_pointage', $this->mois)
            ->whereYear('date_pointage', $this->annee)
            ->orderBy('date_pointage')
            ->get();
    }

    /**
     * Calculer le résumé des présences depuis les pointages
     */
    public function calculerPresences()
    {
        $pointages = $this->getPointagesDuMois();
        
        $jours_travailles = $pointages->where('statut', 'present')->count();
        $jours_absence = $pointages->where('statut', 'absent')->count();
        $jours_justifies = $pointages->whereIn('statut', ['conge', 'maladie', 'mission'])->count();
        
        $heures_normales = $pointages->sum('heures_travaillees');
        $heures_supplementaires = $pointages->sum('heures_supplementaires');
        
        $this->jours_travailles = $jours_travailles;
        $this->jours_absence = $jours_absence;
        $this->jours_justifies = $jours_justifies;
        $this->heures_normales = $heures_normales;
        $this->heures_supplementaires = $heures_supplementaires;
        
        return $this;
    }

    /**
     * Valider les présences
     */
    public function validerPresences($validePar = null, $notes = null)
    {
        $this->statut_validation = 'valide';
        $this->date_validation = now();
        $this->valide_par = $validePar ?? 'Système';
        if ($notes) {
            $this->notes_validation = $notes;
        }
        $this->save();
        return $this;
    }

    /**
     * Appliquer un ajustement aux heures
     */
    public function ajusterHeures($ajustement, $motif, $validePar = null)
    {
        $this->ajustement_heures = $ajustement;
        $this->motif_ajustement = $motif;
        $this->statut_validation = 'ajuste';
        $this->date_validation = now();
        $this->valide_par = $validePar ?? 'Système';
        $this->save();
        
        // Recalculer le salaire avec l'ajustement
        $this->recalculerAvecAjustement();
        
        return $this;
    }

    /**
     * Recalculer le salaire en tenant compte des ajustements
     */
    public function recalculerAvecAjustement()
    {
        // Ajouter les heures ajustées aux heures normales pour le calcul
        // (l'ajustement peut être positif ou négatif)
        $heures_effectives = $this->heures_normales + ($this->ajustement_heures ?? 0);
        
        // Recalculer le salaire
        $this->calculerSalaire();
        $this->save();
        
        return $this;
    }

    /**
     * Calculer les cotisations et le salaire net selon la législation algérienne
     * 
     * Références:
     * - CNAS: 9% cotisation salariale (25% patronale non déduite du salaire)
     * - IRG: Barème progressif LF 2022 avec abattements
     */
    public function calculerSalaire()
    {
        // Total des primes
        $total_primes = $this->prime_anciennete + $this->prime_rendement + 
                        $this->prime_transport + $this->autres_primes;
        
        // Salaire brut = salaire de base + primes
        $this->salaire_brut = $this->salaire_base + $total_primes;
        
        // Cotisation CNAS salariale: 9% du salaire brut
        // (la cotisation patronale de 25% n'est pas déduite du salaire de l'employé)
        $this->cotisation_cnss = $this->salaire_brut * 0.09;
        
        // Pas de cotisation AMO séparée en Algérie (incluse dans CNAS)
        $this->cotisation_amo = 0;
        
        // Salaire Net Imposable (SNI) = Brut - Cotisations sociales
        $salaire_imposable = $this->salaire_brut - $this->cotisation_cnss;
        
        // IRG (Impôt sur le Revenu Global) selon barème algérien
        $this->ir = $this->calculerIRG($salaire_imposable);
        
        // Total déductions
        $this->total_deductions = $this->cotisation_cnss + $this->cotisation_amo + 
                                  $this->ir + $this->autres_deductions;
        
        // Salaire net à payer
        $this->salaire_net = $this->salaire_brut - $this->total_deductions;
        
        return $this;
    }

    /**
     * Calculer l'IRG selon le barème algérien (LF 2022)
     * 
     * Règles:
     * 1. Salaire ≤ 30,000 DZD: Exonération totale
     * 2. Salaire 30,001 - 35,000 DZD: Formule dégressive spéciale
     * 3. Salaire > 35,000 DZD: Barème progressif avec abattement 40%
     * 
     * Barème annuel:
     * - 0% sur 0 - 240,000 DZD
     * - 23% sur 240,001 - 480,000 DZD
     * - 27% sur 480,001 - 960,000 DZD
     * - 30% sur 960,001 - 1,920,000 DZD
     * - 33% sur 1,920,001 - 3,840,000 DZD
     * - 35% sur > 3,840,000 DZD
     */
    private function calculerIRG($salaire_imposable)
    {
        // Règle 1: Exonération totale pour salaires ≤ 30,000 DZD
        if ($salaire_imposable <= 30000) {
            return 0;
        }
        
        // Calculer l'IRG brut selon le barème mensuel
        $irg_brut = $this->calculerIRGBareme($salaire_imposable);
        
        // Règle 2: Salaires entre 30,001 et 35,000 DZD - formule dégressive
        if ($salaire_imposable > 30000 && $salaire_imposable < 35000) {
            // Appliquer d'abord l'abattement de 40% sur l'IRG brut
            $irg_avec_abattement = $this->appliquerAbattement40($irg_brut);
            // Formule spéciale: IRG = IRG(abattu) × (137/51) − (27925/8)
            $irg_final = ($irg_avec_abattement * (137 / 51)) - (27925 / 8);
            return max(0, round($irg_final, 2));
        }
        
        // Règle 3: Salaires ≥ 35,000 DZD - abattement standard de 40%
        $irg_final = $this->appliquerAbattement40($irg_brut);
        
        return round($irg_final, 2);
    }

    /**
     * Calculer l'IRG brut selon le barème progressif mensuel
     * (Tranches annuelles divisées par 12)
     */
    private function calculerIRGBareme($salaire_mensuel)
    {
        // Tranches mensuelles (tranches annuelles / 12)
        // 0% : 0 - 20,000 DZD (240,000 / 12)
        // 23%: 20,001 - 40,000 DZD (480,000 / 12)
        // 27%: 40,001 - 80,000 DZD (960,000 / 12)
        // 30%: 80,001 - 160,000 DZD (1,920,000 / 12)
        // 33%: 160,001 - 320,000 DZD (3,840,000 / 12)
        // 35%: > 320,000 DZD
        
        $irg = 0;
        
        if ($salaire_mensuel <= 20000) {
            return 0;
        }
        
        // Tranche 23%: 20,001 - 40,000
        if ($salaire_mensuel > 20000) {
            $tranche = min($salaire_mensuel, 40000) - 20000;
            $irg += $tranche * 0.23;
        }
        
        // Tranche 27%: 40,001 - 80,000
        if ($salaire_mensuel > 40000) {
            $tranche = min($salaire_mensuel, 80000) - 40000;
            $irg += $tranche * 0.27;
        }
        
        // Tranche 30%: 80,001 - 160,000
        if ($salaire_mensuel > 80000) {
            $tranche = min($salaire_mensuel, 160000) - 80000;
            $irg += $tranche * 0.30;
        }
        
        // Tranche 33%: 160,001 - 320,000
        if ($salaire_mensuel > 160000) {
            $tranche = min($salaire_mensuel, 320000) - 160000;
            $irg += $tranche * 0.33;
        }
        
        // Tranche 35%: > 320,000
        if ($salaire_mensuel > 320000) {
            $tranche = $salaire_mensuel - 320000;
            $irg += $tranche * 0.35;
        }
        
        return $irg;
    }

    /**
     * Appliquer l'abattement de 40% sur l'IRG
     * Avec plancher de 1,000 DZD/mois et plafond de 1,500 DZD/mois
     * (12,000 DZD/an et 18,000 DZD/an)
     */
    private function appliquerAbattement40($irg_brut)
    {
        // Calculer l'abattement de 40%
        $abattement = $irg_brut * 0.40;
        
        // Appliquer les bornes mensuelles (annuelles / 12)
        $abattement_min = 1000;  // 12,000 / 12
        $abattement_max = 1500;  // 18,000 / 12
        
        // L'abattement doit être entre le min et le max
        $abattement = max($abattement_min, min($abattement, $abattement_max));
        
        // IRG final = IRG brut - abattement
        // Mais l'abattement ne peut pas dépasser l'IRG brut
        $irg_final = max(0, $irg_brut - $abattement);
        
        return $irg_final;
    }
}
