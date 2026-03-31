# Routes Contract: New & Modified Routes

## New Routes

### Saisie des pièces en lot

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `paies-mensuelles/{paieMensuelle}/saisie-pieces` | `PaieMensuelleController@saisiePiecesEnLot` | `paies-mensuelles.saisie-pieces` |
| POST | `paies-mensuelles/{paieMensuelle}/saisie-pieces` | `PaieMensuelleController@storePiecesEnLot` | `paies-mensuelles.store-pieces` |

#### GET saisie-pieces

**Request**: Aucun paramètre supplémentaire
**Response (Inertia)**: Page `PaiesMensuelles/SaisiePieces` avec props:
- `paieMensuelle`: objet paie mensuelle (id, reference, mois, annee, statut)
- `fichesPiece`: tableau de fiches de paie pour employés "piece" du mois:
  - `id`: int
  - `employe`: { id, matricule, nom, prenom, mode_remuneration, prime_par_piece }
  - `pieces_fabriquees`: int|null
  - `prime_rendement`: decimal
  - `prime_par_piece_snapshot`: decimal|null

**Preconditions**: paie mensuelle existe, statut != 'cloture'
**Access**: Authentifié

#### POST store-pieces

**Request body**:
```json
{
  "pieces": {
    "{fiche_paie_id}": {nombre_pieces},
    "{fiche_paie_id}": {nombre_pieces}
  }
}
```

**Validation**:
- `pieces`: required, array
- `pieces.*`: integer, min:0

**Response**: Redirect vers `paies-mensuelles.show` avec message de succès
**Side effects**: Met à jour `pieces_fabriquees`, recalcule `prime_rendement` et `salaire_brut` pour chaque fiche, recalcule totaux paie mensuelle

## Modified Routes (behavior changes)

### Employes

| Route | Change |
|-------|--------|
| POST `employes` (store) | +validation mode_remuneration, prime_par_piece conditionnelle |
| PUT `employes/{id}` (update) | idem |

### Fiches de paie

| Route | Change |
|-------|--------|
| POST `fiches-paie` (store) | +gestion pieces_fabriquees pour employés "piece", +calcul montant_heures_sup |
| PUT `fiches-paie/{id}` (update) | idem |
| GET `fiches-paie/{id}` (show) | +affichage montant heures sup, pièces, prime rendement détaillée |
