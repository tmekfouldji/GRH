<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'type',
        'date_debut',
        'date_fin',
        'nombre_jours',
        'statut',
        'motif',
        'commentaire_responsable',
        'date_validation',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_validation' => 'date',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'annuel' => 'Congé annuel',
            'maladie' => 'Congé maladie',
            'maternite' => 'Congé maternité',
            'paternite' => 'Congé paternité',
            'sans_solde' => 'Congé sans solde',
            'exceptionnel' => 'Congé exceptionnel',
            default => 'Autre',
        };
    }

    public function getStatutBadgeAttribute()
    {
        return match($this->statut) {
            'en_attente' => '<span class="badge bg-warning">En attente</span>',
            'approuve' => '<span class="badge bg-success">Approuvé</span>',
            'refuse' => '<span class="badge bg-danger">Refusé</span>',
            'annule' => '<span class="badge bg-secondary">Annulé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }

    // Calculer le nombre de jours automatiquement
    public static function calculerNombreJours($date_debut, $date_fin)
    {
        $debut = \Carbon\Carbon::parse($date_debut);
        $fin = \Carbon\Carbon::parse($date_fin);
        
        return $debut->diffInDays($fin) + 1;
    }
}
