<template>
    <Head title="Nouvelle Paie Mensuelle" />
    
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">📅 Nouvelle Paie Mensuelle</h2>
                    <p class="text-sm text-gray-500">Générer les fiches de paie pour une période</p>
                </div>
                <Link href="/paies-mensuelles" class="text-gray-500 hover:text-gray-700">
                    <X class="w-5 h-5" />
                </Link>
            </div>
            
            <!-- Info card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <Info class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Cette action va:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Générer une fiche de paie pour chaque employé actif</li>
                            <li>Calculer automatiquement les cotisations CNAS et IRG</li>
                            <li>Inclure les heures des pointages du mois</li>
                            <li>Appliquer les primes par défaut configurées</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mois *</label>
                        <select v-model="form.mois" class="input" :class="{ 'border-red-500': form.errors.mois }">
                            <option v-for="(nom, idx) in moisNoms" :key="idx" :value="idx + 1">{{ nom }}</option>
                        </select>
                        <p v-if="form.errors.mois" class="text-red-500 text-sm mt-1">{{ form.errors.mois }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
                        <input v-model="form.annee" type="number" class="input" :class="{ 'border-red-500': form.errors.annee }" />
                        <p v-if="form.errors.annee" class="text-red-500 text-sm mt-1">{{ form.errors.annee }}</p>
                    </div>
                </div>
                
                <!-- Warning if period exists -->
                <div v-if="periodeExiste" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-red-700">
                        <AlertTriangle class="w-5 h-5" />
                        <span class="font-medium">Une paie existe déjà pour cette période!</span>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                    <textarea v-model="form.notes" rows="3" class="input" placeholder="Notes ou commentaires..."></textarea>
                </div>
                
                <!-- Summary -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-3">Résumé</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Période:</span>
                            <span class="font-medium ml-2">{{ moisNoms[form.mois - 1] }} {{ form.annee }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Employés actifs:</span>
                            <span class="font-medium ml-2">{{ employesActifs }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/paies-mensuelles" class="btn btn-secondary">Annuler</Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing || periodeExiste"
                        class="btn btn-primary flex items-center gap-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        <Zap v-else class="w-4 h-4" />
                        Générer les fiches de paie
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Info, AlertTriangle, Loader2, Zap } from 'lucide-vue-next';

const props = defineProps({
    moisActuel: Number,
    anneeActuelle: Number,
    paiesExistantes: Array,
    employesActifs: Number,
});

const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const form = useForm({
    mois: props.moisActuel,
    annee: props.anneeActuelle,
    notes: '',
});

const periodeExiste = computed(() => {
    const key = `${form.annee}-${form.mois}`;
    return props.paiesExistantes?.includes(key);
});

const submit = () => {
    form.post('/paies-mensuelles');
};
</script>
