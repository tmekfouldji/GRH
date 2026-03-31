# Implementation Plan: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

**Branch**: `001-you-study-whole` | **Date**: 2026-03-31 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-you-study-whole/spec.md`

## Summary

Quatre modifications au système de paie GRH Textile :
1. **Heures supplémentaires majorées à 50%** — calculer le montant monétaire des heures sup et l'intégrer au salaire brut
2. **Catégorie de rémunération employé** — nouveau champ `mode_remuneration` (salaire/piece) avec `prime_par_piece` conditionnelle
3. **Prime de rendement** — saisie par pièces fabriquées ou montant libre selon la catégorie, avec page de saisie en lot
4. **Timezone WAT** — passer l'application de UTC à Africa/Algiers (UTC+1)

## Technical Context

**Language/Version**: PHP 8.2 / Laravel 12.0
**Primary Dependencies**: Vue 3.5, Inertia.js 2.2, Tailwind CSS 3.4, PhpSpreadsheet 5.3
**Storage**: MySQL 8 (database: `gestion_employes`)
**Testing**: PHPUnit 11.5 (tests basiques existants uniquement)
**Target Platform**: Web application (serveur Linux/Windows)
**Project Type**: Web application (Laravel + Inertia/Vue SPA-like)
**Performance Goals**: N/A (application interne, quelques utilisateurs)
**Constraints**: Conformité au droit du travail algérien (CNAS 9%, IRG progressif, jours ouvrés dim-jeu)
**Scale/Scope**: ~100 employés, 1 gestionnaire RH, ~20 fiches de paie/mois

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Constitution non configurée (template vierge). Aucune contrainte à vérifier. **PASS**.

## Project Structure

### Documentation (this feature)

```text
specs/001-you-study-whole/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (internal routes)
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── Employe.php              # +mode_remuneration, +prime_par_piece
│   ├── FichePaie.php            # +montant_heures_sup, +pieces_fabriquees, +categorie_employe_snapshot
│   └── PaieMensuelle.php        # Totaux mis à jour
├── Http/Controllers/
│   ├── EmployeController.php    # Validation mode_remuneration + prime_par_piece
│   ├── FichePaieController.php  # Logique pièces, heures sup
│   └── PaieMensuelleController.php  # +saisiePiecesEnLot(), +storePiecesEnLot()
└── ...

config/
└── app.php                      # timezone → Africa/Algiers

database/migrations/
├── YYYY_MM_DD_add_mode_remuneration_to_employes.php
└── YYYY_MM_DD_add_overtime_pieces_to_fiches_paie.php

resources/js/Pages/
├── Employes/
│   ├── Create.vue               # +champ mode_remuneration + prime_par_piece conditionnel
│   └── Edit.vue                 # idem
├── FichesPaie/
│   ├── Show.vue                 # +affichage heures sup majorées, pièces, prime rendement
│   └── Edit.vue                 # +champs pièces/prime selon catégorie
└── PaiesMensuelles/
    ├── Show.vue                 # +bouton "Saisie des pièces"
    └── SaisiePieces.vue         # NOUVEAU: page saisie en lot des pièces
```

**Structure Decision**: Architecture Laravel MVC existante conservée. Aucune nouvelle couche — modifications in-place dans les modèles, contrôleurs et vues existants. Une seule nouvelle page Vue (SaisiePieces.vue).

## Complexity Tracking

Aucune violation de constitution — section non applicable.
