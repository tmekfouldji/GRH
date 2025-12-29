<?php

namespace App\Http\Controllers;

use App\Models\PaieMensuelle;
use App\Models\FichePaie;
use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaieMensuelleController extends Controller
{
    public function index(Request $request)
    {
        $query = PaieMensuelle::query();

        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $paies = $query->withCount('fichesPaie')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->paginate(12);

        $annees = PaieMensuelle::distinct()->pluck('annee')->sortDesc();

        return Inertia::render('PaiesMensuelles/Index', [
            'paies' => $paies,
            'annees' => $annees,
            'filters' => $request->only(['annee', 'statut']),
        ]);
    }

    public function create()
    {
        $moisActuel = now()->month;
        $anneeActuelle = now()->year;

        // Vérifier les paies existantes
        $paiesExistantes = PaieMensuelle::select('mois', 'annee')->get()
            ->map(fn($p) => $p->annee . '-' . $p->mois)
            ->toArray();

        $employesActifs = Employe::where('statut', 'actif')->count();

        return Inertia::render('PaiesMensuelles/Create', [
            'moisActuel' => $moisActuel,
            'anneeActuelle' => $anneeActuelle,
            'paiesExistantes' => $paiesExistantes,
            'employesActifs' => $employesActifs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
            'notes' => 'nullable|string',
        ]);

        $result = PaieMensuelle::genererPaieMensuelle(
            $validated['mois'],
            $validated['annee'],
            ['notes' => $validated['notes'] ?? null]
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('paies-mensuelles.show', $result['paie'])
            ->with('success', $result['message']);
    }

    public function show(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->load(['fichesPaie.employe']);

        // Statistiques de remise
        $stats = [
            'en_attente' => $paiesMensuelle->fichesPaie->where('statut_reception', 'en_attente')->count(),
            'remis' => $paiesMensuelle->fichesPaie->where('statut_reception', 'remis')->count(),
            'confirme' => $paiesMensuelle->fichesPaie->where('statut_reception', 'confirme')->count(),
        ];

        return Inertia::render('PaiesMensuelles/Show', [
            'paie' => $paiesMensuelle,
            'stats' => $stats,
        ]);
    }

    public function valider(PaieMensuelle $paiesMensuelle)
    {
        if ($paiesMensuelle->statut !== 'brouillon') {
            return redirect()->back()->with('error', 'Cette paie ne peut pas être validée.');
        }

        $paiesMensuelle->valider();

        return redirect()->back()->with('success', 'Paie mensuelle validée avec succès.');
    }

    public function demarrerPaiement(PaieMensuelle $paiesMensuelle)
    {
        if ($paiesMensuelle->statut !== 'valide') {
            return redirect()->back()->with('error', 'La paie doit être validée avant de démarrer le paiement.');
        }

        $paiesMensuelle->demarrerPaiement();

        return redirect()->back()->with('success', 'Paiement démarré.');
    }

    public function cloturer(PaieMensuelle $paiesMensuelle)
    {
        if ($paiesMensuelle->statut !== 'en_paiement') {
            return redirect()->back()->with('error', 'La paie doit être en cours de paiement pour être clôturée.');
        }

        $paiesMensuelle->cloturer();

        return redirect()->back()->with('success', 'Paie mensuelle clôturée.');
    }

    public function annuler(PaieMensuelle $paiesMensuelle)
    {
        if ($paiesMensuelle->statut === 'cloture') {
            return redirect()->back()->with('error', 'Une paie clôturée ne peut pas être annulée.');
        }

        $paiesMensuelle->annuler();

        return redirect()->route('paies-mensuelles.index')
            ->with('success', 'Paie mensuelle annulée.');
    }

    public function destroy(PaieMensuelle $paiesMensuelle)
    {
        if ($paiesMensuelle->statut === 'cloture') {
            return redirect()->back()->with('error', 'Une paie clôturée ne peut pas être supprimée.');
        }

        $paiesMensuelle->annuler();

        return redirect()->route('paies-mensuelles.index')
            ->with('success', 'Paie mensuelle supprimée.');
    }

    public function marquerRemis(Request $request, FichePaie $fichePaie)
    {
        $validated = $request->validate([
            'statut_reception' => 'required|in:en_attente,confirme,remis',
        ]);

        $fichePaie->statut_reception = $validated['statut_reception'];
        
        if ($validated['statut_reception'] !== 'en_attente') {
            $fichePaie->date_remise = now();
            $fichePaie->remis_par = $request->user()?->name ?? 'Système';
        } else {
            $fichePaie->date_remise = null;
            $fichePaie->remis_par = null;
        }

        $fichePaie->save();

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    public function marquerTousRemis(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->fichesPaie()->update([
            'statut_reception' => 'remis',
            'date_remise' => now(),
            'remis_par' => 'Marquage en masse',
        ]);

        return redirect()->back()->with('success', 'Toutes les fiches marquées comme remises.');
    }

    public function updateStatutTaxes(Request $request, PaieMensuelle $paiesMensuelle)
    {
        $validated = $request->validate([
            'statut_cnas' => 'nullable|in:non_declare,declare,paye',
            'statut_irg' => 'nullable|in:non_declare,declare,paye',
        ]);

        if (isset($validated['statut_cnas'])) {
            $paiesMensuelle->statut_cnas = $validated['statut_cnas'];
        }
        if (isset($validated['statut_irg'])) {
            $paiesMensuelle->statut_irg = $validated['statut_irg'];
        }

        $paiesMensuelle->save();

        return redirect()->back()->with('success', 'Statuts fiscaux mis à jour.');
    }

    public function imprimerTout(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->load(['fichesPaie.employe']);

        return view('paies-mensuelles.imprimer-tout', [
            'paie' => $paiesMensuelle,
        ]);
    }

    public function rapport(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->load(['fichesPaie.employe']);

        return view('paies-mensuelles.rapport', [
            'paie' => $paiesMensuelle,
        ]);
    }

    /**
     * Afficher la page de validation des présences pour une fiche
     */
    public function validationPresences(FichePaie $fichePaie)
    {
        $fichePaie->load(['employe', 'paieMensuelle']);
        
        // Récupérer les pointages du mois
        $pointages = $fichePaie->getPointagesDuMois();
        
        // Calculer les présences si pas encore fait
        if ($fichePaie->jours_travailles == 0 && $fichePaie->jours_absence == 0) {
            $fichePaie->calculerPresences();
            $fichePaie->calculerSalaire();
            $fichePaie->save();
        }

        return Inertia::render('PaiesMensuelles/ValidationPresences', [
            'fiche' => $fichePaie,
            'pointages' => $pointages,
            'joursOuvres' => $fichePaie->getJoursOuvresDuMois(),
        ]);
    }

    /**
     * Valider les présences d'un employé
     */
    public function validerPresencesEmploye(Request $request, FichePaie $fichePaie)
    {
        $validated = $request->validate([
            'action' => 'required|in:valider,ajuster',
            'notes' => 'nullable|string|max:500',
            'ajustement_heures' => 'nullable|numeric',
            'motif_ajustement' => 'nullable|string|max:500',
        ]);

        if ($validated['action'] === 'valider') {
            $fichePaie->validerPresences(
                $request->user()?->name ?? 'Système',
                $validated['notes'] ?? null
            );
        } else {
            $fichePaie->ajusterHeures(
                $validated['ajustement_heures'] ?? 0,
                $validated['motif_ajustement'] ?? '',
                $request->user()?->name ?? 'Système'
            );
        }

        // Recalculer les totaux de la paie mensuelle
        if ($fichePaie->paieMensuelle) {
            $fichePaie->paieMensuelle->recalculerTotaux();
        }

        return redirect()->back()->with('success', 'Présences validées avec succès.');
    }

    /**
     * Valider toutes les présences en attente
     */
    public function validerToutesPresences(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->fichesPaie()
            ->where('statut_validation', 'en_attente')
            ->each(function ($fiche) {
                $fiche->validerPresences('Validation en masse');
            });

        return redirect()->back()->with('success', 'Toutes les présences ont été validées.');
    }

    /**
     * Calculer les présences pour toutes les fiches et recalculer les salaires
     */
    public function calculerToutesPresences(PaieMensuelle $paiesMensuelle)
    {
        $paiesMensuelle->fichesPaie()->each(function ($fiche) {
            // Calculer les présences depuis les pointages
            $fiche->calculerPresences();
            // Recalculer le salaire basé sur les présences
            $fiche->calculerSalaire();
            $fiche->save();
        });

        $paiesMensuelle->recalculerTotaux();

        return redirect()->back()->with('success', 'Présences et salaires calculés pour tous les employés.');
    }

    /**
     * Réouvrir la validation d'une fiche (permet de modifier après validation)
     */
    public function reouvrirValidation(FichePaie $fichePaie)
    {
        // Vérifier que la paie n'est pas clôturée
        if ($fichePaie->paieMensuelle && $fichePaie->paieMensuelle->statut === 'cloture') {
            return redirect()->back()->with('error', 'Impossible de réouvrir: la paie est clôturée.');
        }

        $fichePaie->statut_validation = 'en_attente';
        $fichePaie->date_validation = null;
        $fichePaie->valide_par = null;
        $fichePaie->notes_validation = ($fichePaie->notes_validation ? $fichePaie->notes_validation . "\n" : '') 
            . "[Réouvert le " . now()->format('d/m/Y H:i') . "]";
        $fichePaie->save();

        return redirect()->back()->with('success', 'Validation réouverte. Vous pouvez modifier les présences.');
    }

    /**
     * Modifier un pointage depuis la page de validation
     */
    public function modifierPointageValidation(Request $request, $pointageId)
    {
        $validated = $request->validate([
            'heure_entree' => 'nullable|string',
            'heure_sortie' => 'nullable|string',
            'heures_travaillees' => 'nullable|numeric|min:0|max:24',
            'heures_supplementaires' => 'nullable|numeric|min:0|max:24',
            'statut' => 'required|in:present,absent,conge,maladie,mission',
            'motif' => 'nullable|string|max:500',
        ]);

        $pointage = \App\Models\Pointage::findOrFail($pointageId);
        
        // Combiner date du pointage avec les heures pour créer datetime complet
        $datePointage = $pointage->date_pointage->format('Y-m-d');
        
        if ($validated['heure_entree']) {
            $pointage->heure_entree = \Carbon\Carbon::parse($datePointage . ' ' . $validated['heure_entree']);
        } else {
            $pointage->heure_entree = null;
        }
        
        if ($validated['heure_sortie']) {
            $pointage->heure_sortie = \Carbon\Carbon::parse($datePointage . ' ' . $validated['heure_sortie']);
        } else {
            $pointage->heure_sortie = null;
        }
        
        // Recalculer les heures si les deux sont présents
        if ($pointage->heure_entree && $pointage->heure_sortie) {
            list($heures_normales, $heures_sup) = \App\Models\Pointage::calculerHeures(
                $pointage->heure_entree,
                $pointage->heure_sortie
            );
            $pointage->heures_travaillees = $heures_normales;
            $pointage->heures_supplementaires = $heures_sup;
        } else {
            $pointage->heures_travaillees = $validated['heures_travaillees'] ?? 0;
            $pointage->heures_supplementaires = $validated['heures_supplementaires'] ?? 0;
        }
        
        $pointage->statut = $validated['statut'];
        
        // Ajouter une note de modification si motif fourni
        if ($validated['motif']) {
            $pointage->commentaire = ($pointage->commentaire ? $pointage->commentaire . "\n" : '') 
                . "[Modifié le " . now()->format('d/m/Y H:i') . "] " . $validated['motif'];
        }
        
        $pointage->save();

        // Recalculer les présences de la fiche de paie associée
        $fichePaie = FichePaie::where('employe_id', $pointage->employe_id)
            ->where('mois', $pointage->date_pointage->month)
            ->where('annee', $pointage->date_pointage->year)
            ->first();
        
        if ($fichePaie) {
            $fichePaie->calculerPresences();
            $fichePaie->save();
            
            // Recalculer les totaux de la paie mensuelle
            if ($fichePaie->paieMensuelle) {
                $fichePaie->paieMensuelle->recalculerTotaux();
            }
        }

        return redirect()->back()->with('success', 'Pointage modifié avec succès.');
    }
}
