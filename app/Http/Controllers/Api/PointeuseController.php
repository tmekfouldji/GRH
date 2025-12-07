<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PointeuseController extends Controller
{
    /**
     * Enregistrer un pointage depuis la pointeuse
     * POST /api/pointeuse/pointage
     * 
     * Body: {
     *   "matricule": "EMP001",
     *   "type": "entree" | "sortie",
     *   "datetime": "2024-01-15 08:30:00" (optionnel, défaut: now),
     *   "device_id": "POINTEUSE_01" (optionnel)
     * }
     */
    public function enregistrerPointage(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'required|string',
            'type' => 'required|in:entree,sortie',
            'datetime' => 'nullable|date',
            'device_id' => 'nullable|string',
        ]);

        // Trouver l'employé par matricule
        $employe = Employe::where('matricule', $validated['matricule'])->first();

        if (!$employe) {
            Log::warning("Pointeuse: Matricule inconnu", ['matricule' => $validated['matricule']]);
            return response()->json([
                'success' => false,
                'message' => 'Employé non trouvé',
                'matricule' => $validated['matricule']
            ], 404);
        }

        $datetime = isset($validated['datetime']) 
            ? Carbon::parse($validated['datetime']) 
            : now();

        $date = $datetime->toDateString();
        $heure = $datetime->format('H:i:s');

        // Chercher ou créer le pointage du jour
        $pointage = Pointage::firstOrNew([
            'employe_id' => $employe->id,
            'date_pointage' => $date,
        ]);

        if ($validated['type'] === 'entree') {
            $pointage->heure_entree = $heure;
            $pointage->statut = 'present';
            
            // Vérifier retard (après 8h30)
            if ($datetime->format('H:i') > '08:30') {
                $pointage->statut = 'retard';
            }
        } else {
            $pointage->heure_sortie = $heure;
            
            // Calculer les heures si entrée existe
            if ($pointage->heure_entree) {
                list($heuresNormales, $heuresSup) = Pointage::calculerHeures(
                    $pointage->heure_entree,
                    $heure
                );
                $pointage->heures_travaillees = $heuresNormales;
                $pointage->heures_supplementaires = $heuresSup;
            }
        }

        $pointage->commentaire = $pointage->commentaire 
            ? $pointage->commentaire . " | Device: " . ($validated['device_id'] ?? 'N/A')
            : "Device: " . ($validated['device_id'] ?? 'N/A');

        $pointage->save();

        Log::info("Pointeuse: Pointage enregistré", [
            'employe' => $employe->nom_complet,
            'type' => $validated['type'],
            'datetime' => $datetime->toDateTimeString()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pointage enregistré',
            'data' => [
                'employe' => $employe->nom_complet,
                'matricule' => $employe->matricule,
                'type' => $validated['type'],
                'heure' => $heure,
                'date' => $date,
            ]
        ]);
    }

    /**
     * Import en masse depuis fichier pointeuse
     * POST /api/pointeuse/import
     * 
     * Body: {
     *   "pointages": [
     *     {"matricule": "EMP001", "type": "entree", "datetime": "2024-01-15 08:30:00"},
     *     {"matricule": "EMP001", "type": "sortie", "datetime": "2024-01-15 17:00:00"},
     *   ]
     * }
     */
    public function importMasse(Request $request)
    {
        $validated = $request->validate([
            'pointages' => 'required|array',
            'pointages.*.matricule' => 'required|string',
            'pointages.*.type' => 'required|in:entree,sortie',
            'pointages.*.datetime' => 'required|date',
        ]);

        $results = ['success' => 0, 'errors' => []];

        foreach ($validated['pointages'] as $index => $data) {
            $employe = Employe::where('matricule', $data['matricule'])->first();
            
            if (!$employe) {
                $results['errors'][] = "Ligne {$index}: Matricule {$data['matricule']} non trouvé";
                continue;
            }

            $datetime = Carbon::parse($data['datetime']);
            $date = $datetime->toDateString();
            $heure = $datetime->format('H:i:s');

            $pointage = Pointage::firstOrNew([
                'employe_id' => $employe->id,
                'date_pointage' => $date,
            ]);

            if ($data['type'] === 'entree') {
                $pointage->heure_entree = $heure;
                $pointage->statut = $datetime->format('H:i') > '08:30' ? 'retard' : 'present';
            } else {
                $pointage->heure_sortie = $heure;
                if ($pointage->heure_entree) {
                    list($heuresNormales, $heuresSup) = Pointage::calculerHeures(
                        $pointage->heure_entree, $heure
                    );
                    $pointage->heures_travaillees = $heuresNormales;
                    $pointage->heures_supplementaires = $heuresSup;
                }
            }

            $pointage->save();
            $results['success']++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$results['success']} pointages importés",
            'errors' => $results['errors']
        ]);
    }

    /**
     * Récupérer la liste des employés (pour synchronisation pointeuse)
     * GET /api/pointeuse/employes
     */
    public function listeEmployes()
    {
        $employes = Employe::where('statut', 'actif')
            ->select('id', 'matricule', 'nom', 'prenom', 'poste', 'departement')
            ->orderBy('matricule')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $employes->count(),
            'data' => $employes
        ]);
    }

    /**
     * Vérifier le statut d'un employé
     * GET /api/pointeuse/statut/{matricule}
     */
    public function statutEmploye($matricule)
    {
        $employe = Employe::where('matricule', $matricule)->first();

        if (!$employe) {
            return response()->json(['success' => false, 'message' => 'Employé non trouvé'], 404);
        }

        $pointageAujourdhui = Pointage::where('employe_id', $employe->id)
            ->where('date_pointage', today())
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'employe' => $employe->nom_complet,
                'matricule' => $employe->matricule,
                'statut_employe' => $employe->statut,
                'pointage_aujourdhui' => $pointageAujourdhui ? [
                    'heure_entree' => $pointageAujourdhui->heure_entree,
                    'heure_sortie' => $pointageAujourdhui->heure_sortie,
                    'statut' => $pointageAujourdhui->statut,
                ] : null
            ]
        ]);
    }

    /**
     * Ping pour vérifier la connexion
     * GET /api/pointeuse/ping
     */
    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'API Pointeuse connectée',
            'server_time' => now()->toDateTimeString(),
            'version' => '1.0'
        ]);
    }
}
