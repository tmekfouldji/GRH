<template>
    <Head title="Nouvelle fiche de paie" />
    
    <div class="max-w-4xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Nouvelle fiche de paie</h2>
                <Link href="/fiches-paie" class="text-gray-500 hover:text-gray-700"><X class="w-5 h-5" /></Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
                        <select v-model="form.employe_id" class="input" :class="{ 'border-red-500': form.errors.employe_id }">
                            <option value="">Sélectionner un employé</option>
                            <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                                {{ emp.prenom }} {{ emp.nom }} - {{ formatNumber(emp.salaire_base) }} DZD
                            </option>
                        </select>
                        <p v-if="form.errors.employe_id" class="text-red-500 text-sm mt-1">{{ form.errors.employe_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mois *</label>
                        <select v-model="form.mois" class="input">
                            <option v-for="(nom, index) in moisNoms" :key="index" :value="index + 1">{{ nom }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
                        <input v-model="form.annee" type="number" class="input" />
                    </div>
                </div>
                
                <!-- Infos employé sélectionné -->
                <div v-if="selectedEmploye" class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">Employé sélectionné</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-blue-600">Salaire de base</span>
                            <p class="font-bold">{{ formatNumber(selectedEmploye.salaire_base) }} DZD</p>
                        </div>
                        <div>
                            <span class="text-blue-600">Prime transport</span>
                            <p class="font-medium">{{ formatNumber(selectedEmploye.prime_transport_defaut || 0) }} DZD</p>
                        </div>
                        <div>
                            <span class="text-blue-600">Prime panier</span>
                            <p class="font-medium">{{ formatNumber(selectedEmploye.prime_panier_defaut || 0) }} DZD</p>
                        </div>
                        <div>
                            <span class="text-blue-600">Poste</span>
                            <p class="font-medium">{{ selectedEmploye.poste || '-' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Primes</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime de transport</label>
                            <input v-model="form.prime_transport" type="number" step="100" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime de panier</label>
                            <input v-model="form.prime_panier" type="number" step="100" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime de rendement</label>
                            <input v-model="form.prime_rendement" type="number" step="100" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Autres primes</label>
                            <input v-model="form.autres_primes" type="number" step="100" class="input" />
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Déductions</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Autres déductions</label>
                            <input v-model="form.autres_deductions" type="number" step="100" class="input" />
                        </div>
                    </div>
                </div>
                
                <!-- Aperçu calcul -->
                <div v-if="selectedEmploye" class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Aperçu du calcul</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
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
                            <span class="text-gray-500">Autres déductions</span>
                            <p class="font-medium text-red-600">-{{ formatNumber(form.autres_deductions) }} DZD</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Salaire Net</span>
                            <p class="font-bold text-green-600">{{ formatNumber(salaryPreview.salaireNet - form.autres_deductions) }} DZD</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/fiches-paie" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Créer la fiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Loader2 } from 'lucide-vue-next';
import { calculateFromBrut } from '@/utils/salaryCalculator';

const props = defineProps({ employes: Array });

const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const form = useForm({
    employe_id: '',
    mois: new Date().getMonth() + 1,
    annee: new Date().getFullYear(),
    prime_transport: 0,
    prime_panier: 0,
    prime_rendement: 0,
    autres_primes: 0,
    autres_deductions: 0,
});

const selectedEmploye = computed(() => {
    if (!form.employe_id) return null;
    return props.employes?.find(e => e.id === parseInt(form.employe_id));
});

// Auto-fill primes from employee defaults when selected
watch(() => form.employe_id, (newVal) => {
    if (newVal && selectedEmploye.value) {
        form.prime_transport = selectedEmploye.value.prime_transport_defaut || 0;
        form.prime_panier = selectedEmploye.value.prime_panier_defaut || 0;
    }
});

const salaryPreview = computed(() => {
    if (!selectedEmploye.value) return { totalBrut: 0, cotisationCNAS: 0, irg: 0, salaireNet: 0 };
    
    const salaireBrut = parseFloat(selectedEmploye.value.salaire_base) || 0;
    const primeTransport = parseFloat(form.prime_transport) || 0;
    const primePanier = parseFloat(form.prime_panier) || 0;
    const primeRendement = parseFloat(form.prime_rendement) || 0;
    const autresPrimes = parseFloat(form.autres_primes) || 0;
    
    return calculateFromBrut(salaireBrut, {
        primeTransport,
        primePanier,
        autresPrimes: primeRendement + autresPrimes,
    });
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(num || 0));
};

const submit = () => form.post('/fiches-paie');
</script>
