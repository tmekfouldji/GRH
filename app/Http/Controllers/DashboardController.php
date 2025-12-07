<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Pointage;
use App\Models\Conge;
use App\Models\FichePaie;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_employes' => Employe::count(),
            'employes_actifs' => Employe::where('statut', 'actif')->count(),
            'employes_conge' => Employe::where('statut', 'conge')->count(),
            'conges_en_attente' => Conge::where('statut', 'en_attente')->count(),
        ];

        // Pointages du jour
        $pointagesAujourdhui = Pointage::with('employe')
            ->whereDate('date_pointage', today())
            ->get();

        $presencesStats = [
            'presents' => $pointagesAujourdhui->where('statut', 'present')->count(),
            'absents' => $stats['employes_actifs'] - $pointagesAujourdhui->count(),
            'retards' => $pointagesAujourdhui->where('statut', 'retard')->count(),
        ];

        // Derniers pointages
        $derniersPointages = Pointage::with('employe')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Congés en cours
        $congesEnCours = Conge::with('employe')
            ->where('statut', 'approuve')
            ->where('date_debut', '<=', today())
            ->where('date_fin', '>=', today())
            ->get();

        // Demandes de congés en attente
        $congesEnAttente = Conge::with('employe')
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Masse salariale du mois
        $masseSalariale = FichePaie::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('salaire_net');

        // Graphique des présences de la semaine
        $presencesSemaine = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $presencesSemaine[] = [
                'date' => $date->format('d/m'),
                'jour' => $date->locale('fr')->dayName,
                'presents' => Pointage::whereDate('date_pointage', $date)
                    ->whereIn('statut', ['present', 'retard'])
                    ->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'presencesStats' => $presencesStats,
            'derniersPointages' => $derniersPointages,
            'congesEnCours' => $congesEnCours,
            'congesEnAttente' => $congesEnAttente,
            'masseSalariale' => $masseSalariale,
            'presencesSemaine' => $presencesSemaine,
        ]);
    }
}
