# Research: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

## R-001: Conflit avec le champ `categorie` existant

**Decision**: Utiliser un nouveau champ `mode_remuneration` (enum: salaire/piece) au lieu de modifier le champ `categorie` existant.

**Rationale**: Le champ `categorie` existant dans la table `employes` est un string libre utilisé pour la classification professionnelle (Cadre, Ouvrier, Technicien). Il a une sémantique complètement différente du concept "à la pièce / au salaire". Créer un champ dédié évite toute confusion et préserve la rétro-compatibilité.

**Alternatives considered**:
- Renommer `categorie` en `categorie_professionnelle` et utiliser `categorie` pour le mode de rémunération → Trop de refactoring, risque de casser l'existant
- Ajouter un préfixe au champ existant → Confusion sémantique

## R-002: Réutilisation du champ `prime_rendement` existant dans fiches_paie

**Decision**: Réutiliser le champ `prime_rendement` existant dans `fiches_paie` pour stocker le montant calculé de la prime de rendement (que ce soit par pièces ou montant libre).

**Rationale**: Le champ existe déjà avec la bonne sémantique (decimal, montant en DZD). Pour les employés "piece", la valeur sera calculée (prime_par_piece × pieces_fabriquees). Pour les employés "salaire", la valeur sera saisie directement. Pas besoin de nouveau champ pour le montant — seuls les champs `pieces_fabriquees` et `prime_par_piece_snapshot` sont nouveaux.

**Alternatives considered**:
- Créer un nouveau champ `prime_rendement_calculee` → Duplication inutile, le champ existant fait le même travail

## R-003: Calcul des heures supplémentaires — formule et intégration

**Decision**: Le montant des heures supplémentaires est calculé dans `FichePaie::calculerSalaire()` et stocké dans un nouveau champ `montant_heures_supplementaires`. Il est ajouté au salaire brut APRÈS la proration par ratio de présence.

**Rationale**:
- Formule: `montant_hs = heures_supplementaires × (salaire_base / (jours_ouvres × 8)) × 1.5`
- Le taux horaire est basé sur le salaire de base mensuel complet (non proraté)
- Le montant est ajouté au brut avant calcul CNAS/IRG
- Pour les employés "piece" avec salaire_base = 0, le montant est 0 (taux horaire = 0)

**Alternatives considered**:
- Calculer les heures sup jour par jour avec des taux différents → Hors scope (spec dit taux unique 50%)
- Ne pas stocker le montant, le recalculer à chaque affichage → Mauvaise traçabilité, incohérence possible

## R-004: Timezone — stratégie de migration

**Decision**: Modifier uniquement `config/app.php` (timezone = 'Africa/Algiers'). Ne pas convertir les données existantes.

**Rationale**:
- Laravel utilise la timezone de `config/app.php` pour toutes les opérations Carbon et Eloquent timestamps
- Les colonnes MySQL `datetime` ne stockent pas de timezone — elles contiennent les valeurs telles quelles
- Changer la config fait que tous les nouveaux `now()`, `Carbon::now()`, `created_at`, `updated_at` seront en WAT
- Les données existantes (pointages, fiches) restent inchangées — elles ont été saisies avec l'heure perçue par l'utilisateur, donc probablement déjà en heure locale (saisie manuelle)
- Si les pointages ont été enregistrés via `now()` en UTC, il y a un décalage d'1h sur les données historiques — mais la spec dit explicitement de ne pas modifier rétroactivement

**Alternatives considered**:
- Migrer toutes les données existantes +1h → Risqué, spec interdit les modifications rétroactives
- Utiliser une colonne TIMESTAMP au lieu de DATETIME → Changement trop invasif sur le schéma existant

## R-005: Saisie en lot des pièces — design de la page

**Decision**: Nouvelle page Vue `SaisiePieces.vue` accessible depuis `PaiesMensuelles/Show.vue` via un bouton "Saisie des pièces". La page affiche un tableau éditable de tous les employés "piece" du mois avec leurs pièces actuelles et un bouton de sauvegarde global.

**Rationale**:
- Deux nouvelles routes: `GET paies-mensuelles/{id}/saisie-pieces` et `POST paies-mensuelles/{id}/saisie-pieces`
- Le contrôleur filtre les fiches_paie du mois dont l'employé a `mode_remuneration = 'piece'`
- À la sauvegarde, pour chaque fiche: met à jour `pieces_fabriquees`, recalcule `prime_rendement` et `salaire_brut`, puis recalcule les totaux de la paie mensuelle
- Le bouton n'apparaît que s'il y a au moins un employé "piece" dans la paie

**Alternatives considered**:
- Modal dans la page Show → Trop limité pour un tableau avec potentiellement beaucoup d'employés
- Modifier individuellement chaque fiche → Workflow trop lent (clarification spec: option B choisie)

## R-006: Snapshot de la catégorie dans la fiche de paie

**Decision**: Stocker `mode_remuneration_snapshot` et `prime_par_piece_snapshot` dans `fiches_paie` au moment de la génération.

**Rationale**: Si la catégorie ou la prime par pièce change après la génération, la fiche conserve les valeurs utilisées pour le calcul. Cela permet aussi de détecter un décalage (FR-029: avertissement si catégorie actuelle ≠ snapshot) et assure la traçabilité.

**Alternatives considered**:
- Ne pas faire de snapshot, toujours lire depuis l'employé → Perte de traçabilité, recalculs silencieux incorrects
