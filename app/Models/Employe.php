<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $table = 'employes';

    protected $fillable = [
        'matricule',    // AC-No. - Code employé (obligatoire)
        'nom',          // Nom (obligatoire)
        'prenom',       // Prénom (optionnel)
        'email',
        'telephone',
        'poste',
        'departement',
        'date_embauche',
        'date_naissance',
        'salaire_base',
        'type_contrat',
        'statut',
        'adresse',
        'cin',
        'cnss',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'date_naissance' => 'date',
        'salaire_base' => 'decimal:2',
    ];

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

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
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
}
