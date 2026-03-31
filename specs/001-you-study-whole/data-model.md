# Data Model: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

## Entity Changes

### Employe (table: `employes`)

#### New Fields

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| `mode_remuneration` | enum('salaire','piece') | NO | 'salaire' | Mode de rémunération de l'employé |
| `prime_par_piece` | decimal(10,2) | YES | NULL | Montant en DZD par pièce fabriquée (requis si mode_remuneration = 'piece') |

#### Validation Rules

- `mode_remuneration`: required, in:salaire,piece
- `prime_par_piece`: required_if:mode_remuneration,piece | nullable | numeric | min:0.01
- Si `mode_remuneration = 'salaire'`, `prime_par_piece` est ignoré (peut être null)
- Si `mode_remuneration = 'piece'` et `prime_par_piece` est null ou 0, avertissement (pas blocage) lors de la génération de fiche

#### Impact on Existing Data

- Migration ajoute `mode_remuneration` avec défaut 'salaire' → tous les employés existants sont "au salaire" automatiquement
- `prime_par_piece` nullable → aucun impact sur les employés existants

#### Existing Field Note

- Le champ `categorie` (string, 50) reste inchangé — il représente la classification professionnelle (Cadre, Ouvrier, Technicien), PAS le mode de rémunération

---

### FichePaie (table: `fiches_paie`)

#### New Fields

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| `montant_heures_supplementaires` | decimal(10,2) | NO | 0.00 | Montant majoré des heures sup (heures × taux_horaire × 1.5) |
| `pieces_fabriquees` | integer unsigned | YES | NULL | Nombre de pièces fabriquées dans le mois (employés "piece" uniquement) |
| `prime_par_piece_snapshot` | decimal(10,2) | YES | NULL | Snapshot de la prime par pièce au moment du calcul |
| `mode_remuneration_snapshot` | enum('salaire','piece') | NO | 'salaire' | Snapshot du mode de rémunération au moment du calcul |

#### Existing Fields Reused

| Field | Current Usage | New Usage |
|-------|--------------|-----------|
| `heures_supplementaires` | Nombre d'heures (decimal) | Inchangé — stocke toujours le nombre d'heures |
| `prime_rendement` | Montant libre (decimal) | Réutilisé — pour "piece": prime_par_piece × pieces_fabriquees, pour "salaire": montant libre saisi |

#### Validation Rules

- `montant_heures_supplementaires`: numeric, min:0 (calculé automatiquement)
- `pieces_fabriquees`: nullable, integer, min:0 (requis uniquement pour mode "piece")
- `prime_par_piece_snapshot`: nullable, numeric, min:0
- `mode_remuneration_snapshot`: required, in:salaire,piece

#### State Transitions

Aucun changement aux statuts existants (brouillon → valide → paye). Les nouveaux champs sont calculés lors de la génération/recalcul de la fiche.

---

### PaieMensuelle (table: `paies_mensuelles`)

#### No Schema Changes

Les totaux existants (`total_primes`, `total_brut`, `total_net`, etc.) intègrent déjà les heures sup et primes de rendement via le recalcul des fiches. La méthode `recalculerTotaux()` somme les champs des fiches — les nouveaux montants (heures sup majorées, prime rendement pièces) sont déjà inclus dans `salaire_brut` de chaque fiche.

---

## Calculation Formulas

### Employé "au salaire" (mode_remuneration = 'salaire')

```
taux_horaire = salaire_base / (jours_ouvres × 8)
montant_heures_sup = heures_supplementaires × taux_horaire × 1.5

salaire_brut = (salaire_base × ratio_presence)
             + prime_transport
             + prime_panier
             + prime_anciennete
             + prime_rendement        ← montant libre saisi
             + autres_primes
             + montant_heures_sup

cotisation_cnas = salaire_brut × 0.09
salaire_imposable = salaire_brut - cotisation_cnas
ir = calculerIRG(salaire_imposable)
salaire_net = salaire_brut - cotisation_cnas - ir - autres_deductions
net_a_payer = salaire_net - deduction_retard - deduction_absence
```

### Employé "à la pièce" (mode_remuneration = 'piece')

```
prime_rendement = prime_par_piece × pieces_fabriquees    ← calculé automatiquement
taux_horaire = salaire_base / (jours_ouvres × 8)        ← peut être 0 si salaire_base = 0
montant_heures_sup = heures_supplementaires × taux_horaire × 1.5

salaire_brut = prime_rendement                           ← PAS affecté par ratio_presence
             + (salaire_base × ratio_presence)           ← proraté par présence
             + prime_transport
             + prime_panier
             + prime_anciennete
             + autres_primes
             + montant_heures_sup

cotisation_cnas = salaire_brut × 0.09
salaire_imposable = salaire_brut - cotisation_cnas
ir = calculerIRG(salaire_imposable)
salaire_net = salaire_brut - cotisation_cnas - ir - autres_deductions
net_a_payer = salaire_net - deduction_retard - deduction_absence
```

### Key Difference

La composante `prime_rendement` (pièces × prix) n'est **jamais** proratée par le ratio de présence. Elle représente la production réelle. Seul le `salaire_base` est proraté.

---

## Relationships

```
Employe 1──N FichePaie    (employe_id FK)
PaieMensuelle 1──N FichePaie  (paie_mensuelle_id FK, nullable)

No new relationships added.
```

## Migration Strategy

### Migration 1: `add_mode_remuneration_to_employes`
- ADD `mode_remuneration` enum('salaire','piece') DEFAULT 'salaire' NOT NULL AFTER `mode_paiement`
- ADD `prime_par_piece` decimal(10,2) NULL AFTER `mode_remuneration`

### Migration 2: `add_overtime_pieces_to_fiches_paie`
- ADD `montant_heures_supplementaires` decimal(10,2) DEFAULT 0.00 NOT NULL AFTER `heures_supplementaires`
- ADD `pieces_fabriquees` int unsigned NULL AFTER `prime_rendement`
- ADD `prime_par_piece_snapshot` decimal(10,2) NULL AFTER `pieces_fabriquees`
- ADD `mode_remuneration_snapshot` enum('salaire','piece') DEFAULT 'salaire' NOT NULL AFTER `prime_par_piece_snapshot`
