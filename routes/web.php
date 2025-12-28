<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\FichePaieController;
use App\Http\Controllers\PaieMensuelleController;
use App\Http\Controllers\ImportPointageController;
use Inertia\Inertia;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Guide Système de Paie
Route::get('/guide-salaire', function () {
    return Inertia::render('SalaireInfo');
})->name('guide-salaire');

// Employés
Route::resource('employes', EmployeController::class);

// Pointages
Route::get('pointages/import', [ImportPointageController::class, 'index'])->name('pointages.import');
Route::post('pointages/import', [ImportPointageController::class, 'import'])->name('pointages.import.store');
Route::get('pointages/import/template', [ImportPointageController::class, 'downloadTemplate'])->name('pointages.import.template');
Route::resource('pointages', PointageController::class)->except(['show']);
Route::post('pointages/entree', [PointageController::class, 'entree'])->name('pointages.entree');
Route::post('pointages/sortie', [PointageController::class, 'sortie'])->name('pointages.sortie');
Route::get('pointages/rapport/journalier', [PointageController::class, 'rapportJournalier'])->name('pointages.rapport');

// Congés
Route::resource('conges', CongeController::class);
Route::post('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver');
Route::post('conges/{conge}/refuser', [CongeController::class, 'refuser'])->name('conges.refuser');

// Fiches de paie
Route::resource('fiches-paie', FichePaieController::class);
Route::post('fiches-paie/generer-masse', [FichePaieController::class, 'genererMasse'])->name('fiches-paie.generer-masse');
Route::get('fiches-paie/exporter/excel', [FichePaieController::class, 'exporterExcel'])->name('fiches-paie.exporter');
Route::post('fiches-paie/importer/excel', [FichePaieController::class, 'importerExcel'])->name('fiches-paie.importer');
Route::get('fiches-paie/{fiches_paie}/imprimer', [FichePaieController::class, 'imprimer'])->name('fiches-paie.imprimer');
Route::post('fiches-paie/{fichePaie}/marquer-remis', [PaieMensuelleController::class, 'marquerRemis'])->name('fiches-paie.marquer-remis');

// Paies Mensuelles
Route::resource('paies-mensuelles', PaieMensuelleController::class)->except(['edit', 'update']);
Route::post('paies-mensuelles/{paiesMensuelle}/valider', [PaieMensuelleController::class, 'valider'])->name('paies-mensuelles.valider');
Route::post('paies-mensuelles/{paiesMensuelle}/demarrer-paiement', [PaieMensuelleController::class, 'demarrerPaiement'])->name('paies-mensuelles.demarrer-paiement');
Route::post('paies-mensuelles/{paiesMensuelle}/cloturer', [PaieMensuelleController::class, 'cloturer'])->name('paies-mensuelles.cloturer');
Route::post('paies-mensuelles/{paiesMensuelle}/marquer-tous-remis', [PaieMensuelleController::class, 'marquerTousRemis'])->name('paies-mensuelles.marquer-tous-remis');
Route::post('paies-mensuelles/{paiesMensuelle}/statut-taxes', [PaieMensuelleController::class, 'updateStatutTaxes'])->name('paies-mensuelles.statut-taxes');
Route::get('paies-mensuelles/{paiesMensuelle}/imprimer-tout', [PaieMensuelleController::class, 'imprimerTout'])->name('paies-mensuelles.imprimer-tout');
Route::get('paies-mensuelles/{paiesMensuelle}/rapport', [PaieMensuelleController::class, 'rapport'])->name('paies-mensuelles.rapport');
Route::post('paies-mensuelles/{paiesMensuelle}/valider-toutes-presences', [PaieMensuelleController::class, 'validerToutesPresences'])->name('paies-mensuelles.valider-toutes-presences');
Route::post('paies-mensuelles/{paiesMensuelle}/calculer-presences', [PaieMensuelleController::class, 'calculerToutesPresences'])->name('paies-mensuelles.calculer-presences');

// Validation des présences par fiche
Route::get('fiches-paie/{fichePaie}/validation-presences', [PaieMensuelleController::class, 'validationPresences'])->name('fiches-paie.validation-presences');
Route::post('fiches-paie/{fichePaie}/valider-presences', [PaieMensuelleController::class, 'validerPresencesEmploye'])->name('fiches-paie.valider-presences');
Route::post('fiches-paie/{fichePaie}/reouvrir-validation', [PaieMensuelleController::class, 'reouvrirValidation'])->name('fiches-paie.reouvrir-validation');
Route::post('pointages/{pointage}/modifier-validation', [PaieMensuelleController::class, 'modifierPointageValidation'])->name('pointages.modifier-validation');
