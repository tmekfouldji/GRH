<template>
    <Head title="Modifier la fiche de paie" />
    
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Modifier la fiche de paie</h2>
                    <p class="text-sm text-gray-500">{{ fichePaie.employe?.prenom }} {{ fichePaie.employe?.nom }} - {{ getMoisNom(fichePaie.mois) }} {{ fichePaie.annee }}</p>
                </div>
                <Link href="/fiches-paie" class="text-gray-500 hover:text-gray-700"><X class="w-5 h-5" /></Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salaire de base *</label>
                    <input v-model="form.salaire_base" type="number" step="0.01" class="input" />
                </div>
                
                <div class="border-t pt-4">
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
                
                <div class="border-t pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                    <select v-model="form.statut" class="input">
                        <option value="brouillon">Brouillon</option>
                        <option value="valide">Validé</option>
                        <option value="paye">Payé</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/fiches-paie" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Loader2 } from 'lucide-vue-next';

const props = defineProps({ fichePaie: Object, employes: Array });

const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const form = useForm({
    salaire_base: props.fichePaie.salaire_base,
    prime_anciennete: props.fichePaie.prime_anciennete || 0,
    prime_rendement: props.fichePaie.prime_rendement || 0,
    prime_transport: props.fichePaie.prime_transport || 0,
    autres_primes: props.fichePaie.autres_primes || 0,
    autres_deductions: props.fichePaie.autres_deductions || 0,
    statut: props.fichePaie.statut,
});

const getMoisNom = (mois) => moisNoms[mois - 1] || '';
const submit = () => form.put(`/fiches-paie/${props.fichePaie.id}`);
</script>
