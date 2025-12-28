<?php

namespace App\Models;

use App\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory, TracksUserActions;

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
     * Retourne [heures_totales, heures_supplementaires]
     */
    public static function calculerHeures($datetime_entree, $datetime_sortie)
    {
        if (!$datetime_entree || !$datetime_sortie) {
            return [0, 0];
        }

        $entree = \Carbon\Carbon::parse($datetime_entree);
        $sortie = \Carbon\Carbon::parse($datetime_sortie);
        
        // Si sortie est avant entrée (ex: entrée 23h, sortie 07h lendemain)
        // On ajoute un jour à la sortie
        if ($sortie->lt($entree)) {
            $sortie->addDay();
        }
        
        // Calculer la différence en minutes (toujours positive avec abs)
        $minutes = abs($entree->diffInMinutes($sortie));
        $heures_totales = $minutes / 60;
        
        // Limiter à 24h max (protection contre erreurs)
        $heures_totales = min($heures_totales, 24);
        
        // Heures supplémentaires = heures au-delà de 8h
        $heures_sup = max(0, $heures_totales - 8);

        return [round($heures_totales, 2), round($heures_sup, 2)];
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
