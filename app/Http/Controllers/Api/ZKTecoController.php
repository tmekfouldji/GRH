<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Contrôleur pour intégration ZKTeco
 * Compatible avec les modèles: K40, K50, UA760, MB360, SpeedFace, etc.
 * 
 * Configuration ZKTeco:
 * - Menu > Comm. > Cloud Server Setting
 * - Enable Cloud Server: Yes
 * - Server Address: [IP_SERVEUR]
 * - Server Port: 8000
 * - HTTPS: No
 */
class ZKTecoController extends Controller
{
    /**
     * Recevoir les données ADMS Push de ZKTeco
     * Le format dépend du modèle, cette méthode gère les formats courants
     * 
     * POST /api/zkteco/receive
     */
    public function receive(Request $request)
    {
        // Log toutes les données reçues pour debug
        Log::info('ZKTeco Data Received', [
            'content' => $request->getContent(),
            'headers' => $request->headers->all(),
            'all' => $request->all()
        ]);

        // Format ADMS standard ZKTeco
        // SN=xxxx\tUSER_ID=xxx\tVERIFY_MODE=x\tTIME=xxxx-xx-xx xx:xx:xx\tSTATUS=x
        $content = $request->getContent();
        
        if (str_contains($content, 'USER_ID=')) {
            return $this->parseADMSFormat($content);
        }

        // Format JSON (certains modèles récents)
        if ($request->has('user_id') || $request->has('pin') || $request->has('emp_code')) {
            return $this->parseJSONFormat($request);
        }

        // Format table (ZKAccess)
        if ($request->has('Table')) {
            return $this->parseZKAccessFormat($request);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data received - format unknown',
            'raw' => $content
        ]);
    }

    /**
     * Parse format ADMS (texte avec tabulations)
     * Exemple: SN=ABCD1234\tUSER_ID=001\tVERIFY_MODE=1\tTIME=2024-01-15 08:30:00\tSTATUS=0
     */
    private function parseADMSFormat($content)
    {
        $lines = explode("\n", trim($content));
        $results = ['processed' => 0, 'errors' => []];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parser les paires clé=valeur
            $data = [];
            $pairs = preg_split('/\t/', $line);
            foreach ($pairs as $pair) {
                if (str_contains($pair, '=')) {
                    list($key, $value) = explode('=', $pair, 2);
                    $data[strtoupper(trim($key))] = trim($value);
                }
            }

            if (isset($data['USER_ID']) && isset($data['TIME'])) {
                $result = $this->processPointage(
                    $data['USER_ID'],
                    $data['TIME'],
                    $data['STATUS'] ?? '0', // 0=Check-In, 1=Check-Out
                    $data['SN'] ?? null
                );
                
                if ($result['success']) {
                    $results['processed']++;
                } else {
                    $results['errors'][] = $result['message'];
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$results['processed']} pointage(s) traité(s)",
            'errors' => $results['errors']
        ]);
    }

    /**
     * Parse format JSON (modèles récents)
     */
    private function parseJSONFormat(Request $request)
    {
        $matricule = $request->input('user_id') 
            ?? $request->input('pin') 
            ?? $request->input('emp_code')
            ?? $request->input('UserID');
        
        $datetime = $request->input('time') 
            ?? $request->input('punch_time') 
            ?? $request->input('DateTime')
            ?? now()->toDateTimeString();
        
        $status = $request->input('status') 
            ?? $request->input('punch_state') 
            ?? $request->input('State')
            ?? '0';
        
        $deviceSn = $request->input('sn') 
            ?? $request->input('device_sn') 
            ?? $request->input('SN');

        $result = $this->processPointage($matricule, $datetime, $status, $deviceSn);
        
        return response()->json($result);
    }

    /**
     * Parse format ZKAccess (logiciel PC)
     */
    private function parseZKAccessFormat(Request $request)
    {
        $table = $request->input('Table');
        
        if ($table === 'TRANSACTION') {
            $data = $request->input('Data', []);
            $results = ['processed' => 0, 'errors' => []];
            
            foreach ($data as $record) {
                $result = $this->processPointage(
                    $record['pin'] ?? $record['emp_code'] ?? null,
                    $record['time_stamp'] ?? $record['punch_time'] ?? null,
                    $record['status'] ?? '0',
                    $record['device_sn'] ?? null
                );
                
                if ($result['success']) {
                    $results['processed']++;
                } else {
                    $results['errors'][] = $result['message'];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$results['processed']} pointage(s) traité(s)",
                'errors' => $results['errors']
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Table ignored: ' . $table]);
    }

    /**
     * Traiter et enregistrer un pointage
     */
    private function processPointage($matricule, $datetime, $status, $deviceSn = null)
    {
        if (!$matricule || !$datetime) {
            return ['success' => false, 'message' => 'Données manquantes'];
        }

        // Chercher l'employé par matricule (essayer plusieurs formats)
        $employe = Employe::where('matricule', $matricule)
            ->orWhere('matricule', str_pad($matricule, 3, '0', STR_PAD_LEFT))
            ->orWhere('matricule', 'EMP' . str_pad($matricule, 3, '0', STR_PAD_LEFT))
            ->first();

        if (!$employe) {
            Log::warning("ZKTeco: Matricule non trouvé", ['matricule' => $matricule]);
            return ['success' => false, 'message' => "Matricule {$matricule} non trouvé"];
        }

        try {
            $dt = Carbon::parse($datetime);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Date invalide: {$datetime}"];
        }

        $date = $dt->toDateString();
        $heure = $dt->format('H:i:s');

        // Status ZKTeco: 0=Check-In, 1=Check-Out, 2=Break-Out, 3=Break-In, 4=OT-In, 5=OT-Out
        $isEntree = in_array($status, ['0', '2', '4', 0, 2, 4, 'check-in', 'in']);

        $pointage = Pointage::firstOrNew([
            'employe_id' => $employe->id,
            'date_pointage' => $date,
        ]);

        if ($isEntree) {
            // Ne pas écraser si déjà une entrée
            if (!$pointage->heure_entree) {
                $pointage->heure_entree = $heure;
                $pointage->statut = $dt->format('H:i') > '08:30' ? 'retard' : 'present';
            }
        } else {
            // Toujours mettre à jour la sortie (dernière sortie)
            $pointage->heure_sortie = $heure;
            
            if ($pointage->heure_entree) {
                list($heuresNormales, $heuresSup) = Pointage::calculerHeures(
                    $pointage->heure_entree,
                    $heure
                );
                $pointage->heures_travaillees = $heuresNormales;
                $pointage->heures_supplementaires = $heuresSup;
            }
        }

        if ($deviceSn) {
            $pointage->commentaire = "ZKTeco SN: {$deviceSn}";
        }

        $pointage->save();

        Log::info('ZKTeco: Pointage enregistré', [
            'employe' => $employe->nom_complet,
            'type' => $isEntree ? 'entree' : 'sortie',
            'heure' => $heure
        ]);

        return [
            'success' => true,
            'message' => 'Pointage enregistré',
            'employe' => $employe->nom_complet,
            'type' => $isEntree ? 'entree' : 'sortie',
            'heure' => $heure
        ];
    }

    /**
     * Handshake pour ZKTeco ADMS
     * GET /api/zkteco/cdata
     */
    public function cdata(Request $request)
    {
        $sn = $request->input('SN', 'UNKNOWN');
        
        Log::info('ZKTeco CDATA Request', ['SN' => $sn, 'params' => $request->all()]);

        // Réponse standard pour ADMS
        return response("OK", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Recevoir les données iclock (ancien protocole)
     * POST /api/zkteco/iclock/cdata
     */
    public function iclock(Request $request)
    {
        return $this->receive($request);
    }

    /**
     * GetRequest - ZKTeco demande les commandes en attente
     * GET /api/zkteco/getrequest
     */
    public function getRequest(Request $request)
    {
        // Pour l'instant, pas de commandes à envoyer à la pointeuse
        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * DeviceCmd - Recevoir confirmation des commandes
     * POST /api/zkteco/devicecmd
     */
    public function deviceCmd(Request $request)
    {
        Log::info('ZKTeco DeviceCmd', $request->all());
        return response("OK", 200)->header('Content-Type', 'text/plain');
    }
}
