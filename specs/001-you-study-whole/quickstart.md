# Quickstart: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+, MySQL 8
- Repo cloné, branche `001-you-study-whole` checkoutée

## Setup

```bash
composer install
npm install
cp .env.example .env   # si pas déjà fait
php artisan key:generate
php artisan migrate
npm run dev
```

## Key Files to Modify

### Backend (PHP/Laravel)

| File | Changes |
|------|---------|
| `config/app.php` | `'timezone' => 'Africa/Algiers'` |
| `app/Models/Employe.php` | +fillable: mode_remuneration, prime_par_piece. +casts. +validation conditionnelle |
| `app/Models/FichePaie.php` | +fillable: montant_heures_supplementaires, pieces_fabriquees, snapshots. Modifier `calculerSalaire()` pour intégrer heures sup majorées et logique "piece" |
| `app/Models/PaieMensuelle.php` | Aucun changement structurel (totaux recalculés automatiquement) |
| `app/Http/Controllers/EmployeController.php` | +validation mode_remuneration, prime_par_piece conditionnelle |
| `app/Http/Controllers/FichePaieController.php` | +logique pièces dans store/update |
| `app/Http/Controllers/PaieMensuelleController.php` | +saisiePiecesEnLot(), +storePiecesEnLot() |
| `routes/web.php` | +2 routes saisie pièces |

### Frontend (Vue/Inertia)

| File | Changes |
|------|---------|
| `resources/js/Pages/Employes/Create.vue` | +champ mode_remuneration (select), +champ prime_par_piece (conditionnel) |
| `resources/js/Pages/Employes/Edit.vue` | idem |
| `resources/js/Pages/Employes/Index.vue` | +colonne/badge mode_remuneration |
| `resources/js/Pages/FichesPaie/Show.vue` | +section heures sup majorées, +infos pièces/prime rendement |
| `resources/js/Pages/FichesPaie/Edit.vue` | +champs pièces ou montant libre selon catégorie |
| `resources/js/Pages/PaiesMensuelles/Show.vue` | +bouton "Saisie des pièces" (si employés piece existent) |
| `resources/js/Pages/PaiesMensuelles/SaisiePieces.vue` | NOUVEAU: tableau saisie en lot |

### Database

| Migration | Description |
|-----------|-------------|
| `add_mode_remuneration_to_employes` | +mode_remuneration (enum), +prime_par_piece (decimal) |
| `add_overtime_pieces_to_fiches_paie` | +montant_heures_supplementaires, +pieces_fabriquees, +snapshots |

## Verification

1. **Timezone**: `php artisan tinker` → `now()` doit retourner l'heure WAT (UTC+1)
2. **Employé piece**: Créer un employé avec mode_remuneration=piece, prime_par_piece=50
3. **Paie mensuelle**: Générer une paie, accéder à "Saisie des pièces", entrer 100 pièces → prime_rendement = 5000
4. **Heures sup**: Créer un pointage de 10h → 2h sup → vérifier le montant majoré dans la fiche
