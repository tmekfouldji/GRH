# Tasks: Heures Supplémentaires, Primes Rendement, Catégories Employé & Timezone WAT

**Input**: Design documents from `/specs/001-you-study-whole/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: No test tasks generated — not explicitly requested in specification.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup

**Purpose**: Configuration and database migrations shared by all stories

- [x] T001 Set timezone to 'Africa/Algiers' in config/app.php (change 'UTC' to 'Africa/Algiers')
- [x] T002 Create migration add_mode_remuneration_to_employes: add `mode_remuneration` enum('salaire','piece') DEFAULT 'salaire' NOT NULL and `prime_par_piece` decimal(10,2) NULL to `employes` table in database/migrations/
- [x] T003 Create migration add_overtime_pieces_to_fiches_paie: add `montant_heures_supplementaires` decimal(10,2) DEFAULT 0 NOT NULL, `pieces_fabriquees` int unsigned NULL, `prime_par_piece_snapshot` decimal(10,2) NULL, `mode_remuneration_snapshot` enum('salaire','piece') DEFAULT 'salaire' NOT NULL to `fiches_paie` table in database/migrations/
- [x] T004 Run migrations: `php artisan migrate`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Model-level changes that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T005 Update Employe model: add `mode_remuneration`, `prime_par_piece` to $fillable array, add casts (`mode_remuneration` as string, `prime_par_piece` as decimal:2) in app/Models/Employe.php
- [x] T006 Update FichePaie model: add `montant_heures_supplementaires`, `pieces_fabriquees`, `prime_par_piece_snapshot`, `mode_remuneration_snapshot` to $fillable array, add appropriate casts in app/Models/FichePaie.php

**Checkpoint**: Foundation ready — models recognize new fields, user story implementation can begin

---

## Phase 3: User Story 4 — Correction du fuseau horaire GMT → WAT (Priority: P1)

**Goal**: Toutes les heures affichées et enregistrées sont en WAT (UTC+1) au lieu de GMT/UTC

**Independent Test**: Exécuter `php artisan tinker` → `now()` retourne l'heure WAT. Créer un pointage → l'heure stockée correspond à l'heure locale.

### Implementation for User Story 4

- [x] T007 [US4] Verify timezone change is effective: check all Carbon::now(), now(), today() calls in app/Models/Pointage.php, app/Http/Controllers/PointageController.php produce WAT times — no code change needed if config/app.php is correct, but verify no hardcoded 'UTC' exists
- [x] T008 [US4] Check frontend date formatting in resources/js/Pages/ — search all .vue files for date/time formatting (toLocaleString, formatDate, etc.) and ensure no explicit 'UTC' timezone override exists; fix any found
- [x] T009 [US4] Verify that Inertia shared data and any JavaScript Date() usage respects server timezone in resources/js/app.js and resources/js/Layouts/ — no conversion issues between server WAT and client display

**Checkpoint**: All timestamps across the platform display in WAT (UTC+1). Existing data unchanged.

---

## Phase 4: User Story 1 — Calcul des heures supplémentaires à +50% (Priority: P1)

**Goal**: Les heures supplémentaires sont majorées de 50% du taux horaire et le montant apparaît dans les fiches de paie

**Independent Test**: Créer un pointage de 10h pour un employé (salaire_base=30000, 22 jours ouvrés). Générer la fiche de paie → montant_heures_supplementaires = 2 × (30000/176) × 1.5 ≈ 511.36 DZD

### Implementation for User Story 1

- [ ] T010 [US1] Modify FichePaie::calculerSalaire() in app/Models/FichePaie.php: add calculation of taux_horaire_base = salaire_base / (jours_ouvres × 8), then montant_heures_supplementaires = heures_supplementaires × taux_horaire_base × 1.5, store in $this->montant_heures_supplementaires, add to salaire_brut before CNAS/IRG calculation
- [ ] T011 [US1] Update FichePaie::calculerSalaire() in app/Models/FichePaie.php: if salaire_base == 0 and heures_supplementaires > 0, set montant_heures_supplementaires = 0 (handle edge case, prepare warning flag)
- [ ] T012 [US1] Update Employe::calculerSalairePreview() in app/Models/Employe.php to include heures supplémentaires montant in the preview calculation (if applicable)
- [x] T013 [US1] Update FichesPaie/Show.vue in resources/js/Pages/FichesPaie/Show.vue: add display section showing heures_supplementaires (count), taux horaire de base, taux majoré (×1.5), montant_heures_supplementaires
- [x] T014 [US1] Update PaiesMensuelles/Show.vue in resources/js/Pages/PaiesMensuelles/Show.vue: add montant heures supplémentaires column/info in the fiches listing table
- [x] T015 [US1] Update PaiesMensuelles/ValidationPresences.vue in resources/js/Pages/PaiesMensuelles/ValidationPresences.vue: show montant heures sup in the hours detail section, display warning if salaire_base = 0 and heures_sup > 0

**Checkpoint**: Fiches de paie include overtime at 150% rate. Warning shown when base salary is 0.

---

## Phase 5: User Story 2 — Catégorie employé : à la pièce ou au salaire (Priority: P1)

**Goal**: Chaque employé a une catégorie de rémunération (salaire/pièce) avec prime par pièce conditionnelle

**Independent Test**: Créer un employé avec mode_remuneration=piece, prime_par_piece=50. Vérifier que le formulaire affiche le champ. Consulter la liste → badge visible.

### Implementation for User Story 2

- [x] T016 [US2] Update EmployeController::store() in app/Http/Controllers/EmployeController.php: add validation rules for mode_remuneration (required, in:salaire,piece) and prime_par_piece (required_if:mode_remuneration,piece, nullable, numeric, min:0.01)
- [x] T017 [US2] Update EmployeController::update() in app/Http/Controllers/EmployeController.php: add same validation rules as store() for mode_remuneration and prime_par_piece
- [x] T018 [P] [US2] Update Employes/Create.vue in resources/js/Pages/Employes/Create.vue: add select field for mode_remuneration (options: "Au salaire", "À la pièce"), add conditional prime_par_piece input field (shown only when mode_remuneration=piece), make prime_par_piece required when visible
- [x] T019 [P] [US2] Update Employes/Edit.vue in resources/js/Pages/Employes/Edit.vue: same changes as Create.vue — select mode_remuneration, conditional prime_par_piece field
- [x] T020 [US2] Update Employes/Index.vue in resources/js/Pages/Employes/Index.vue: add mode_remuneration column or badge ("Salaire" / "Pièce") in the employees table, pass mode_remuneration from EmployeController::index()
- [x] T021 [US2] Update EmployeController::index() and EmployeController::create()/edit() in app/Http/Controllers/EmployeController.php: ensure mode_remuneration and prime_par_piece are passed to Inertia props
- [x] T022 [US2] Update FichePaie::calculerSalaire() in app/Models/FichePaie.php: snapshot mode_remuneration and prime_par_piece from Employe at calculation time (store in mode_remuneration_snapshot, prime_par_piece_snapshot)
- [x] T023 [US2] Add category mismatch warning in FichePaieController::show() and PaieMensuelleController::show() in app/Http/Controllers/: when fiche.mode_remuneration_snapshot != employe.mode_remuneration, pass a warning flag to the frontend

**Checkpoint**: Employees can be categorized as piece/salary. Badge visible in list. Snapshot stored in fiches.

---

## Phase 6: User Story 3 — Prime de rendement par pièce ou par montant (Priority: P2)

**Goal**: Saisie des pièces fabriquées (employés piece) ou montant libre (employés salaire) dans les fiches de paie, avec page de saisie en lot

**Independent Test**: Créer un employé piece (prime_par_piece=50). Générer paie mensuelle. Accéder "Saisie des pièces" → saisir 500 pièces → prime_rendement = 25000 DZD. Vérifier le salaire brut.

**Dependencies**: Requires Phase 5 (US2) — category must exist to determine rendement input type

### Implementation for User Story 3

- [x] T024 [US3] Modify FichePaie::calculerSalaire() in app/Models/FichePaie.php: for mode_remuneration_snapshot='piece', calculate prime_rendement = prime_par_piece_snapshot × pieces_fabriquees; salary formula = prime_rendement (not prorated) + (salaire_base × ratio_presence) + primes + montant_heures_sup. For mode='salaire', keep prime_rendement as manually entered amount
- [x] T025 [US3] Update PaieMensuelle::genererPaieMensuelle() in app/Models/PaieMensuelle.php: when generating fiches for piece employees, set pieces_fabriquees=0 as default, add warning if prime_par_piece is null/0 on the employee
- [x] T026 [US3] Add routes for batch piece entry in routes/web.php: GET paies-mensuelles/{paieMensuelle}/saisie-pieces and POST paies-mensuelles/{paieMensuelle}/saisie-pieces
- [x] T027 [US3] Implement PaieMensuelleController::saisiePiecesEnLot() in app/Http/Controllers/PaieMensuelleController.php: load paie with fiches where employe.mode_remuneration='piece', return Inertia page PaiesMensuelles/SaisiePieces with fichesPiece data
- [x] T028 [US3] Implement PaieMensuelleController::storePiecesEnLot() in app/Http/Controllers/PaieMensuelleController.php: validate pieces array (each integer >= 0), update pieces_fabriquees on each fiche, recalculate prime_rendement and salaire via calculerSalaire(), recalculate paie totals via recalculerTotaux(), redirect with success message
- [x] T029 [US3] Create SaisiePieces.vue in resources/js/Pages/PaiesMensuelles/SaisiePieces.vue: table with columns (matricule, nom, prénom, prime par pièce, pièces fabriquées input, montant calculé en temps réel), save button that POSTs all pieces, back link to paie show
- [x] T030 [US3] Update PaiesMensuelles/Show.vue in resources/js/Pages/PaiesMensuelles/Show.vue: add "Saisie des pièces" button (visible only if paie has piece employees and statut != 'cloture'), link to saisie-pieces route
- [x] T031 [US3] Update FichesPaie/Show.vue in resources/js/Pages/FichesPaie/Show.vue: display prime de rendement details — for piece employees: show pieces_fabriquees, prime_par_piece_snapshot, calculated amount; for salary employees: show prime_rendement as entered amount
- [x] T032 [US3] Update FichesPaie/Edit.vue in resources/js/Pages/FichesPaie/Edit.vue: for piece employees show pieces_fabriquees input (auto-calculate prime_rendement), for salary employees show prime_rendement direct input field; adapt based on mode_remuneration_snapshot
- [x] T033 [US3] Update FichePaieController::update() in app/Http/Controllers/FichePaieController.php: handle pieces_fabriquees input for piece employees (validate integer >= 0, recalculate prime_rendement), handle prime_rendement direct input for salary employees (validate decimal >= 0)
- [x] T034 [US3] Validate edge case: in FichePaieController::store() and PaieMensuelle::genererPaieMensuelle(), if piece employee has prime_par_piece null/0, add warning to response/logs but do not block generation — in app/Http/Controllers/FichePaieController.php and app/Models/PaieMensuelle.php

**Checkpoint**: Piece employees get batch piece entry. Salary employees get manual rendement input. All calculations correct.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T035 [P] Update Excel export in FichePaieController::exporterExcel() in app/Http/Controllers/FichePaieController.php: add montant_heures_supplementaires, pieces_fabriquees, mode_remuneration_snapshot columns to exported spreadsheet
- [x] T036 [P] Update SalaireCalculateur.vue in resources/js/Pages/SalaireCalculateur.vue: add mode_remuneration toggle, when "piece" selected show prime_par_piece and pieces fields, calculate accordingly
- [x] T037 [P] Update SalaireCalculateurInverse.vue in resources/js/Pages/SalaireCalculateurInverse.vue: same piece/salary toggle as forward calculator
- [x] T038 Update FichePaieController::syncEmployeData() in app/Http/Controllers/FichePaieController.php: sync mode_remuneration and prime_par_piece from employee to fiche snapshots when syncing data
- [x] T039 Run quickstart.md validation: verify all 4 verification steps pass (timezone tinker, employee piece creation, paie with pieces, heures sup montant)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 (migrations must run)
- **US4 Timezone (Phase 3)**: Depends on Phase 1 only — can run in parallel with Phase 2
- **US1 Heures Sup (Phase 4)**: Depends on Phase 2 (FichePaie model updated)
- **US2 Catégorie (Phase 5)**: Depends on Phase 2 (Employe model updated)
- **US3 Prime Rendement (Phase 6)**: Depends on Phase 5 (category must exist)
- **Polish (Phase 7)**: Depends on Phases 4, 5, 6

### User Story Dependencies

- **US4 (Timezone)**: Independent — config change only, no dependency on other stories
- **US1 (Heures Sup)**: Independent — modifies salary calculation, no dependency on categories
- **US2 (Catégorie)**: Independent — adds employee classification, no dependency on heures sup
- **US3 (Prime Rendement)**: **Depends on US2** — requires mode_remuneration field to determine input type

### Within Each User Story

- Models/backend logic before frontend display
- Controller updates before Vue page updates
- Core calculation before edge case handling

### Parallel Opportunities

**After Phase 2 completes, these can run in parallel:**
- US4 (Timezone) — already completable after Phase 1
- US1 (Heures Sup) — independent salary calculation
- US2 (Catégorie) — independent employee classification

**Within Phase 5 (US2):**
- T018 and T019 (Create.vue and Edit.vue) can run in parallel

**Within Phase 7 (Polish):**
- T035, T036, T037 can all run in parallel

---

## Parallel Example: After Foundational Phase

```text
# These three stories can start simultaneously after Phase 2:
Stream A: T007→T008→T009 (US4: Timezone)
Stream B: T010→T011→T012→T013→T014→T015 (US1: Heures Sup)
Stream C: T016→T017→T018+T019→T020→T021→T022→T023 (US2: Catégorie)

# Then US3 starts after Stream C completes:
Stream D: T024→T025→T026→T027→T028→T029→T030→T031→T032→T033→T034 (US3: Prime Rendement)
```

---

## Implementation Strategy

### MVP First (US4 + US1 + US2)

1. Complete Phase 1: Setup (migrations + timezone config)
2. Complete Phase 2: Foundational (model $fillable updates)
3. Complete Phase 3: US4 Timezone — immediate impact, low risk
4. Complete Phase 4: US1 Heures Sup — high value, affects all employees
5. Complete Phase 5: US2 Catégorie — enables US3
6. **STOP and VALIDATE**: Test timezone, heures sup, and categories independently
7. Deploy if ready — this is the MVP

### Full Delivery

8. Complete Phase 6: US3 Prime Rendement — batch piece entry
9. Complete Phase 7: Polish — Excel export, calculators
10. Final validation with quickstart.md

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US3 is the only story with a cross-story dependency (requires US2)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
