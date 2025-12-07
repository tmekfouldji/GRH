<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PointeuseController;
use App\Http\Controllers\Api\ZKTecoController;

/*
|--------------------------------------------------------------------------
| API Routes - Pointeuse
|--------------------------------------------------------------------------
|
| Routes pour l'intégration avec les pointeuses réseau
| URL de base: http://[IP_SERVEUR]:8000/api/
|
*/

// Routes génériques pointeuse
Route::prefix('pointeuse')->group(function () {
    Route::get('/ping', [PointeuseController::class, 'ping']);
    Route::get('/employes', [PointeuseController::class, 'listeEmployes']);
    Route::get('/statut/{matricule}', [PointeuseController::class, 'statutEmploye']);
    Route::post('/pointage', [PointeuseController::class, 'enregistrerPointage']);
    Route::post('/import', [PointeuseController::class, 'importMasse']);
});

/*
|--------------------------------------------------------------------------
| ZKTeco ADMS / Push Protocol
|--------------------------------------------------------------------------
| Compatible: K40, K50, UA760, MB360, SpeedFace-V5L, UFace, etc.
| 
| Configuration sur la pointeuse:
|   Menu > Comm. > Cloud Server Setting
|   - Server Address: [IP_SERVEUR]
|   - Server Port: 8000
|   - Enable: Yes
*/
Route::prefix('zkteco')->group(function () {
    // Endpoint principal - réception des pointages
    Route::match(['get', 'post'], '/cdata', [ZKTecoController::class, 'cdata']);
    Route::post('/receive', [ZKTecoController::class, 'receive']);
    
    // ADMS Protocol endpoints
    Route::match(['get', 'post'], '/iclock/cdata', [ZKTecoController::class, 'iclock']);
    Route::get('/getrequest', [ZKTecoController::class, 'getRequest']);
    Route::post('/devicecmd', [ZKTecoController::class, 'deviceCmd']);
});

// Alias pour compatibilité iclock
Route::match(['get', 'post'], '/iclock/cdata', [ZKTecoController::class, 'iclock']);
Route::match(['get', 'post'], '/cdata', [ZKTecoController::class, 'cdata']);
