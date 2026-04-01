<?php

namespace App\Http\Controllers;

use App\Models\FichePaie;
use App\Models\Employe;
use App\Models\Pointage;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Inertia\Inertia;

class FichePaieController extends Controller
{
    public function index(Request $request)
    {
        $query = FichePaie::with('employe');

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        if ($request->filled('mois')) {
            $query->where('mois', $request->mois);
        }

        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $fichesPaie = $query->orderBy('annee', 'desc')
                           ->orderBy('mois', 'desc')
                           ->paginate(15);
        
        $employes = Employe::orderBy('nom')->get();
        $annees = FichePaie::distinct()->pluck('annee')->sortDesc();

        return Inertia::render('FichesPaie/Index', [
            'fichesPaie' => $fichesPaie,
            'employes' => $employes,
            'annees' => $annees,
            'filters' => $request->only(['employe_id', 'mois', 'annee', 'statut']),
        ]);
    }

    public function create()
    {
        $employes = Employe::where('statut', '!=', 'inactif')->orderBy('nom')->get();
        return Inertia::render('FichesPaie/Create', ['employes' => $employes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
            'prime_rendement' => 'nullable|numeric|min:0',
            'autres_primes' => 'nullable|numeric|min:0',
            'autres_deductions' => 'nullable|numeric|min:0',
        ]);

        $employe = Employe::find($validated['employe_id']);
        
        // Calculer les heures travaillées du mois
        $pointages = Pointage::where('employe_id', $employe->id)
            ->whereMonth('date_pointage', $validated['mois'])
            ->whereYear('date_pointage', $validated['annee'])
            ->get();

        $heures_normales = $pointages->sum('heures_travaillees');
        $heures_sup = $pointages->sum('heures_supplementaires');

        $fichePaie = new FichePaie([
            'employe_id' => $employe->id,
            'mois' => $validated['mois'],
            'annee' => $validated['annee'],
            'salaire_base' => $employe->salaire_base,
            'heures_normales' => $heures_normales,
            'heures_supplementaires' => $heures_sup,
            'prime_anciennete' => 0,
            'prime_rendement' => $validated['prime_rendement'] ?? 0,
            'autres_primes' => $validated['autres_primes'] ?? 0,
            'autres_deductions' => $validated['autres_deductions'] ?? 0,
            'est_declare_snapshot' => $employe->est_declare ?? true,
        ]);

        $fichePaie->calculerSalaire();
        $fichePaie->save();

        return redirect()->route('fiches-paie.index')
            ->with('success', 'Fiche de paie créée avec succès.');
    }

    public function show(FichePaie $fichesPaie)
    {
        $fichesPaie->load('employe');

        $warnings = [];
        if ($fichesPaie->employe && $fichesPaie->mode_remuneration_snapshot !== $fichesPaie->employe->mode_remuneration) {
            $warnings[] = "Le mode de rémunération de l'employé a changé depuis la génération de cette fiche (fiche: {$fichesPaie->mode_remuneration_snapshot}, actuel: {$fichesPaie->employe->mode_remuneration}).";
        }

        return Inertia::render('FichesPaie/Show', [
            'fichePaie' => $fichesPaie,
            'warnings' => $warnings,
        ]);
    }

    public function edit(FichePaie $fichesPaie)
    {
        $employes = Employe::orderBy('nom')->get();
        return Inertia::render('FichesPaie/Edit', ['fichePaie' => $fichesPaie, 'employes' => $employes]);
    }

