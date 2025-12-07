<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportPointageController extends Controller
{
    /**
     * Afficher la page d'import
     */
    public function index()
    {
        $employes = Employe::orderBy('nom')->get();
        
        return Inertia::render('Pointages/Import', [
            'employes' => $employes,
        ]);
    }

    /**
     * Importer les pointages depuis un fichier Excel
     * Format attendu:
     * - Col A: Numéro ligne
     * - Col B: Code employé
     * - Col C: Nom employé  
     * - Col D: Date/Heure (DD/MM/YYYY HH:MM)
     * - Col E: Type (C/In, C/Out, Out Back)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        // Vérifier l'extension manuellement
        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()
                ->with('error', 'Le fichier doit être au format Excel (.xlsx, .xls) ou CSV.');
        }

        $file = $request->file('file');
        
        try {
            // Charger le fichier avec le bon lecteur selon l'extension
            $spreadsheet = $this->loadSpreadsheet($file->getPathname(), $extension);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Supprimer l'en-tête si présent
            if (isset($rows[0]) && (
                stripos($rows[0][0] ?? '', 'no') !== false ||
                stripos($rows[0][2] ?? '', 'name') !== false ||
                stripos($rows[0][2] ?? '', 'nom') !== false
            )) {
                array_shift($rows);
            }

            $results = [
                'total' => 0,
                'imported' => 0,
                'updated' => 0,
                'errors' => [],
                'employees_created' => [],
            ];

            // Grouper les pointages par employé et date
            $pointagesGrouped = [];

            foreach ($rows as $index => $row) {
                $results['total']++;
                $lineNum = $index + 2;

                // Structure du fichier ZKTeco:
                // Col A (0): AC-No (code employé) - OBLIGATOIRE
                // Col B (1): Numéro (ignoré)
                // Col C (2): Nom employé
                // Col D (3): DateTime
                // Col E (4): Type (C/In, C/Out)
                
                $codeEmploye = trim($row[0] ?? '');
                
                // Skip si pas de code employé (AC-No vide)
                if (empty($codeEmploye)) {
                    continue;
                }

                // Colonne B (index 1) ignorée - c'est juste un numéro de ligne
                $nomEmploye = trim($row[2] ?? '');    // Col C
                $dateTimeStr = trim($row[3] ?? '');   // Col D
                $type = strtolower(trim($row[4] ?? '')); // Col E

                if (empty($dateTimeStr)) {
                    continue;
                }

                // Parser la date/heure (format: DD/MM/YYYY HH:MM)
                try {
                    // Gérer différents formats de date
                    $dateTime = $this->parseDateTime($dateTimeStr);
                    if (!$dateTime) {
                        $results['errors'][] = "Ligne {$lineNum}: Format de date invalide '{$dateTimeStr}'";
                        continue;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Ligne {$lineNum}: Erreur date '{$dateTimeStr}'";
                    continue;
                }

                // Trouver ou créer l'employé par code (AC-No.)
                $employe = $this->findOrCreateEmploye($codeEmploye, $nomEmploye, $results);
                
                if (!$employe) {
                    $results['errors'][] = "Ligne {$lineNum}: Code employé manquant";
                    continue;
                }

                // Déterminer si c'est une entrée ou sortie
                $isEntree = $this->isEntree($type);

                // Clé unique: employé + date
                $key = $employe->id . '_' . $dateTime->toDateString();

                if (!isset($pointagesGrouped[$key])) {
                    $pointagesGrouped[$key] = [
                        'employe_id' => $employe->id,
                        'date' => $dateTime->toDateString(),
                        'entrees' => [],  // Stocke les datetime complets
                        'sorties' => [],
                    ];
                }

                // Stocker le datetime complet (date + heure)
                if ($isEntree) {
                    $pointagesGrouped[$key]['entrees'][] = $dateTime->toDateTimeString();
                } else {
                    $pointagesGrouped[$key]['sorties'][] = $dateTime->toDateTimeString();
                }
            }

            // Créer/mettre à jour les pointages
            foreach ($pointagesGrouped as $data) {
                $pointage = Pointage::firstOrNew([
                    'employe_id' => $data['employe_id'],
                    'date_pointage' => $data['date'],
                ]);

                $isNew = !$pointage->exists;

                // Prendre la première entrée et la dernière sortie (datetime complet)
                if (!empty($data['entrees'])) {
                    sort($data['entrees']);
                    $premiereEntree = Carbon::parse($data['entrees'][0]);
                    $pointage->heure_entree = $premiereEntree;
                    
                    // Vérifier retard (après 8h30)
                    if ($premiereEntree->format('H:i') > '08:30') {
                        $pointage->statut = 'retard';
                    } else {
                        $pointage->statut = 'present';
                    }
                }

                if (!empty($data['sorties'])) {
                    sort($data['sorties']);
                    $derniereSortie = Carbon::parse(end($data['sorties']));
                    $pointage->heure_sortie = $derniereSortie;
                }

                // Calculer les heures travaillées avec datetime complets
                if ($pointage->heure_entree && $pointage->heure_sortie) {
                    list($heuresNormales, $heuresSup) = Pointage::calculerHeures(
                        $pointage->heure_entree,
                        $pointage->heure_sortie
                    );
                    $pointage->heures_travaillees = $heuresNormales;
                    $pointage->heures_supplementaires = $heuresSup;
                }

                $pointage->commentaire = 'Import Excel ' . now()->format('d/m/Y H:i');
                $pointage->save();

                if ($isNew) {
                    $results['imported']++;
                } else {
                    $results['updated']++;
                }
            }

            // Message de résultat
            $message = "{$results['imported']} pointages importés, {$results['updated']} mis à jour.";
            
            if (!empty($results['employees_created'])) {
                $nbCreated = count($results['employees_created']);
                $message .= " {$nbCreated} employé(s) créé(s): " . implode(', ', array_slice($results['employees_created'], 0, 5));
                if ($nbCreated > 5) {
                    $message .= " et " . ($nbCreated - 5) . " autres.";
                }
            }

            return redirect()->route('pointages.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Import Excel error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Parser la date/heure dans différents formats
     */
    private function parseDateTime($dateTimeStr)
    {
        $formats = [
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'd-m-Y H:i:s',
            'm/d/Y H:i',
            'm/d/Y H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateTimeStr);
                if ($date && $date->year > 2000) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Essayer le parsing automatique
        try {
            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Trouver ou créer l'employé par code (AC-No.)
     * Le code employé est l'identifiant principal
     */
    private function findOrCreateEmploye($code, $nom, &$results)
    {
        $code = trim($code);
        $nom = trim($nom);

        if (empty($code)) {
            return null;
        }

        // Chercher par matricule/code exact
        $employe = Employe::where('matricule', $code)->first();
        
        if ($employe) {
            return $employe;
        }

        // Chercher avec padding (01 = 1)
        $employe = Employe::where('matricule', ltrim($code, '0'))
            ->orWhere('matricule', str_pad($code, 2, '0', STR_PAD_LEFT))
            ->orWhere('matricule', str_pad($code, 3, '0', STR_PAD_LEFT))
            ->first();
        
        if ($employe) {
            return $employe;
        }

        // Employé n'existe pas → Créer automatiquement
        // Extraire nom et prénom du nom complet
        $nomParts = $this->parseNomComplet($nom);

        $employe = Employe::create([
            'matricule' => $code,
            'nom' => $nomParts['nom'],
            'prenom' => $nomParts['prenom'],
            'statut' => 'actif',
            // Tous les autres champs restent vides/null
            'email' => null,
            'telephone' => null,
            'adresse' => null,
            'date_naissance' => null,
            'date_embauche' => now()->toDateString(), // Date d'import par défaut
            'poste' => null,
            'departement' => null,
            'salaire_base' => null,
            'type_contrat' => null,
            'cin' => null,
            'cnss' => null,
        ]);

        // Tracker les employés créés
        if (!isset($results['employees_created'])) {
            $results['employees_created'] = [];
        }
        $results['employees_created'][] = "{$employe->nom} {$employe->prenom} ({$code})";

        Log::info("Import: Nouvel employé créé", [
            'matricule' => $code,
            'nom' => $nom
        ]);

        return $employe;
    }

    /**
     * Extraire nom et prénom d'un nom complet
     * Ex: "NABIL BBS" → ['prenom' => 'NABIL', 'nom' => 'BBS']
     */
    private function parseNomComplet($nomComplet)
    {
        $parts = preg_split('/\s+/', trim($nomComplet));
        
        if (count($parts) >= 2) {
            // Premier mot = prénom, reste = nom
            $prenom = array_shift($parts);
            $nom = implode(' ', $parts);
        } else {
            // Un seul mot = nom, prénom vide
            $nom = $nomComplet;
            $prenom = '';
        }

        return [
            'nom' => $nom,
            'prenom' => $prenom,
        ];
    }

    /**
     * Déterminer si c'est une entrée
     */
    private function isEntree($type)
    {
        $entreesTypes = ['c/in', 'cin', 'in', 'entree', 'entrée', 'check-in', 'checkin', 'arrivée', 'arrivee'];
        $sortiesTypes = ['c/out', 'cout', 'out', 'sortie', 'check-out', 'checkout', 'départ', 'depart', 'out back'];

        $type = strtolower(trim($type));

        if (in_array($type, $entreesTypes)) {
            return true;
        }

        if (in_array($type, $sortiesTypes)) {
            return false;
        }

        // Par défaut, vérifier si contient "in" ou "out"
        if (str_contains($type, 'in') && !str_contains($type, 'out')) {
            return true;
        }

        return false; // Par défaut: sortie
    }

    /**
     * Télécharger un modèle Excel
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Code');
        $sheet->setCellValue('C1', 'Nom Employé');
        $sheet->setCellValue('D1', 'Date/Heure');
        $sheet->setCellValue('E1', 'Type');

        // Exemple
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', '01');
        $sheet->setCellValue('C2', 'NABIL BBS');
        $sheet->setCellValue('D2', '01/12/2025 08:00');
        $sheet->setCellValue('E2', 'C/In');

        $sheet->setCellValue('A3', '1');
        $sheet->setCellValue('B3', '01');
        $sheet->setCellValue('C3', 'NABIL BBS');
        $sheet->setCellValue('D3', '01/12/2025 17:00');
        $sheet->setCellValue('E3', 'C/Out');

        // Style
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'modele_pointages.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Charger un fichier Excel/CSV avec le bon lecteur
     * Gère les fichiers HTML déguisés en .xls (export pointeuses)
     */
    private function loadSpreadsheet($filePath, $extension)
    {
        // Essayer les lecteurs dans l'ordre
        $readers = [];

        switch ($extension) {
            case 'xlsx':
                $readers = ['Xlsx', 'Xls', 'Html', 'Csv'];
                break;
            case 'xls':
                // Souvent les .xls sont en fait des HTML (export web)
                $readers = ['Xls', 'Html', 'Xlsx', 'Csv'];
                break;
            case 'csv':
                $readers = ['Csv'];
                break;
            default:
                $readers = ['Xlsx', 'Xls', 'Html', 'Csv'];
        }

        $lastException = null;

        foreach ($readers as $readerType) {
            try {
                $readerClass = "\\PhpOffice\\PhpSpreadsheet\\Reader\\{$readerType}";
                $reader = new $readerClass();

                // Configuration spéciale pour CSV
                if ($readerType === 'Csv') {
                    $reader->setDelimiter(';');
                    $reader->setEnclosure('"');
                    $reader->setSheetIndex(0);
                }

                // Vérifier si le fichier peut être lu
                if ($reader->canRead($filePath)) {
                    Log::info("Import: Lecture avec {$readerType}");
                    return $reader->load($filePath);
                }
            } catch (\Exception $e) {
                $lastException = $e;
                Log::debug("Import: {$readerType} a échoué - " . $e->getMessage());
                continue;
            }
        }

        // Dernier recours: parsing manuel du fichier texte
        Log::info("Import: Tentative de parsing manuel");
        return $this->parseAsText($filePath);
    }

    /**
     * Parser le fichier comme texte brut (format pointeuse)
     */
    private function parseAsText($filePath)
    {
        $content = file_get_contents($filePath);
        
        // Détecter l'encodage et convertir en UTF-8
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Nettoyer le contenu HTML si présent
        if (stripos($content, '<html') !== false || stripos($content, '<table') !== false) {
            return $this->parseHtmlTable($content);
        }

        // Parser comme texte délimité (tab, virgule, point-virgule)
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Détecter le délimiteur
            if (str_contains($line, "\t")) {
                $row = explode("\t", $line);
            } elseif (str_contains($line, ';')) {
                $row = explode(';', $line);
            } elseif (str_contains($line, ',')) {
                $row = str_getcsv($line);
            } else {
                $row = preg_split('/\s{2,}/', $line); // Espaces multiples
            }

            $rows[] = array_map('trim', $row);
        }

        // Créer un spreadsheet à partir des données
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
            }
        }

        return $spreadsheet;
    }

    /**
     * Parser un tableau HTML
     */
    private function parseHtmlTable($html)
    {
        $rows = [];

        // Extraire les lignes du tableau
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $html, $trMatches);

        foreach ($trMatches[1] as $tr) {
            $row = [];
            // Extraire les cellules (td ou th)
            preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/si', $tr, $tdMatches);
            foreach ($tdMatches[1] as $cell) {
                // Nettoyer le HTML
                $value = strip_tags($cell);
                $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                $value = trim(preg_replace('/\s+/', ' ', $value));
                $row[] = $value;
            }
            if (!empty($row)) {
                $rows[] = $row;
            }
        }

        // Créer un spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
            }
        }

        Log::info("Import: HTML parsé, " . count($rows) . " lignes trouvées");
        return $spreadsheet;
    }
}
