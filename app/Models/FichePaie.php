<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichePaie extends Model
{
    use HasFactory;

    protected $table = 'fiches_paie';

    protected $fillable = [
        'employe_id',
        'mois',
        'annee',
        'salaire_base',
        'heures_normales',
        'heures_supplementaires',
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
    ];

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

    // Calculer les cotisations et le salaire net
    public function calculerSalaire()
    {
        // Total des primes
        $total_primes = $this->prime_anciennete + $this->prime_rendement + 
                        $this->prime_transport + $this->autres_primes;
        
        // Salaire brut
        $this->salaire_brut = $this->salaire_base + $total_primes;
        
        // Cotisations (taux marocains approximatifs)
        $this->cotisation_cnss = $this->salaire_brut * 0.0448; // 4.48%
        $this->cotisation_amo = $this->salaire_brut * 0.0226;  // 2.26%
        
        // IR (simplifié - barème progressif)
        $salaire_imposable = $this->salaire_brut - $this->cotisation_cnss - $this->cotisation_amo;
        $this->ir = $this->calculerIR($salaire_imposable);
        
        // Total déductions
        $this->total_deductions = $this->cotisation_cnss + $this->cotisation_amo + 
                                  $this->ir + $this->autres_deductions;
        
        // Salaire net
        $this->salaire_net = $this->salaire_brut - $this->total_deductions;
        
        return $this;
    }

    private function calculerIR($salaire_imposable)
    {
        // Barème IR simplifié (mensuel)
        if ($salaire_imposable <= 2500) return 0;
        if ($salaire_imposable <= 4166) return ($salaire_imposable - 2500) * 0.10;
        if ($salaire_imposable <= 5000) return 166.6 + ($salaire_imposable - 4166) * 0.20;
        if ($salaire_imposable <= 6666) return 333.4 + ($salaire_imposable - 5000) * 0.30;
        if ($salaire_imposable <= 15000) return 833.2 + ($salaire_imposable - 6666) * 0.34;
        return 3666.76 + ($salaire_imposable - 15000) * 0.38;
    }
}
