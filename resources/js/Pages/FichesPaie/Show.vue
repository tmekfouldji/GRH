<template>
    <Head :title="`Fiche de paie - ${fichePaie.employe?.prenom} ${fichePaie.employe?.nom}`" />
    
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <Link href="/fiches-paie" class="flex items-center text-gray-500 hover:text-gray-700">
                <ArrowLeft class="w-5 h-5 mr-2" /> Retour aux fiches
            </Link>
            <div class="flex gap-2">
                <Link :href="`/fiches-paie/${fichePaie.id}/edit`" class="btn btn-primary flex items-center gap-2">
                    <Pencil class="w-4 h-4" /> Modifier
                </Link>
                <a :href="`/fiches-paie/${fichePaie.id}/imprimer`" target="_blank" class="btn btn-secondary flex items-center gap-2">
                    <Printer class="w-4 h-4" /> Imprimer
                </a>
            </div>
        </div>
        
        <!-- Fiche de paie -->
        <div class="card">
            <div class="border-b pb-4 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">FICHE DE PAIE</h2>
                        <p class="text-gray-500">{{ getMoisNom(fichePaie.mois) }} {{ fichePaie.annee }}</p>
                    </div>
                    <span :class="getStatutClass(fichePaie.statut)" class="px-3 py-1 text-sm rounded-full font-medium">
                        {{ getStatutLabel(fichePaie.statut) }}
                    </span>
                </div>
            </div>
            
            <!-- Employé -->
            <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">EMPLOYÉ</h3>
                    <p class="font-semibold text-lg">{{ fichePaie.employe?.prenom }} {{ fichePaie.employe?.nom }}</p>
                    <p class="text-gray-600">Matricule: {{ fichePaie.employe?.matricule }}</p>
                    <p class="text-gray-600">Poste: {{ fichePaie.employe?.poste || '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">PÉRIODE</h3>
                    <p class="font-semibold text-lg">{{ getMoisNom(fichePaie.mois) }} {{ fichePaie.annee }}</p>
                    <p class="text-gray-600">Heures travaillées: {{ fichePaie.heures_normales }}h</p>
                    <p class="text-gray-600">Heures sup.: {{ fichePaie.heures_supplementaires }}h</p>
                </div>
            </div>
            
            <!-- Détails salaire -->
            <div class="space-y-4">
                <!-- Gains -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-3">GAINS</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Salaire de base</span>
                            <span class="font-medium">{{ formatMoney(fichePaie.salaire_base) }}</span>
                        </div>
                        <div v-if="fichePaie.prime_rendement > 0" class="flex justify-between">
                            <span class="text-gray-600">Prime de rendement</span>
                            <span class="font-medium">{{ formatMoney(fichePaie.prime_rendement) }}</span>
                        </div>
                        <div v-if="fichePaie.prime_transport > 0" class="flex justify-between">
                            <span class="text-gray-600">Prime de transport</span>
                            <span class="font-medium">{{ formatMoney(fichePaie.prime_transport) }}</span>
                        </div>
                        <div v-if="fichePaie.autres_primes > 0" class="flex justify-between">
                            <span class="text-gray-600">Autres primes</span>
                            <span class="font-medium">{{ formatMoney(fichePaie.autres_primes) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t font-semibold">
                            <span>SALAIRE BRUT</span>
                            <span class="text-blue-600">{{ formatMoney(fichePaie.salaire_brut) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Déductions -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-3">DÉDUCTIONS</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cotisation CNAS (9%)</span>
                            <span class="font-medium text-red-600">-{{ formatMoney(fichePaie.cotisation_cnss) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">IRG (Impôt sur le Revenu Global)</span>
                            <span class="font-medium text-red-600">-{{ formatMoney(fichePaie.ir) }}</span>
                        </div>
                        <div v-if="fichePaie.autres_deductions > 0" class="flex justify-between">
                            <span class="text-gray-600">Autres déductions</span>
                            <span class="font-medium text-red-600">-{{ formatMoney(fichePaie.autres_deductions) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t font-semibold">
                            <span>TOTAL DÉDUCTIONS</span>
                            <span class="text-red-600">-{{ formatMoney(fichePaie.total_deductions) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Salaire Net -->
                <div class="bg-blue-50 rounded-lg p-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-800">SALAIRE NET</span>
                        <span class="text-xl font-bold text-blue-600">{{ formatMoney(fichePaie.salaire_net) }}</span>
                    </div>
                </div>
                
                <!-- Retards & Pénalités -->
                <div v-if="fichePaie.deduction_retard > 0 || fichePaie.deduction_absence > 0">
                    <h3 class="text-sm font-medium text-gray-500 mb-3 mt-4">RETARDS & PÉNALITÉS</h3>
                    <div class="space-y-2">
                        <div v-if="fichePaie.deduction_retard > 0" class="flex justify-between">
                            <span class="text-gray-600">Pénalités retards/absences</span>
                            <span class="font-medium text-red-600">-{{ formatMoney(fichePaie.deduction_retard) }}</span>
                        </div>
                        <div v-if="fichePaie.deduction_absence > 0" class="flex justify-between">
                            <span class="text-gray-600">Déduction absences</span>
                            <span class="font-medium text-red-600">-{{ formatMoney(fichePaie.deduction_absence) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Net à payer -->
                <div class="bg-green-50 rounded-lg p-4 mt-4 border-2 border-green-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-semibold text-gray-800">NET À PAYER</span>
                            <p class="text-xs text-gray-500">Après pénalités</p>
                        </div>
                        <span class="text-2xl font-bold text-green-600">{{ formatMoney(fichePaie.net_a_payer || fichePaie.salaire_net) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Printer } from 'lucide-vue-next';
import { formatMoney, getMonthName } from '@/utils/formatters';

const props = defineProps({ fichePaie: Object });

const getMoisNom = (mois) => getMonthName(mois);
const getStatutClass = (statut) => ({ brouillon: 'bg-gray-100 text-gray-800', valide: 'bg-yellow-100 text-yellow-800', paye: 'bg-green-100 text-green-800' }[statut] || 'bg-gray-100 text-gray-800');
const getStatutLabel = (statut) => ({ brouillon: 'Brouillon', valide: 'Validé', paye: 'Payé' }[statut] || statut);
</script>
