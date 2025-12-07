<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'date_pointage',
        'heure_entree',
        'heure_sortie',
        'heures_travaillees',
        'heures_supplementaires',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'date_pointage' => 'date',
        'heure_entree' => 'datetime',
        'heure_sortie' => 'datetime',
        'heures_travaillees' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function getStatutBadgeAttribute()
    {
        return match($this->statut) {
            'present' => '<span class="badge bg-success">Présent</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'retard' => '<span class="badge bg-warning">Retard</span>',
            'conge' => '<span class="badge bg-info">Congé</span>',
            'maladie' => '<span class="badge bg-secondary">Maladie</span>',
            'mission' => '<span class="badge bg-primary">Mission</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }

    /**
     * Calculer les heures travaillées à partir de datetime complets
     * Gère les erreurs humaines et les travaux de nuit
     */
    public static function calculerHeures($heure_entree, $heure_sortie)
    {
        if (!$heure_entree || !$heure_sortie) {
            return [0, 0];
        }

        $entree = \Carbon\Carbon::parse($heure_entree);
        $sortie = \Carbon\Carbon::parse($heure_sortie);
        
        // Calculer la différence (peut être négative si erreur humaine)
        $minutes = $entree->diffInMinutes($sortie, false);
        
        // Si négatif (sortie avant entrée), prendre la valeur absolue
        // C'est une erreur humaine, on calcule quand même
        $heures = abs($minutes) / 60;
        
        // Limiter à 24h max (protection contre erreurs)
        $heures = min($heures, 24);
        
        // Heures normales (max 8h) et supplémentaires
        $heures_normales = min($heures, 8);
        $heures_sup = max(0, $heures - 8);

        return [round($heures_normales, 2), round($heures_sup, 2)];
    }

    /**
     * Accesseur pour afficher l'heure d'entrée formatée
     */
    public function getHeureEntreeFormateeAttribute()
    {
        return $this->heure_entree ? $this->heure_entree->format('H:i') : null;
    }

    /**
     * Accesseur pour afficher l'heure de sortie formatée
     */
    public function getHeureSortieFormateeAttribute()
    {
        return $this->heure_sortie ? $this->heure_sortie->format('H:i') : null;
    }
}