    public function update(Request $request, FichePaie $fichesPaie)
    {
        $rules = [
            'salaire_base' => 'required|numeric|min:0',
            'autres_primes' => 'nullable|numeric|min:0',
            'autres_deductions' => 'nullable|numeric|min:0',
            'statut' => 'required|in:brouillon,valide,paye',
        ];

        if ($fichesPaie->mode_remuneration_snapshot === 'piece') {
            $rules['pieces_fabriquees'] = 'nullable|integer|min:0';
        } else {
            $rules['prime_rendement'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $fichesPaie->fill($validated);
        $fichesPaie->calculerSalaire();

        if ($validated['statut'] === 'paye' && !$fichesPaie->date_paiement) {
            $fichesPaie->date_paiement = today();
        }

        $fichesPaie->save();

        return redirect()->route('fiches-paie.index')
            ->with('success', 'Fiche de paie modifiée avec succès.');
    }

    public function destroy(FichePaie $fichesPaie)
    {
        $fichesPaie->delete();

        return redirect()->route('fiches-paie.index')
            ->with('success', 'Fiche de paie supprimée avec succès.');
    }

    // Générer les fiches de paie pour tous les employés d'un mois
    public function genererMasse(Request $request)
    {
        $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
        ]);

        $employes = Employe::where('statut', '!=', 'inactif')->get();
        $count = 0;
        $skipped = [];

        foreach ($employes as $employe) {
            // Vérifier que l'employé a un salaire de base
            if (!$employe->salaire_base || $employe->salaire_base <= 0) {
                $skipped[] = $employe->nom_complet;
                continue;
            }

            // Vérifier si la fiche existe déjà
            $exists = FichePaie::where('employe_id', $employe->id)
                ->where('mois', $request->mois)
                ->where('annee', $request->annee)
                ->exists();

            if (!$exists) {
                $pointages = Pointage::where('employe_id', $employe->id)
                    ->whereMonth('date_pointage', $request->mois)
                    ->whereYear('date_pointage', $request->annee)
                    ->get();

                $fichePaie = new FichePaie([
                    'employe_id' => $employe->id,
                    'mois' => $request->mois,
                    'annee' => $request->annee,
                    'salaire_base' => $employe->salaire_base,
                    'heures_normales' => $pointages->sum('heures_travaillees'),
                    'heures_supplementaires' => $pointages->sum('heures_supplementaires'),
                    'est_declare_snapshot' => $employe->est_declare ?? true,
                ]);

                $fichePaie->calculerSalaire();
                $fichePaie->save();
                $count++;
            }
        }

        $message = "{$count} fiches de paie générées avec succès.";
        if (count($skipped) > 0) {
            $message .= " ⚠️ Ignorés (sans salaire): " . implode(', ', $skipped);
        }

        return redirect()->route('fiches-paie.index')
            ->with('success', $message);
    }

    // Exporter vers Excel
    public function exporterExcel(Request $request)
    {
        $query = FichePaie::with('employe');

        if ($request->filled('mois')) {
            $query->where('mois', $request->mois);
        }

        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }

        $fichesPaie = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fiches de Paie');

        // En-têtes
        $headers = ['Matricule', 'Nom', 'Prénom', 'Période', 'Mode Rémun.', 'Salaire Base',
                    'H.Sup Montant', 'Pièces', 'Primes', 'Salaire Brut', 'Déductions', 'Salaire Net', 'Statut'];
        $sheet->fromArray($headers, null, 'A1');

        // Style des en-têtes
        $lastCol = 'M';
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setRGB('FFFFFF');

