<template>
    <Head title="Rapport journalier" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Rapport journalier</h2>
                <p class="text-sm text-gray-500">{{ formatDateLong(date) }}</p>
            </div>
            <div class="flex gap-2">
                <input v-model="selectedDate" type="date" @change="changeDate" class="input w-auto" />
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="card text-center">
                <p class="text-3xl font-bold text-gray-800">{{ stats.total }}</p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="card text-center">
                <p class="text-3xl font-bold text-green-600">{{ stats.presents }}</p>
                <p class="text-sm text-gray-500">Présents</p>
            </div>
            <div class="card text-center">
                <p class="text-3xl font-bold text-red-600">{{ stats.absents }}</p>
                <p class="text-sm text-gray-500">Absents</p>
            </div>
            <div class="card text-center">
                <p class="text-3xl font-bold text-yellow-600">{{ stats.retards }}</p>
                <p class="text-sm text-gray-500">Retards</p>
            </div>
            <div class="card text-center">
                <p class="text-3xl font-bold text-blue-600">{{ stats.conges }}</p>
                <p class="text-sm text-gray-500">Congés</p>
            </div>
        </div>
        
        <!-- Employee List -->
        <div class="card p-0 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Poste</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Entrée</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Sortie</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Heures</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="employe in employes" :key="employe.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-medium text-sm">{{ employe.prenom[0] }}{{ employe.nom[0] }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ employe.prenom }} {{ employe.nom }}</div>
                                    <div class="text-sm text-gray-500">{{ employe.matricule }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ employe.poste || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            {{ employe.pointages?.[0]?.heure_entree || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ employe.pointages?.[0]?.heure_sortie || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            {{ employe.pointages?.[0]?.heures_travaillees || 0 }}h
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatutClass(employe.pointages?.[0]?.statut)" class="px-2 py-1 text-xs rounded-full font-medium">
                                {{ getStatutLabel(employe.pointages?.[0]?.statut) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/formatters';

const props = defineProps({
    employes: Array,
    date: String,
    stats: Object,
});

const selectedDate = ref(props.date);

const changeDate = () => {
    router.get('/pointages/rapport/journalier', { date: selectedDate.value });
};

const formatDateLong = (date) => formatDate(date, 'long');

const getStatutClass = (statut) => {
    if (!statut) return 'bg-red-100 text-red-800';
    return {
        present: 'bg-green-100 text-green-800',
        absent: 'bg-red-100 text-red-800',
        retard: 'bg-yellow-100 text-yellow-800',
        conge: 'bg-blue-100 text-blue-800',
        maladie: 'bg-gray-100 text-gray-800',
    }[statut] || 'bg-gray-100 text-gray-800';
};

const getStatutLabel = (statut) => {
    if (!statut) return 'Absent';
    return { present: 'Présent', absent: 'Absent', retard: 'Retard', conge: 'Congé', maladie: 'Maladie' }[statut] || statut;
};
</script>
