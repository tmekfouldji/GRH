<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use App\Models\Employe;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class PointageController extends Controller
{
    public function index(Request $request)
    {
        $query = Pointage::with('employe');

        // Filtre par plage de dates
        if ($request->filled('date_debut')) {
            $query->whereDate('date_pointage', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_pointage', '<=', $request->date_fin);
        }

        // Ancien filtre par date unique (compatibilité)
        if ($request->filled('date') && !$request->filled('date_debut')) {
            $query->whereDate('date_pointage', $request->date);
        }

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $pointages = $query->orderBy('date_pointage', 'desc')
                          ->orderBy('heure_entree', 'desc')
                          ->paginate(25);
        
        $employes = Employe::where('statut', 'actif')->orderBy('matricule')->get();

        return Inertia::render('Pointages/Index', [
            'pointages' => $pointages,
            'employes' => $employes,
            'filters' => $request->only(['date', 'date_debut', 'date_fin', 'employe_id', 'statut']),
        ]);
    }

    public function create()
    {
        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();
        return Inertia::render('Pointages/Create', ['employes' => $employes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'date_pointage' => 'required|date',
            'heure_entree' => 'nullable',
            'heure_sortie' => 'nullable',
            'statut' => 'required|in:present,absent,retard,conge,maladie,mission',
            'commentaire' => 'nullable|string',
        ]);

        // Calculer les heures travaillées
        if ($validated['heure_entree'] && $validated['heure_sortie']) {
            list($heures_normales, $heures_sup) = Pointage::calculerHeures(
                $validated['heure_entree'], 
                $validated['heure_sortie']
            );
            $validated['heures_travaillees'] = $heures_normales;
            $validated['heures_supplementaires'] = $heures_sup;
        }

        Pointage::create($validated);

        return redirect()->route('pointages.index')
            ->with('success', 'Pointage enregistré avec succès.');
    }

    public function edit(Pointage $pointage)
    {
        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();
        return Inertia::render('Pointages/Edit', ['pointage' => $pointage, 'employes' => $employes]);
    }

    public function update(Request $request, Pointage $pointage)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'date_pointage' => 'required|date',
            'heure_entree' => 'nullable',
            'heure_sortie' => 'nullable',
            'statut' => 'required|in:present,absent,retard,conge,maladie,mission',
            'commentaire' => 'nullable|string',
        ]);

        // Calculer les heures travaillées
        if ($validated['heure_entree'] && $validated['heure_sortie']) {
            list($heures_normales, $heures_sup) = Pointage::calculerHeures(
                $validated['heure_entree'], 
                $validated['heure_sortie']
            );
            $validated['heures_travaillees'] = $heures_normales;
            $validated['heures_supplementaires'] = $heures_sup;
        }

        $pointage->update($validated);

        return redirect()->route('pointages.index')
            ->with('success', 'Pointage modifié avec succès.');
    }

    public function destroy(Pointage $pointage)
    {
        $pointage->delete();

        return redirect()->route('pointages.index')
            ->with('success', 'Pointage supprimé avec succès.');
    }

    // Pointage rapide (entrée)
    public function entree(Request $request)
    {
        $request->validate(['employe_id' => 'required|exists:employes,id']);

        $pointage = Pointage::firstOrCreate(
            [
                'employe_id' => $request->employe_id,
                'date_pointage' => today(),
            ],
            [
                'heure_entree' => now()->format('H:i:s'),
                'statut' => 'present',
            ]
        );

        if (!$pointage->wasRecentlyCreated) {
            $pointage->update(['heure_entree' => now()->format('H:i:s')]);
        }

        return redirect()->back()->with('success', 'Entrée enregistrée.');
    }

    // Pointage rapide (sortie)
    public function sortie(Request $request)
    {
        $request->validate(['employe_id' => 'required|exists:employes,id']);

        $pointage = Pointage::where('employe_id', $request->employe_id)
            ->where('date_pointage', today())
            ->first();

        if ($pointage) {
            $heure_sortie = now()->format('H:i:s');
            list($heures_normales, $heures_sup) = Pointage::calculerHeures(
                $pointage->heure_entree, 
                $heure_sortie
            );

            $pointage->update([
                'heure_sortie' => $heure_sortie,
                'heures_travaillees' => $heures_normales,
                'heures_supplementaires' => $heures_sup,
            ]);

            return redirect()->back()->with('success', 'Sortie enregistrée.');
        }

        return redirect()->back()->with('error', 'Aucune entrée trouvée pour aujourd\'hui.');
    }

    // Vue du rapport journalier
    public function rapportJournalier(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $employes = Employe::where('statut', 'actif')
            ->with(['pointages' => function($q) use ($date) {
                $q->where('date_pointage', $date);
            }])
            ->orderBy('nom')
            ->get();

        $stats = [
            'total' => $employes->count(),
            'presents' => 0,
            'absents' => 0,
            'retards' => 0,
            'conges' => 0,
        ];

        foreach ($employes as $employe) {
            $pointage = $employe->pointages->first();
            if ($pointage) {
                $stats[$pointage->statut === 'present' ? 'presents' : $pointage->statut . 's']++;
            } else {
                $stats['absents']++;
            }
        }

        return Inertia::render('Pointages/Rapport', [
            'employes' => $employes,
            'date' => $date,
            'stats' => $stats,
        ]);
    }
}
