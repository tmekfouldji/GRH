<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CongeController extends Controller
{
    public function index(Request $request)
    {
        $query = Conge::with('employe');

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $conges = $query->orderBy('created_at', 'desc')->paginate(15);
        $employes = Employe::where('statut', '!=', 'inactif')->orderBy('nom')->get();

        return Inertia::render('Conges/Index', [
            'conges' => $conges,
            'employes' => $employes,
            'filters' => $request->only(['employe_id', 'statut', 'type']),
        ]);
    }

    public function create()
    {
        $employes = Employe::where('statut', '!=', 'inactif')->orderBy('nom')->get();
        return Inertia::render('Conges/Create', ['employes' => $employes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'type' => 'required|in:annuel,maladie,maternite,paternite,sans_solde,exceptionnel',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string',
        ]);

        $validated['nombre_jours'] = Conge::calculerNombreJours(
            $validated['date_debut'], 
            $validated['date_fin']
        );
        $validated['statut'] = 'en_attente';

        Conge::create($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé enregistrée avec succès.');
    }

    public function show(Conge $conge)
    {
        $conge->load('employe');
        return Inertia::render('Conges/Show', ['conge' => $conge]);
    }

    public function edit(Conge $conge)
    {
        $employes = Employe::where('statut', '!=', 'inactif')->orderBy('nom')->get();
        return Inertia::render('Conges/Edit', ['conge' => $conge, 'employes' => $employes]);
    }

    public function update(Request $request, Conge $conge)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'type' => 'required|in:annuel,maladie,maternite,paternite,sans_solde,exceptionnel',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string',
            'statut' => 'required|in:en_attente,approuve,refuse,annule',
            'commentaire_responsable' => 'nullable|string',
        ]);

        $validated['nombre_jours'] = Conge::calculerNombreJours(
            $validated['date_debut'], 
            $validated['date_fin']
        );

        if ($validated['statut'] !== $conge->statut && 
            in_array($validated['statut'], ['approuve', 'refuse'])) {
            $validated['date_validation'] = today();
        }

        $conge->update($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Congé modifié avec succès.');
    }

    public function destroy(Conge $conge)
    {
        $conge->delete();

        return redirect()->route('conges.index')
            ->with('success', 'Congé supprimé avec succès.');
    }

    // Approuver un congé
    public function approuver(Request $request, Conge $conge)
    {
        $conge->update([
            'statut' => 'approuve',
            'date_validation' => today(),
            'commentaire_responsable' => $request->commentaire_responsable,
        ]);

        // Mettre à jour le statut de l'employé si le congé commence aujourd'hui
        if ($conge->date_debut <= today() && $conge->date_fin >= today()) {
            $conge->employe->update(['statut' => 'conge']);
        }

        return redirect()->back()->with('success', 'Congé approuvé.');
    }

    // Refuser un congé
    public function refuser(Request $request, Conge $conge)
    {
        $request->validate(['commentaire_responsable' => 'required|string']);

        $conge->update([
            'statut' => 'refuse',
            'date_validation' => today(),
            'commentaire_responsable' => $request->commentaire_responsable,
        ]);

        return redirect()->back()->with('success', 'Congé refusé.');
    }
}
