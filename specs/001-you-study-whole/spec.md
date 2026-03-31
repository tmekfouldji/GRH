# Feature Specification: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

**Feature Branch**: `001-you-study-whole`
**Created**: 2026-03-31
**Status**: Draft
**Input**: User description: "Heures supplémentaires (+50%), prime de rendement par pièce/montant, catégorie employé (à la pièce/salaire), fixer timezone GMT→WAT"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Calcul des heures supplémentaires à +50% (Priority: P1)

En tant que gestionnaire RH, je veux que les heures supplémentaires soient automatiquement majorées de 50% du taux horaire et affichées dans les fiches de paie mensuelles, afin que les employés soient rémunérés conformément à la réglementation.

**Why this priority**: Les heures supplémentaires impactent directement le salaire net de chaque employé présent. C'est le changement qui touche le plus d'employés et affecte chaque fiche de paie mensuelle.

**Independent Test**: Peut être testé en créant un pointage avec plus de 8h pour un employé, puis en générant sa fiche de paie. Le montant des heures supplémentaires doit apparaître clairement avec la majoration de 50%.

**Acceptance Scenarios**:

1. **Given** un employé avec un salaire de base de 30 000 DZD/mois et 22 jours ouvrés, **When** il travaille 10h dans une journée (2h supplémentaires), **Then** le montant des heures supplémentaires pour cette journée = 2h × (salaire_base / (22 jours × 8h)) × 1.5 = 2 × (30000 / 176) × 1.5 = 2 × 170.45 × 1.5 = 511.36 DZD
2. **Given** un employé avec 0 heures supplémentaires dans le mois, **When** la fiche de paie est générée, **Then** le montant des heures supplémentaires est 0 DZD et la ligne apparaît avec la valeur 0
3. **Given** un employé avec des heures supplémentaires cumulées sur plusieurs jours du mois, **When** la fiche de paie est générée, **Then** le total des heures supplémentaires du mois est la somme de toutes les heures supplémentaires journalières, et le montant total est calculé avec la majoration de 50%
4. **Given** un employé à temps partiel (salaire proraté), **When** il fait des heures supplémentaires, **Then** le taux horaire de base utilisé pour le calcul des heures supplémentaires est basé sur le salaire de base complet (non proraté), divisé par le nombre d'heures normales du mois
5. **Given** la fiche de paie dans la vue détaillée des paies mensuelles, **When** le gestionnaire consulte les heures travaillées, **Then** il voit : heures normales, heures supplémentaires (en nombre d'heures), taux horaire de base, taux majoré (+50%), et montant total des heures supplémentaires

---

### User Story 2 - Catégorie employé : à la pièce ou au salaire (Priority: P1)

En tant que gestionnaire RH, je veux pouvoir définir la catégorie de chaque employé ("à la pièce" ou "au salaire") dans sa fiche employé, afin que le calcul du salaire s'adapte automatiquement selon le mode de rémunération.

**Why this priority**: La catégorie employé est un prérequis pour la prime de rendement (User Story 3). Elle change fondamentalement la façon dont le salaire est calculé. Sans cette distinction, la prime de rendement ne peut pas fonctionner.

**Independent Test**: Peut être testé en créant deux employés — un "au salaire" et un "à la pièce" — et en vérifiant que le formulaire employé affiche les champs appropriés pour chaque catégorie.

**Acceptance Scenarios**:

1. **Given** le formulaire de création/édition d'un employé, **When** le gestionnaire sélectionne la catégorie "au salaire", **Then** le formulaire affiche les champs standards (salaire de base, primes transport/panier) et masque le champ "prime par pièce"
2. **Given** le formulaire de création/édition d'un employé, **When** le gestionnaire sélectionne la catégorie "à la pièce", **Then** le formulaire affiche un champ supplémentaire "prime par pièce" (en DZD) qui est obligatoire, et le champ "salaire de base" reste visible mais facultatif (peut être 0)
3. **Given** un employé existant de catégorie "au salaire", **When** le gestionnaire change sa catégorie à "à la pièce", **Then** le champ "prime par pièce" apparaît et doit être renseigné avant de sauvegarder
4. **Given** un employé de catégorie "à la pièce" avec une prime par pièce de 50 DZD et un salaire de base de 0 DZD, **When** sa fiche de paie est générée avec 500 pièces fabriquées, **Then** son salaire = (50 × 500) + 0 = 25 000 DZD
5. **Given** un employé de catégorie "à la pièce" avec une prime par pièce de 50 DZD et un salaire de base de 10 000 DZD, **When** sa fiche de paie est générée avec 500 pièces fabriquées, **Then** son salaire = (50 × 500) + 10 000 = 35 000 DZD
6. **Given** un employé de catégorie "au salaire", **When** sa fiche de paie est générée, **Then** le calcul du salaire suit la logique actuelle (salaire de base × ratio de présence + primes)
7. **Given** la liste des employés, **When** le gestionnaire consulte la liste, **Then** la catégorie de chaque employé est visible (colonne ou badge "Pièce" / "Salaire")

---

### User Story 3 - Prime de rendement par pièce ou par montant (Priority: P2)

En tant que gestionnaire RH, je veux pouvoir saisir une prime de rendement pour chaque employé dans sa fiche de paie mensuelle — soit en nombre de pièces fabriquées (pour les employés "à la pièce"), soit en montant fixe (pour les employés "au salaire") — afin de rémunérer la performance individuelle.

**Why this priority**: La prime de rendement dépend de la catégorie employé (User Story 2). Elle ajoute un composant variable au salaire qui nécessite une saisie mensuelle.

**Independent Test**: Peut être testé en créant une fiche de paie pour un employé "à la pièce", en saisissant le nombre de pièces fabriquées, et en vérifiant que le montant de la prime est correctement calculé et intégré au salaire brut.

**Acceptance Scenarios**:

1. **Given** un employé de catégorie "à la pièce" avec une prime par pièce de 50 DZD, **When** le gestionnaire crée/édite sa fiche de paie mensuelle, **Then** un champ "Nombre de pièces fabriquées" est affiché, et le montant de la prime de rendement est calculé automatiquement (pièces × prime par pièce)
2. **Given** un employé de catégorie "au salaire", **When** le gestionnaire crée/édite sa fiche de paie mensuelle, **Then** un champ "Prime de rendement (montant)" est affiché permettant de saisir un montant libre en DZD
3. **Given** un employé "à la pièce" avec 0 pièces fabriquées saisies, **When** la fiche de paie est calculée, **Then** la prime de rendement est 0 DZD et le salaire est uniquement le salaire de base (si défini)
4. **Given** un employé "à la pièce" dont la prime par pièce n'est pas définie dans sa fiche employé (valeur null ou 0), **When** le gestionnaire tente de générer sa fiche de paie, **Then** un avertissement est affiché : "Prime par pièce non définie pour cet employé. Veuillez la configurer dans la fiche employé."
5. **Given** une paie mensuelle en cours de génération en masse, **When** un employé "à la pièce" n'a pas de pièces fabriquées saisies pour le mois, **Then** sa fiche de paie est générée avec une prime de rendement de 0 DZD et un avertissement est signalé
8. **Given** une paie mensuelle générée contenant des employés "à la pièce", **When** le gestionnaire accède à la paie mensuelle, **Then** un bouton/lien "Saisie des pièces" mène vers une page dédiée affichant un tableau de tous les employés "à la pièce" du mois avec un champ de saisie pour le nombre de pièces fabriquées de chacun, permettant la saisie et la sauvegarde en lot
6. **Given** la fiche de paie dans la vue détaillée, **When** le gestionnaire consulte les détails, **Then** il voit clairement : la catégorie de l'employé, le nombre de pièces (si applicable), la prime par pièce (si applicable), et le montant total de la prime de rendement
7. **Given** un employé "à la pièce" avec salaire = (prime_piece × pieces) + salaire_base, **When** le salaire brut est calculé, **Then** les cotisations CNAS (9%) et l'IRG sont calculés sur ce salaire brut total (incluant la prime de rendement)

---

### User Story 4 - Correction du fuseau horaire GMT vers WAT (Priority: P1)

En tant qu'utilisateur de la plateforme, je veux que toutes les heures affichées et enregistrées soient en heure WAT (West Africa Time, UTC+1) au lieu de GMT/UTC, afin que les pointages et horodatages correspondent à l'heure locale réelle en Algérie.

**Why this priority**: Un décalage d'1 heure affecte tous les pointages, toutes les heures d'entrée/sortie, et donc tous les calculs de salaire. C'est un bug critique qui fausse les données existantes et futures.

**Independent Test**: Peut être testé en vérifiant que l'heure affichée dans l'interface correspond à l'heure locale WAT (UTC+1), et que les nouveaux pointages sont enregistrés avec le bon fuseau horaire.

**Acceptance Scenarios**:

1. **Given** la configuration de la plateforme, **When** l'application démarre, **Then** le fuseau horaire par défaut est "Africa/Algiers" (WAT/UTC+1)
2. **Given** un employé qui pointe son entrée à 08:00 heure locale, **When** le pointage est enregistré, **Then** la base de données stocke 08:00 en WAT et l'affichage montre 08:00 (pas 07:00 ni 09:00)
3. **Given** des pointages existants enregistrés en GMT/UTC, **When** le fuseau horaire est corrigé, **Then** les anciens pointages ne sont pas modifiés rétroactivement (ils restent tels quels pour préserver l'intégrité des données historiques)
4. **Given** la page de rapport journalier des pointages, **When** le gestionnaire consulte les heures, **Then** toutes les heures sont affichées en WAT (UTC+1)
5. **Given** le calcul des heures travaillées entre entrée et sortie, **When** les deux horodatages sont en WAT, **Then** la différence en heures est correcte (pas de décalage dû au fuseau horaire)
6. **Given** n'importe quelle page de la plateforme affichant une date/heure, **When** l'utilisateur consulte la page, **Then** l'heure est en WAT (UTC+1) — cela inclut : dates de création, dates de validation, dates de paiement, timestamps d'activité

---

### Edge Cases

- **Heures supplémentaires avec salaire de base à 0** : Si un employé "à la pièce" a un salaire de base de 0, le taux horaire pour les heures supplémentaires est 0. Les heures supplémentaires ne génèrent aucune majoration. Le système doit afficher un avertissement dans ce cas.
- **Changement de catégorie en cours de mois** : Si un employé passe de "au salaire" à "à la pièce" (ou inversement) en milieu de mois et qu'une fiche de paie existe déjà pour ce mois, un avertissement visuel est affiché sur la fiche existante indiquant que la catégorie a changé depuis la génération. Le gestionnaire doit manuellement recalculer la fiche pour appliquer la nouvelle catégorie.
- **Nombre de pièces négatif ou aberrant** : Le système doit valider que le nombre de pièces est un entier positif ou zéro. Les valeurs négatives sont rejetées.
- **Prime par pièce modifiée après génération de la fiche** : Si la prime par pièce est modifiée dans la fiche employé après la génération d'une fiche de paie du mois, les fiches existantes conservent l'ancien taux. Seule une régénération met à jour.
- **Employé sans pointages dans le mois** : Pour un employé "à la pièce" sans pointages, le salaire est calculé uniquement sur les pièces fabriquées + salaire de base. Le ratio de présence n'affecte pas la composante "pièces" mais affecte le salaire de base (si > 0).
- **Heures supplémentaires maximales** : Le système accepte un maximum de 16 heures supplémentaires par jour (24h - 8h normales). Au-delà, il y a probablement une erreur de saisie.
- **Transition de fuseau horaire** : Les pointages créés avant la correction du fuseau restent inchangés. Un commentaire ou une note dans le système documente la date de transition.
- **Paie mensuelle déjà validée/clôturée** : Les modifications de catégorie ou de prime par pièce n'affectent pas les fiches de paie déjà validées ou clôturées.
- **Employé "à la pièce" avec heures supplémentaires** : Un employé "à la pièce" peut aussi avoir des heures supplémentaires. Dans ce cas, le taux horaire pour les heures supplémentaires est basé sur le salaire de base (si > 0). Si salaire de base = 0, les heures supplémentaires sont à taux 0 et un avertissement s'affiche.

## Clarifications

### Session 2026-03-31

- Q: Comment le gestionnaire saisit-il les pièces fabriquées pour les employés "à la pièce" après génération en masse ? → A: Page dédiée de saisie en lot (tableau avec tous les employés "à la pièce" du mois) accessible depuis la paie mensuelle.
- Q: Comportement lors d'un changement de catégorie employé en cours de mois avec fiche existante ? → A: Avertissement affiché sur la fiche existante, recalcul manuel par le gestionnaire (pas de recalcul automatique silencieux).

## Requirements *(mandatory)*

### Functional Requirements

#### Heures Supplémentaires (+50%)

- **FR-001** : Le système DOIT calculer un taux horaire de base = salaire_base / (jours_ouvres_du_mois × 8)
- **FR-002** : Le système DOIT calculer le montant des heures supplémentaires = heures_supplementaires × taux_horaire_base × 1.5
- **FR-003** : Le système DOIT ajouter le montant des heures supplémentaires au salaire brut avant le calcul des cotisations et de l'IRG
- **FR-004** : La fiche de paie DOIT afficher : nombre d'heures supplémentaires, taux horaire de base, taux majoré (×1.5), et montant total des heures supplémentaires
- **FR-005** : Le système DOIT stocker le montant des heures supplémentaires dans un champ dédié de la fiche de paie
- **FR-006** : Si le salaire de base est 0 et que des heures supplémentaires existent, le système DOIT afficher un avertissement "Taux horaire à 0 — heures supplémentaires non rémunérées"

#### Catégorie Employé

- **FR-007** : Le système DOIT permettre de définir la catégorie d'un employé : "salaire" (par défaut) ou "piece"
- **FR-008** : Pour un employé de catégorie "piece", le système DOIT afficher et rendre obligatoire un champ "prime par pièce" (décimal, en DZD) dans la fiche employé
- **FR-009** : Pour un employé de catégorie "salaire", le champ "prime par pièce" DOIT être masqué et sa valeur ignorée dans les calculs
- **FR-010** : Le système DOIT valider qu'un employé "piece" a une prime par pièce > 0 avant de permettre la génération de sa fiche de paie (avec avertissement, pas blocage)
- **FR-011** : La catégorie employé DOIT être affichée dans la liste des employés

#### Prime de Rendement

- **FR-012** : Pour un employé "piece", la fiche de paie DOIT contenir un champ "nombre de pièces fabriquées" (entier >= 0)
- **FR-013** : Pour un employé "piece", la prime de rendement DOIT être calculée = prime_par_piece × nombre_pieces_fabriquees
- **FR-014** : Pour un employé "salaire", la fiche de paie DOIT contenir un champ "prime de rendement" (montant libre en DZD, décimal >= 0)
- **FR-015** : La prime de rendement DOIT être incluse dans le salaire brut et soumise aux cotisations CNAS et à l'IRG
- **FR-016** : Pour un employé "piece", le salaire brut DOIT être calculé = (prime_par_piece × pieces_fabriquees) + salaire_base (si salaire_base > 0), le tout avec ratio de présence uniquement sur la partie salaire_base
- **FR-017** : Le système DOIT valider que le nombre de pièces est un entier positif ou zéro (rejeter les valeurs négatives)
- **FR-027** : Le système DOIT fournir une page dédiée de saisie en lot des pièces fabriquées, accessible depuis la vue de la paie mensuelle, affichant un tableau de tous les employés "à la pièce" du mois avec un champ de saisie par employé
- **FR-028** : La page de saisie en lot DOIT permettre de sauvegarder toutes les saisies de pièces en une seule action et recalculer automatiquement les fiches de paie concernées
- **FR-029** : Si la catégorie d'un employé a changé depuis la dernière génération de sa fiche de paie du mois en cours, le système DOIT afficher un avertissement visuel sur la fiche indiquant le décalage entre la catégorie actuelle et celle utilisée lors du calcul
- **FR-030** : Le recalcul d'une fiche de paie après changement de catégorie DOIT être déclenché manuellement par le gestionnaire (pas de recalcul automatique silencieux)

#### Calcul du Salaire pour Employé "à la Pièce"

- **FR-018** : Pour un employé "piece", le salaire brut = (prime_par_piece × pieces_fabriquees) + (salaire_base × ratio_presence) + prime_transport + prime_panier + prime_anciennete + autres_primes + montant_heures_supplementaires
- **FR-019** : La composante "pièces" (prime_par_piece × pieces_fabriquees) N'EST PAS affectée par le ratio de présence — elle est payée intégralement quel que soit le nombre de jours travaillés
- **FR-020** : Si salaire_base = 0 pour un employé "piece", seule la composante pièces et les primes fixes constituent le salaire brut
- **FR-021** : Les cotisations CNAS (9%) et l'IRG sont calculés sur le salaire brut total (incluant la composante pièces)

#### Timezone WAT

- **FR-022** : Le système DOIT utiliser le fuseau horaire "Africa/Algiers" (UTC+1) comme fuseau par défaut pour toute l'application
- **FR-023** : Tous les horodatages affichés à l'utilisateur DOIVENT être en heure WAT (UTC+1)
- **FR-024** : Les nouveaux pointages DOIVENT être enregistrés en WAT
- **FR-025** : Les données historiques (pointages, fiches de paie, dates de validation) NE DOIVENT PAS être modifiées rétroactivement
- **FR-026** : Le système DOIT configurer le fuseau horaire au niveau applicatif pour que toutes les fonctions de date/heure utilisent WAT par défaut

### Key Entities

- **Employé** : Entité principale représentant un travailleur. Ajout de : catégorie (salaire/pièce), prime par pièce (DZD). Relations : pointages, fiches de paie, congés.
- **Fiche de Paie** : Document mensuel de rémunération. Ajout de : montant heures supplémentaires, nombre de pièces fabriquées, prime de rendement calculée, catégorie employé au moment de la génération. Relations : employé, paie mensuelle.
- **Paie Mensuelle** : Lot mensuel regroupant toutes les fiches. Le total brut et net doivent inclure les nouveaux composants (heures sup majorées, primes rendement).
- **Pointage** : Enregistrement quotidien des heures. Pas de changement de structure, mais le stockage des heures doit respecter le fuseau WAT.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001** : 100% des fiches de paie générées pour des employés avec heures supplémentaires affichent le montant majoré de 50% correctement calculé
- **SC-002** : Le gestionnaire peut créer un employé "à la pièce", saisir sa prime par pièce, et générer une fiche de paie avec pièces fabriquées en moins de 5 minutes
- **SC-003** : Le salaire net d'un employé "à la pièce" correspond exactement à la formule : (prime_piece × pièces) + (salaire_base × ratio) + primes - cotisations - IRG - déductions
- **SC-004** : Toutes les heures affichées dans l'interface correspondent à l'heure locale WAT (UTC+1), vérifiable par comparaison avec l'horloge locale
- **SC-005** : La génération en masse des fiches de paie mensuelles gère correctement les deux catégories d'employés sans intervention manuelle supplémentaire
- **SC-006** : Les données historiques restent intactes après la correction du fuseau horaire — aucun pointage ou fiche existant n'est altéré
- **SC-007** : Le gestionnaire peut distinguer visuellement la catégorie de chaque employé (pièce/salaire) dans la liste des employés

## Assumptions

- Le taux de majoration des heures supplémentaires est fixe à 50% (pas de taux différencié pour heures de nuit, week-end, etc.)
- Les jours ouvrés sont du dimanche au jeudi (vendredi et samedi sont les jours de repos), conformément au calendrier algérien déjà implémenté dans le système
- Un employé ne peut appartenir qu'à une seule catégorie à la fois (pas de mode hybride)
- La prime par pièce est un montant fixe par pièce, défini au niveau de l'employé (pas par type de pièce ou produit)
- Le nombre de pièces fabriquées est saisi manuellement par le gestionnaire lors de la création/édition de la fiche de paie (pas de comptage automatique)
- La valeur par défaut de la catégorie employé est "salaire" pour ne pas impacter les employés existants
- Le fuseau horaire cible est "Africa/Algiers" (UTC+1, WAT) — ce fuseau est constant et ne change pas avec l'heure d'été (l'Algérie n'observe pas l'heure d'été)
- La correction du fuseau horaire s'applique uniquement aux nouvelles données. Les données historiques sont conservées telles quelles.
- Les cotisations patronales (25%) s'appliquent aussi sur la composante pièces du salaire brut
- La prime de rendement pour les employés "au salaire" est saisie comme un montant libre (pas de calcul automatique basé sur des KPIs)
