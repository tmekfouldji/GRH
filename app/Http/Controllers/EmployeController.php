<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employe::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('departement')) {
            $query->where('departement', $request->departement);
        }

        // Handle per_page parameter
        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all') {
            $employes = $query->orderBy('nom')->get();
            // Wrap in pagination-like structure for frontend compatibility
            $employes = [
                'data' => $employes,
                'total' => $employes->count(),
                'last_page' => 1,
                'links' => [],
            ];
        } else {
            $employes = $query->orderBy('nom')->paginate((int) $perPage);
        }
        
        $departements = Employe::distinct()->pluck('departement')->filter();

        return Inertia::render('Employes/Index', [
            'employes' => $employes,
            'departements' => $departements,
            'filters' => $request->only(['search', 'statut', 'departement', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Employes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'required|unique:employes,matricule|max:20',
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|max:20',
            'poste' => 'nullable|max:100',
            'categorie' => 'nullable|max:50',
            'echelon' => 'nullable|max:20',
            'departement' => 'nullable|max:100',
            'date_embauche' => 'required|date',
            'salaire_base' => 'required|numeric|min:0',
            'prime_transport_defaut' => 'nullable|numeric|min:0',
            'prime_panier_defaut' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,inactif,conge',
            'adresse' => 'nullable|string',
            'cin' => 'nullable|max:20',
            'numero_cnas' => 'nullable|max:30',
            'mode_paiement' => 'nullable|in:virement,especes,cheque',
            'rib' => 'nullable|max:30',
        ]);

        Employe::create($validated);

        return redirect()->route('employes.index')
            ->with('success', 'Employé ajouté avec succès.');
    }

    public function show(Employe $employe)
    {
        $employe->load(['pointages' => function($q) {
            $q->orderBy('date_pointage', 'desc')->limit(10);
        }, 'conges' => function($q) {
            $q->orderBy('date_debut', 'desc')->limit(5);
        }, 'fichesPaie' => function($q) {
            $q->orderBy('annee', 'desc')->orderBy('mois', 'desc')->limit(6);
        }]);

        return Inertia::render('Employes/Show', ['employe' => $employe]);
    }

    public function edit(Employe $employe)
    {
        return Inertia::render('Employes/Edit', ['employe' => $employe]);
    }

    public function update(Request $request, Employe $employe)
    {
        $validated = $request->validate([
            'matricule' => 'required|max:20|unique:employes,matricule,' . $employe->id,
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|max:20',
            'poste' => 'nullable|max:100',
            'categorie' => 'nullable|max:50',
            'echelon' => 'nullable|max:20',
            'departement' => 'nullable|max:100',
            'date_embauche' => 'required|date',
            'salaire_base' => 'required|numeric|min:0',
            'prime_transport_defaut' => 'nullable|numeric|min:0',
            'prime_panier_defaut' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,inactif,conge',
            'adresse' => 'nullable|string',
            'cin' => 'nullable|max:20',
            'numero_cnas' => 'nullable|max:30',
            'mode_paiement' => 'nullable|in:virement,especes,cheque',
            'rib' => 'nullable|max:30',
        ]);

        $employe->update($validated);

        return redirect()->route('employes.index')
            ->with('success', 'Employé modifié avec succès.');
    }

    public function destroy(Employe $employe)
    {
        $employe->delete();

        return redirect()->route('employes.index')
            ->with('success', 'Employé supprimé avec succès.');
    }
}
