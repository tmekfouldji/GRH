<template>
    <Head title="Nouvel employé" />
    
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-3 card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Nouvel employé</h2>
                <Link href="/employes" class="text-gray-500 hover:text-gray-700">
                    <X class="w-5 h-5" />
                </Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Informations personnelles -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Informations personnelles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Matricule *</label>
                            <input v-model="form.matricule" type="text" class="input" :class="{ 'border-red-500': form.errors.matricule }" />
                            <p v-if="form.errors.matricule" class="text-red-500 text-sm mt-1">{{ form.errors.matricule }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CIN</label>
                            <input v-model="form.cin" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.nom" type="text" class="input" :class="{ 'border-red-500': form.errors.nom }" />
                            <p v-if="form.errors.nom" class="text-red-500 text-sm mt-1">{{ form.errors.nom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input v-model="form.prenom" type="text" class="input" :class="{ 'border-red-500': form.errors.prenom }" />
                            <p v-if="form.errors.prenom" class="text-red-500 text-sm mt-1">{{ form.errors.prenom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="form.telephone" type="text" class="input" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="form.adresse" type="text" class="input" />
                        </div>
                    </div>
                </div>
                
                <!-- Informations professionnelles -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Informations professionnelles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Poste</label>
                            <input v-model="form.poste" type="text" class="input" placeholder="Ex: Opérateur" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                            <input v-model="form.categorie" type="text" class="input" placeholder="Ex: Catégorie 5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Échelon</label>
                            <input v-model="form.echelon" type="text" class="input" placeholder="Ex: 3" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                            <input v-model="form.departement" type="text" class="input" placeholder="Ex: Production" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche *</label>
                            <input v-model="form.date_embauche" type="date" class="input" :class="{ 'border-red-500': form.errors.date_embauche }" />
                            <p v-if="form.errors.date_embauche" class="text-red-500 text-sm mt-1">{{ form.errors.date_embauche }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select v-model="form.statut" class="input">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="conge">En congé</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration Salaire -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Configuration Salaire</h3>

                    <!-- Toggle Déclaré / Non déclaré -->
                    <div class="mb-4 flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.est_declare" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm font-medium text-gray-700">Employé déclaré</span>
                        </label>
                        <span v-if="form.est_declare" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">Déclaré (CNAS + IRG)</span>
                        <span v-else class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded-full">Non déclaré (pas de taxes)</span>
                    </div>

                    <!-- Mode de rémunération -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode de rémunération *</label>
                            <select v-model="form.mode_remuneration" class="input" :class="{ 'border-red-500': form.errors.mode_remuneration }">
                                <option value="salaire">Au salaire</option>
                                <option value="piece">À la pièce</option>
                            </select>
                            <p v-if="form.errors.mode_remuneration" class="text-red-500 text-sm mt-1">{{ form.errors.mode_remuneration }}</p>
                        </div>
                        <div v-if="form.mode_remuneration === 'piece'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prime par pièce (DZD) *</label>
                            <input v-model="form.prime_par_piece" type="number" step="0.01" min="0.01" class="input" :class="{ 'border-red-500': form.errors.prime_par_piece }" placeholder="Ex: 50" />
                            <p v-if="form.errors.prime_par_piece" class="text-red-500 text-sm mt-1">{{ form.errors.prime_par_piece }}</p>
                            <p class="text-xs text-gray-400 mt-1">Montant par pièce fabriquée</p>
                        </div>
                    </div>

                    <!-- Salary inputs -->
                    <div v-if="form.est_declare" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire Brut (DZD) *</label>
                            <input
                                :value="salaireBrut"
                                @input="onBrutInput($event.target.value)"
                                type="number"
                                step="100"
                                class="input"
                                :class="{ 'border-red-500': form.errors.salaire_base, 'bg-blue-50 ring-1 ring-blue-300': lastEdited === 'brut' }"
                                placeholder="Ex: 35000"
                            />
                            <p v-if="form.errors.salaire_base" class="text-red-500 text-sm mt-1">{{ form.errors.salaire_base }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire Net (DZD)</label>
                            <input
                                :value="salaireNet"
                                @input="onNetInput($event.target.value)"
                                type="number"
                                step="100"
                                class="input"
                                :class="{ 'bg-green-50 ring-1 ring-green-300': lastEdited === 'net' }"
                                placeholder="Ex: 30000"
                            />
                        </div>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire (DZD) *</label>
                            <input
                                :value="salaireBrut"
                                @input="onBrutInput($event.target.value)"
                                type="number"
                                step="100"
                                class="input"
                                :class="{ 'border-red-500': form.errors.salaire_base }"
                                placeholder="Ex: 35000"
                            />
                            <p v-if="form.errors.salaire_base" class="text-red-500 text-sm mt-1">{{ form.errors.salaire_base }}</p>
                        </div>
                    </div>

                    <!-- Tax breakdown (only for declared employees) -->
                    <div v-if="form.est_declare && salaireBrut > 0" class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Salaire Brut</span>
                                <p class="font-bold text-blue-600">{{ formatNumber(salaryPreview.totalBrut) }} DZD</p>
                            </div>
                            <div>
                                <span class="text-gray-500">CNAS (9%)</span>
                                <p class="font-medium text-red-600">-{{ formatNumber(salaryPreview.cotisationCNAS) }} DZD</p>
                            </div>
                            <div>
                                <span class="text-gray-500">IRG</span>
                                <p class="font-medium text-orange-600">-{{ formatNumber(salaryPreview.irg) }} DZD</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Salaire Net</span>
                                <p class="font-bold text-green-600">{{ formatNumber(salaryPreview.salaireNet) }} DZD</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sécurité sociale & Paiement -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Sécurité sociale & Paiement</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° CNAS</label>
                            <input v-model="form.numero_cnas" type="text" class="input" placeholder="Numéro sécurité sociale" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                            <select v-model="form.mode_paiement" class="input">
                                <option value="virement">Virement bancaire</option>
                                <option value="especes">Espèces</option>
                                <option value="cheque">Chèque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RIB</label>
                            <input v-model="form.rib" type="text" class="input" placeholder="Numéro compte bancaire" />
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/employes" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Info Sidebar -->
        <div class="space-y-4">
            <div class="card bg-blue-50 border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                    <Info class="w-4 h-4" />
                    Guide Rapide
                </h3>
                <ul class="text-sm text-blue-700 space-y-2">
                    <li>• <strong>SNMG:</strong> 20,000 DZD minimum</li>
                    <li>• <strong>CNAS:</strong> 9% du brut (déclaré)</li>
                    <li>• <strong>IRG:</strong> Exonéré si SNI ≤ 30,000 DZD</li>
                </ul>
                <Link href="/guide-salaire" class="mt-3 text-xs text-blue-600 hover:underline flex items-center gap-1">
                    Voir le guide complet →
                </Link>
            </div>
            
            <div class="card">
                <h3 class="font-semibold text-gray-800 mb-3">📊 Barème IRG</h3>
                <div class="text-xs space-y-1">
                    <div class="flex justify-between">
                        <span>0 - 20k</span>
                        <span class="text-green-600 font-medium">0%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>20k - 40k</span>
                        <span>23%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>40k - 80k</span>
                        <span>27%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>80k - 160k</span>
                        <span>30%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>> 160k</span>
                        <span>33-35%</span>
                    </div>
                </div>
            </div>
            
            <div class="card bg-green-50 border-green-200">
                <h3 class="font-semibold text-green-800 mb-2">Conseil</h3>
                <p class="text-xs text-green-700">
                    Les employés "déclarés" sont soumis aux cotisations CNAS (9%) et à l'IRG. Les employés "non déclarés" reçoivent le salaire brut directement.
                </p>
            </div>
            
            <div class="card bg-orange-50 border-orange-200">
                <h3 class="font-semibold text-orange-800 mb-2">💼 Coût Employeur</h3>
                <p class="text-xs text-orange-700">
                    L'employeur paie <strong>+25%</strong> en charges patronales sur le salaire brut.
                </p>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Loader2, Info } from 'lucide-vue-next';
import { calculateFromBrut, calculateFromNet } from '@/utils/salaryCalculator';

const salaireBrut = ref(0);
const salaireNet = ref(0);
const lastEdited = ref('brut');

const form = useForm({
    matricule: '',
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    poste: '',
    categorie: '',
    echelon: '',
    departement: '',
    date_embauche: '',
    salaire_base: '',
    statut: 'actif',
    adresse: '',
    cin: '',
    numero_cnas: '',
    mode_paiement: 'virement',
    rib: '',
    mode_remuneration: 'salaire',
    prime_par_piece: '',
    est_declare: true,
});

const salaryPreview = computed(() => {
    const brut = parseFloat(salaireBrut.value) || 0;
    if (!form.est_declare) {
        return { totalBrut: brut, cotisationCNAS: 0, irg: 0, salaireNet: brut, salaireBrut: brut };
    }
    return calculateFromBrut(brut);
});

const onBrutInput = (value) => {
    const brut = parseFloat(value) || 0;
    salaireBrut.value = brut;
    lastEdited.value = 'brut';
    if (form.est_declare) {
        const result = calculateFromBrut(brut);
        salaireNet.value = result.salaireNet;
    } else {
        salaireNet.value = brut;
    }
};

const onNetInput = (value) => {
    const net = parseFloat(value) || 0;
    salaireNet.value = net;
    lastEdited.value = 'net';
    const result = calculateFromNet(net);
    salaireBrut.value = result.salaireBrut;
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(num || 0));
};

const submit = () => {
    form.salaire_base = parseFloat(salaireBrut.value) || 0;
    form.post('/employes');
};
</script>
