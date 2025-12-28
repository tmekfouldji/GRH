<template>
    <Head title="Nouvelle fiche de paie" />
    
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Nouvelle fiche de paie</h2>
                <Link href="/fiches-paie" class="text-gray-500 hover:text-gray-700"><X class="w-5 h-5" /></Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
                    <select v-model="form.employe_id" class="input" :class="{ 'border-red-500': errors.employe_id }">
                        <option value="">Sélectionner un employé</option>
                        <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                            {{ emp.prenom }} {{ emp.nom }} - {{ formatMoney(emp.salaire_base) }}
                        </option>
                    </select>
                    <p v-if="errors.employe_id" class="text-red-500 text-sm mt-1">{{ errors.employe_id }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
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
                
                <div class="border-t pt-4 mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Primes</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime d'ancienneté</label>
                            <input v-model="form.prime_anciennete" type="number" step="0.01" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime de rendement</label>
                            <input v-model="form.prime_rendement" type="number" step="0.01" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prime de transport</label>
                            <input v-model="form.prime_transport" type="number" step="0.01" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Autres primes</label>
                            <input v-model="form.autres_primes" type="number" step="0.01" class="input" />
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Déductions</h3>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Autres déductions</label>
                        <input v-model="form.autres_deductions" type="number" step="0.01" class="input" />
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Loader2 } from 'lucide-vue-next';

const props = defineProps({ employes: Array });

const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const form = useForm({
    employe_id: '',
    mois: new Date().getMonth() + 1,
    annee: new Date().getFullYear(),
    prime_anciennete: 0,
    prime_rendement: 0,
    prime_transport: 0,
    autres_primes: 0,
    autres_deductions: 0,
});

const errors = form.errors;
const submit = () => form.post('/fiches-paie');
const formatMoney = (value) => new Intl.NumberFormat('fr-DZ', { style: 'currency', currency: 'DZD' }).format(value || 0);
</script>
