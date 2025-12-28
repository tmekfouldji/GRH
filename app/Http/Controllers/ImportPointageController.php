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
            
            // Log les données BRUTES avant toute modification
            Log::info("Import: " . count($rows) . " lignes lues AVANT réparation");
            Log::info("Import: Nombre de colonnes ligne 1: " . (isset($rows[0]) ? count($rows[0]) : 0));
            if (isset($rows[0])) {
                Log::info("Import BRUT ligne 1: " . json_encode($rows[0]));
            }
            if (isset($rows[1])) {
                Log::info("Import BRUT ligne 2: " . json_encode($rows[1]));
            }
            
            // Vérifier si les données sont corrompues (contiennent @ comme séparateur)
            // Cela arrive avec certains exports de pointeuses
            $rows = $this->fixCorruptedRows($rows);
            
            // Log les premières lignes APRES réparation
            Log::info("Import: " . count($rows) . " lignes APRES réparation");
            if (isset($rows[0])) {
                Log::info("Import REPARE ligne 1: " . json_encode(array_slice($rows[0], 0, 6)));
            }
            if (isset($rows[1])) {
                Log::info("Import REPARE ligne 2: " . json_encode(array_slice($rows[1], 0, 6)));
            }
            
            // Supprimer l'en-tête si présent
            if (isset($rows[0]) && (
                stripos($rows[0][0] ?? '', 'no') !== false ||
                stripos($rows[0][0] ?? '', 'ac') !== false ||
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

                // Structure du fichier attendu:
                // Col A (0): AC-No. (Code employé) - OBLIGATOIRE
                // Col B (1): Numéro (ignoré)
                // Col C (2): Nom employé
                // Col D (3): Date/Heure (DD/MM/YYYY HH:MM)
                // Col E (4): Type (C/In, C/Out)
                
                // Nettoyer les données de la ligne (supprimer null bytes et caractères invisibles)
                $cleanRow = array_map(function($val) {
                    if ($val === null) return '';
                    $val = preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$val);
                    return trim($val);
                }, $row);

                $codeEmploye = trim($cleanRow[0] ?? '');    // Col A - AC-No (Code employé)
                
                // Skip si pas de code employé (AC-No vide)
                if (empty($codeEmploye)) {
                    continue;
                }

                // Valider que le code employé est valide (numérique, 1-4 chiffres)
                if (!preg_match('/^\d{1,4}$/', $codeEmploye)) {
                    // Si le code n'est pas numérique pur, essayer d'extraire les chiffres
                    if (preg_match('/(\d{1,4})/', $codeEmploye, $m)) {
                        $codeEmploye = $m[1];
                    } else {
                        Log::warning("Import ligne {$lineNum}: Code employé invalide ignoré: " . substr($codeEmploye, 0, 30));
                        continue;
                    }
                }

                // Col B (index 1) ignoré
                $nomEmploye = trim($cleanRow[2] ?? '');     // Col C - Nom
                $dateTimeStr = trim($cleanRow[3] ?? '');    // Col D - Date/Heure
                $type = strtolower(trim($cleanRow[4] ?? '')); // Col E - Type

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
        if (empty($dateTimeStr)) {
            return null;
        }

        // Nettoyer la chaîne: supprimer les null bytes et caractères invisibles
        $dateTimeStr = preg_replace('/[\x00-\x1F\x7F]/u', '', $dateTimeStr);
        $dateTimeStr = trim($dateTimeStr);
        
        if (empty($dateTimeStr)) {
            return null;
        }

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
        $entreesTypes = ['c/in', 'cin', 'in', 'entree', 'entrée', 'check-in', 'checkin', 'arrivée', 'arrivee', 'overtime in'];
        $sortiesTypes = ['c/out', 'cout', 'out', 'sortie', 'check-out', 'checkout', 'départ', 'depart', 'out back', 'overtime out'];

        $type = strtolower(trim($type));

        // Vérification exacte d'abord
        if (in_array($type, $entreesTypes)) {
            return true;
        }

        if (in_array($type, $sortiesTypes)) {
            return false;
        }

        // Vérifier les patterns "overtime in" et "overtime out"
        if (str_contains($type, 'overtime out') || str_contains($type, 'out back')) {
            return false;
        }
        if (str_contains($type, 'overtime in')) {
            return true;
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
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                $sheet->setCellValue($cell, $value);
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
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                $sheet->setCellValue($cell, $value);
            }
        }

        Log::info("Import: HTML parsé, " . count($rows) . " lignes trouvées");
        return $spreadsheet;
    }

    /**
     * Réparer les lignes corrompues (données avec @ comme séparateur)
     * Certains exports de pointeuses produisent des données mal formatées
     */
    private function fixCorruptedRows($rows)
    {
        if (empty($rows)) {
            return $rows;
        }

        // Vérifier si les données sont corrompues en vérifiant plusieurs lignes
        $needsRepair = false;
        $checkRows = array_slice($rows, 0, min(5, count($rows)));
        
        foreach ($checkRows as $row) {
            $fullLine = implode('', array_map('strval', $row));
            // Si une ligne contient @ et des patterns comme dates, c'est corrompu
            if (str_contains($fullLine, '@') && preg_match('/\d{2}\/\d{2}\/\d{4}/', $fullLine)) {
                $needsRepair = true;
                break;
            }
        }
        
        // Si les données semblent normales (plus de 4 colonnes propres, pas de @)
        if (!$needsRepair && isset($rows[1]) && count($rows[1]) >= 4) {
            $firstDataCell = $rows[1][0] ?? '';
            if (!str_contains((string)$firstDataCell, '@') && preg_match('/^\d{1,3}$/', trim($firstDataCell))) {
                Log::info("Import: Données semblent normales, pas de réparation nécessaire");
                return $rows;
            }
        }

        Log::info("Import: Détection de données corrompues, tentative de réparation...");
        
        $fixedRows = [];
        
        foreach ($rows as $rowIndex => $row) {
            // Concaténer toutes les cellules de la ligne
            $fullLine = implode('', array_map('strval', $row));
            
            // Skip les lignes d'en-tête
            if ($rowIndex == 0 && (stripos($fullLine, 'AC-No') !== false || stripos($fullLine, 'Name') !== false)) {
                continue;
            }
            
            // Nettoyer les caractères de contrôle et caractères étranges
            $fullLine = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $fullLine);
            
            // Si la ligne contient @, c'est probablement le séparateur
            if (str_contains($fullLine, '@')) {
                $parts = explode('@', $fullLine);
                
                // Parser les parties pour extraire les données
                $fixedRow = $this->parseCorruptedParts($parts);
                if ($fixedRow) {
                    $fixedRows[] = $fixedRow;
                }
            } else {
                // Ligne sans @ - peut-être déjà formatée correctement
                if (count($row) >= 4) {
                    $fixedRows[] = $row;
                }
            }
        }
        
        Log::info("Import: " . count($fixedRows) . " lignes réparées sur " . count($rows) . " lignes originales");
        
        // Si aucune ligne réparée, retourner les originales pour essayer quand même
        if (empty($fixedRows)) {
            Log::warning("Import: Aucune ligne réparée, retour aux données originales");
            return $rows;
        }
        
        return $fixedRows;
    }

    /**
     * Parser les parties d'une ligne corrompue et reconstruire le format attendu
     * Format typique après split par @: ['', '28', '', 'BEN AMOUR AICHA', '30/11/2025 07:50', 'C/In', 'OverTime Out', 'FOT', '']
     */
    private function parseCorruptedParts($parts)
    {
        $code = null;
        $nom = null;
        $datetime = null;
        $type = null;
        
        foreach ($parts as $index => $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            // Nettoyer les caractères parasites (chiffres/lettres collés aux valeurs)
            $cleanPart = preg_replace('/^[^a-zA-Z0-9\/]+|[^a-zA-Z0-9\/\s:]+$/', '', $part);
            $cleanPart = trim($cleanPart);
            
            // Détecter le code employé (1-3 chiffres, peut être collé à d'autres caractères)
            if (!$code && preg_match('/^(\d{1,3})/', $cleanPart, $m)) {
                // S'assurer que c'est bien un code (pas une date)
                if (!preg_match('/\d{2}\/\d{2}\/\d{4}/', $part)) {
                    $code = $m[1];
                    continue;
                }
            }
            
            // Détecter la date/heure (format DD/MM/YYYY HH:MM)
            if (!$datetime && preg_match('/(\d{2}\/\d{2}\/\d{4}\s*\d{2}:\d{2})/', $part, $m)) {
                $datetime = $m[1];
                continue;
            }
            
            // Détecter le type (C/In, C/Out, OverTime In, OverTime Out, Out Back, etc.)
            if (!$type && preg_match('/(C\/In|C\/Out|OverTime\s*In|OverTime\s*Out|Out\s*Back)/i', $part, $m)) {
                $type = $m[1];
                continue;
            }
            
            // Détecter le nom (texte avec lettres, au moins 2 caractères, pas de mots réservés)
            if (!$nom && strlen($cleanPart) >= 2) {
                // Vérifier que c'est un nom (contient des lettres, pas de mots réservés)
                if (preg_match('/[a-zA-Z]{2,}/', $cleanPart) && 
                    !preg_match('/^(FOT|Invalid|Repeat|OverTime|C\/)/i', $cleanPart)) {
                    $nom = $cleanPart;
                    continue;
                }
            }
        }
        
        // Vérifier qu'on a les éléments essentiels (code et datetime)
        if ($code && $datetime) {
            Log::debug("Import parsed: code=$code, nom=" . ($nom ?? 'null') . ", datetime=$datetime, type=" . ($type ?? 'null'));
            return [$code, '', $nom ?? 'Inconnu', $datetime, $type ?? 'C/In'];
        }
        
        return null;
    }
}