        // Données
        $row = 2;
        foreach ($fichesPaie as $fiche) {
            $totalPrimes = $fiche->prime_anciennete + $fiche->prime_rendement + $fiche->autres_primes;

            $sheet->fromArray([
                $fiche->employe->matricule,
                $fiche->employe->nom,
                $fiche->employe->prenom,
                $fiche->periode,
                $fiche->mode_remuneration_snapshot === 'piece' ? 'Pièce' : 'Salaire',
                $fiche->salaire_base,
                $fiche->montant_heures_supplementaires,
                $fiche->pieces_fabriquees,
                $totalPrimes,
                $fiche->salaire_brut,
                $fiche->total_deductions,
                $fiche->salaire_net,
                $fiche->statut,
            ], null, "A{$row}");
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Télécharger
        $filename = 'fiches_paie_' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    // Importer depuis Excel
    public function importerExcel(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('fichier');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $count = 0;
        $errors = [];

        // Skip header row
        foreach (array_slice($rows, 1) as $index => $row) {
            try {
                $matricule = $row[0] ?? null;
                if (!$matricule) continue;

                $employe = Employe::where('matricule', $matricule)->first();
                if (!$employe) {
                    $errors[] = "Ligne " . ($index + 2) . ": Matricule {$matricule} non trouvé";
                    continue;
                }

                // Format attendu: Matricule, Mois, Année, Primes...
                $mois = intval($row[1] ?? date('m'));
                $annee = intval($row[2] ?? date('Y'));

                $fichePaie = FichePaie::firstOrNew([
                    'employe_id' => $employe->id,
                    'mois' => $mois,
                    'annee' => $annee,
                ]);

                $fichePaie->salaire_base = $employe->salaire_base;
                $fichePaie->prime_anciennete = floatval($row[3] ?? 0);
                $fichePaie->prime_rendement = floatval($row[4] ?? 0);
                // prime_transport removed - skip column 5
                $fichePaie->autres_primes = floatval($row[6] ?? 0);
                $fichePaie->autres_deductions = floatval($row[7] ?? 0);

                $fichePaie->calculerSalaire();
                $fichePaie->save();
                $count++;

            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "{$count} fiches de paie importées.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', array_slice($errors, 0, 3));
        }

        return redirect()->route('fiches-paie.index')->with('success', $message);
    }

    // Imprimer une fiche de paie
    public function imprimer(FichePaie $fichesPaie)
    {
        $fichesPaie->load('employe');
        return view('fiches-paie.imprimer', compact('fichesPaie'));
    }

    /**
     * Get pointages for a fiche (for the retards modal)
     */
    public function getPointages(FichePaie $fichePaie)
    {
        // Get pointages for the fiche's specific month/year
        $pointages = $fichePaie->getPointagesDuMois();
        
        return response()->json([
            'pointages' => $pointages,
            'fiche' => $fichePaie->load('employe'),
            'debug' => [
                'fiche_mois' => $fichePaie->mois,
                'fiche_annee' => $fichePaie->annee,
                'employe_id' => $fichePaie->employe_id,
                'pointages_count' => $pointages->count(),
            ],
        ]);
    }

    /**
     * Update pointages and deductions for retards/absences
     */
    public function updateRetards(Request $request, FichePaie $fichePaie)
    {
        $validated = $request->validate([
            'days' => 'array',
            'days.*.id' => 'nullable|integer',
            'days.*.date' => 'required|date',
            'days.*.statut' => 'required|string',
            'days.*.heure_entree' => 'nullable|string',
            'days.*.heure_sortie' => 'nullable|string',
            'days.*.is_late' => 'nullable|boolean',
            'days.*.penalty' => 'nullable|numeric|min:0',
            'days.*.included' => 'nullable|boolean',
            'total_penalty' => 'nullable|numeric|min:0',
            'net_a_payer' => 'nullable|numeric|min:0',
            'jours_travailles' => 'nullable|integer|min:0',
            'jours_ponderes' => 'nullable|numeric|min:0',
            'pieces_fabriquees' => 'nullable|integer|min:0',
            'prime_par_piece' => 'nullable|numeric|min:0',
        ]);

        // Update or create pointages for each day
        foreach ($validated['days'] as $dayData) {
            $date = $dayData['date'];
            
            // Skip days without any data (no times, not included)
            $hasData = !empty($dayData['heure_entree']) || 
                       !empty($dayData['heure_sortie']) || 
                       ($dayData['included'] ?? false) ||
                       ($dayData['is_late'] ?? false);
            
            // Find existing pointage
            $pointage = Pointage::where('employe_id', $fichePaie->employe_id)
                ->whereDate('date_pointage', $date)
                ->first();
            
            if (!$pointage && $hasData) {
                $pointage = new Pointage([
                    'employe_id' => $fichePaie->employe_id,
                    'date_pointage' => $date,
                ]);
            }
            
            if ($pointage) {
                // Set status - if late checkbox is checked, set as retard
                $pointage->statut = ($dayData['is_late'] ?? false) ? 'retard' : $dayData['statut'];
                
                // Update times if provided
                if (!empty($dayData['heure_entree'])) {
                    $pointage->heure_entree = $date . ' ' . $dayData['heure_entree'] . ':00';
                } else {
                    $pointage->heure_entree = null;
                }
                if (!empty($dayData['heure_sortie'])) {
                    $pointage->heure_sortie = $date . ' ' . $dayData['heure_sortie'] . ':00';
                } else {
                    $pointage->heure_sortie = null;
                }
                
                // Recalculate hours if times provided
                if ($pointage->heure_entree && $pointage->heure_sortie) {
                    [$heures, $heures_sup] = Pointage::calculerHeures($pointage->heure_entree, $pointage->heure_sortie);
                    $pointage->heures_travaillees = $heures;
                    $pointage->heures_supplementaires = $heures_sup;
                } else {
                    $pointage->heures_travaillees = 0;
                    $pointage->heures_supplementaires = 0;
                }
                
                $pointage->save();
            }
        }

        // Sum overtime hours from all pointages for this month and update fiche
        $totalHeuresSup = Pointage::where('employe_id', $fichePaie->employe_id)
            ->whereBetween('date_pointage', [
                $fichePaie->annee . '-' . str_pad($fichePaie->mois, 2, '0', STR_PAD_LEFT) . '-01',
                $fichePaie->annee . '-' . str_pad($fichePaie->mois, 2, '0', STR_PAD_LEFT) . '-31',
            ])
            ->sum('heures_supplementaires');
        $fichePaie->heures_supplementaires = round($totalHeuresSup, 2);

        // Update jours_travailles and jours_ponderes FIRST (affects ratio_presence for calculerSalaire)
        if (isset($validated['jours_travailles'])) {
            $fichePaie->jours_travailles = $validated['jours_travailles'];
        }
        if (isset($validated['jours_ponderes'])) {
            $fichePaie->jours_ponderes = $validated['jours_ponderes'];
        }
        
        // Update pieces_fabriquees and prime_par_piece if provided (piece employees)
        if (isset($validated['pieces_fabriquees'])) {
            $fichePaie->pieces_fabriquees = $validated['pieces_fabriquees'];
            // Auto-sync snapshot if not yet set
            if ($fichePaie->mode_remuneration_snapshot !== 'piece' && $fichePaie->employe && $fichePaie->employe->mode_remuneration === 'piece') {
                $fichePaie->mode_remuneration_snapshot = 'piece';
            }
        }
        if (isset($validated['prime_par_piece'])) {
            $fichePaie->prime_par_piece_snapshot = $validated['prime_par_piece'];
        }

        // Set penalties
        $fichePaie->deduction_retard = $validated['total_penalty'] ?? 0;
        $fichePaie->deduction_absence = 0;
        
        // Recalculate salary with new ratio (prorates brut, CNAS, IRG)
        $fichePaie->calculerSalaire();
        
        // Calculate net_a_payer (salaire_net - penalties)
        $fichePaie->calculerNetAPayer();
        
        $fichePaie->save();

        // Recalculate totals if linked to paie mensuelle
        if ($fichePaie->paieMensuelle) {
            $fichePaie->paieMensuelle->recalculerTotaux();
        }

        return redirect()->back()->with('success', 'Retards et déductions mis à jour.');
    }

    /**
     * Sync employee data to fiche de paie
     * Updates salaire_base and primes from current employee data
     */
    public function syncEmployeData(FichePaie $fichePaie)
    {
        $employe = $fichePaie->employe;
        
        if (!$employe) {
            return redirect()->back()->with('error', 'Employé non trouvé.');
        }

        // Update salary data from employee
        $fichePaie->salaire_base = $employe->salaire_base ?? 0;
        $fichePaie->prime_anciennete = $employe->prime_anciennete ?? 0;
        $fichePaie->prime_rendement = $employe->prime_rendement ?? 0;
        $fichePaie->autres_primes = $employe->autres_primes ?? 0;
        $fichePaie->mode_remuneration_snapshot = $employe->mode_remuneration ?? 'salaire';
        $fichePaie->prime_par_piece_snapshot = $employe->prime_par_piece;
        $fichePaie->est_declare_snapshot = $employe->est_declare ?? true;
        
        // Recalculate salary with new data
        $fichePaie->calculerSalaire();
        $fichePaie->calculerNetAPayer();
        $fichePaie->save();

        // Recalculate totals if linked to paie mensuelle
        if ($fichePaie->paieMensuelle) {
            $fichePaie->paieMensuelle->recalculerTotaux();
        }

        return redirect()->back()->with('success', 'Données employé synchronisées.');
    }
}
