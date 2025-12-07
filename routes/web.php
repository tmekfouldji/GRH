<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\FichePaieController;
use App\Http\Controllers\ImportPointageController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
