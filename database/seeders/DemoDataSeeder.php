<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use App\Models\Pointage;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des employés avec des données réalistes algériennes
        $employes = [
            [
                'matricule' => 'EMP001',
                'nom' => 'Benali',
                'prenom' => 'Mohamed',
                'email' => 'mohamed.benali@textile.dz',
                'telephone' => '0555123456',
                'poste' => 'Chef d\'équipe',
                'departement' => 'Production',
                'date_embauche' => '2018-03-15',
                'salaire_base' => 55000,
                'prime_transport_defaut' => 3000,
                'prime_panier_defaut' => 2500,
                'statut' => 'actif',
                'categorie' => 'Cadre',
                'echelon' => '3',
                'numero_cnas' => '1234567890123',
                'mode_paiement' => 'virement',
            ],
            [
                'matricule' => 'EMP002',
                'nom' => 'Boudiaf',
                'prenom' => 'Fatima',
                'email' => 'fatima.boudiaf@textile.dz',
                'telephone' => '0555234567',
                'poste' => 'Opératrice machine',
                'departement' => 'Production',
                'date_embauche' => '2020-06-01',
                'salaire_base' => 35000,
                'prime_transport_defaut' => 2500,
                'prime_panier_defaut' => 2000,
                'statut' => 'actif',
                'categorie' => 'Ouvrier',
                'echelon' => '2',
                'numero_cnas' => '2345678901234',
                'mode_paiement' => 'virement',
            ],
            [
                'matricule' => 'EMP003',
                'nom' => 'Khelifi',
                'prenom' => 'Ahmed',
                'email' => 'ahmed.khelifi@textile.dz',
                'telephone' => '0555345678',
                'poste' => 'Technicien maintenance',
                'departement' => 'Maintenance',
                'date_embauche' => '2019-01-10',
                'salaire_base' => 45000,
                'prime_transport_defaut' => 3000,
                'prime_panier_defaut' => 2500,
                'statut' => 'actif',
                'categorie' => 'Technicien',
                'echelon' => '2',
                'numero_cnas' => '3456789012345',
                'mode_paiement' => 'virement',
            ],
            [
                'matricule' => 'EMP004',
                'nom' => 'Mansouri',
                'prenom' => 'Amina',
                'email' => 'amina.mansouri@textile.dz',
                'telephone' => '0555456789',
                'poste' => 'Comptable',
                'departement' => 'Administration',
                'date_embauche' => '2021-09-01',
                'salaire_base' => 50000,
                'prime_transport_defaut' => 3000,
                'prime_panier_defaut' => 0,
                'statut' => 'actif',
                'categorie' => 'Cadre',
                'echelon' => '1',
                'numero_cnas' => '4567890123456',
                'mode_paiement' => 'virement',
            ],
            [
                'matricule' => 'EMP005',
                'nom' => 'Zeroual',
                'prenom' => 'Karim',
                'email' => 'karim.zeroual@textile.dz',
                'telephone' => '0555567890',
                'poste' => 'Magasinier',
                'departement' => 'Logistique',
                'date_embauche' => '2022-02-15',
                'salaire_base' => 32000,
                'prime_transport_defaut' => 2500,
                'prime_panier_defaut' => 2000,
                'statut' => 'actif',
                'categorie' => 'Ouvrier',
                'echelon' => '1',
                'numero_cnas' => '5678901234567',
                'mode_paiement' => 'especes',
            ],
            [
                'matricule' => 'EMP006',
                'nom' => 'Hamidi',
                'prenom' => 'Samia',
                'email' => 'samia.hamidi@textile.dz',
                'telephone' => '0555678901',
                'poste' => 'Contrôleuse qualité',
                'departement' => 'Qualité',
                'date_embauche' => '2017-11-20',
                'salaire_base' => 42000,
                'prime_transport_defaut' => 3000,
                'prime_panier_defaut' => 2500,
                'statut' => 'actif',
                'categorie' => 'Technicien',
                'echelon' => '3',
                'numero_cnas' => '6789012345678',
                'mode_paiement' => 'virement',
            ],
        ];

        foreach ($employes as $employeData) {
            Employe::updateOrCreate(
                ['matricule' => $employeData['matricule']],
                $employeData
            );
        }

        $this->command->info('✓ 6 employés créés');

        // Générer des pointages pour les 2 derniers mois
        $this->genererPointages();
    }

    private function genererPointages()
    {
        $employes = Employe::all();
        $moisActuel = Carbon::now();
        $moisPrecedent = Carbon::now()->subMonth();

        foreach ([$moisPrecedent, $moisActuel] as $mois) {
            $premierJour = $mois->copy()->startOfMonth();
            $dernierJour = $mois->copy()->endOfMonth();
            
            // Pour chaque jour ouvrable du mois
            $jour = $premierJour->copy();
            while ($jour <= $dernierJour) {
                // Sauter les weekends
                if ($jour->isWeekend()) {
                    $jour->addDay();
                    continue;
                }

                foreach ($employes as $employe) {
                    // 90% de présence, 5% absence, 5% maladie/congé
                    $rand = rand(1, 100);
                    
                    if ($rand <= 90) {
                        // Présent avec variation d'heures
                        $heureEntree = $jour->copy()->setTime(rand(7, 8), rand(0, 30), 0);
                        $heureSortie = $jour->copy()->setTime(rand(16, 18), rand(0, 59), 0);
                        
                        // Calculer heures travaillées
                        list($heuresTravaillees, $heuresSupp) = Pointage::calculerHeures($heureEntree, $heureSortie);
                        
                        Pointage::updateOrCreate(
                            [
                                'employe_id' => $employe->id,
                                'date_pointage' => $jour->format('Y-m-d'),
                            ],
                            [
                                'heure_entree' => $heureEntree,
                                'heure_sortie' => $heureSortie,
                                'heures_travaillees' => $heuresTravaillees,
                                'heures_supplementaires' => $heuresSupp,
                                'statut' => 'present',
                            ]
                        );
                    } elseif ($rand <= 95) {
                        // Absent
                        Pointage::updateOrCreate(
                            [
                                'employe_id' => $employe->id,
                                'date_pointage' => $jour->format('Y-m-d'),
                            ],
                            [
                                'heure_entree' => null,
                                'heure_sortie' => null,
                                'heures_travaillees' => 0,
                                'heures_supplementaires' => 0,
                                'statut' => 'absent',
                            ]
                        );
                    } else {
                        // Maladie ou congé
                        $statut = rand(0, 1) ? 'maladie' : 'conge';
                        Pointage::updateOrCreate(
                            [
                                'employe_id' => $employe->id,
                                'date_pointage' => $jour->format('Y-m-d'),
                            ],
                            [
                                'heure_entree' => null,
                                'heure_sortie' => null,
                                'heures_travaillees' => 0,
                                'heures_supplementaires' => 0,
                                'statut' => $statut,
                            ]
                        );
                    }
                }
                
                $jour->addDay();
            }
        }

        $this->command->info('✓ Pointages générés pour ' . $moisPrecedent->format('F') . ' et ' . $moisActuel->format('F'));
    }
}
