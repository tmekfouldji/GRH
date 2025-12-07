<template>
    <Head title="Tableau de bord" />
    
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Employés</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.total_employes }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">{{ stats.employes_actifs }} actifs</span>
                    <span class="mx-2 text-gray-300">•</span>
                    <span class="text-orange-600">{{ stats.employes_conge }} en congé</span>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Présents Aujourd'hui</p>
                        <p class="text-3xl font-bold text-green-600">{{ presencesStats.presents }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <UserCheck class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-red-600 font-medium">{{ presencesStats.absents }} absents</span>
                    <span class="mx-2 text-gray-300">•</span>
                    <span class="text-yellow-600">{{ presencesStats.retards }} retards</span>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Congés en Attente</p>
                        <p class="text-3xl font-bold text-orange-600">{{ stats.conges_en_attente }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <CalendarClock class="w-6 h-6 text-orange-600" />
                    </div>
                </div>
                <div class="mt-4">
                    <Link href="/conges?statut=en_attente" class="text-sm text-blue-600 hover:underline">
                        Voir les demandes →
                    </Link>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Masse Salariale</p>
                        <p class="text-3xl font-bold text-gray-800">{{ formatMoney(masseSalariale) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <Banknote class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    Ce mois-ci
                </div>
            </div>
        </div>
        
        <!-- Charts and Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Présences de la semaine -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Présences de la semaine</h3>
                <div class="space-y-3">
                    <div v-for="jour in presencesSemaine" :key="jour.date" class="flex items-center">
                        <span class="w-20 text-sm text-gray-600">{{ jour.jour }}</span>
                        <div class="flex-1 mx-4 bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div 
                                class="bg-green-500 h-full rounded-full transition-all duration-500"
                                :style="{ width: `${(jour.presents / stats.employes_actifs * 100) || 0}%` }"
                            ></div>
                        </div>
                        <span class="w-12 text-sm font-medium text-gray-800 text-right">{{ jour.presents }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Derniers pointages -->
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Derniers pointages</h3>
                    <Link href="/pointages" class="text-sm text-blue-600 hover:underline">Voir tout</Link>
                </div>
                <div class="space-y-3">
                    <div v-for="pointage in derniersPointages" :key="pointage.id" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <User class="w-4 h-4 text-gray-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ pointage.employe?.nom }} {{ pointage.employe?.prenom }}</p>
                                <p class="text-xs text-gray-500">{{ formatTime(pointage.heure_entree) }} - {{ formatTime(pointage.heure_sortie) }}</p>
                            </div>
                        </div>
                        <span :class="getStatutClass(pointage.statut)" class="px-2 py-1 text-xs rounded-full">
                            {{ getStatutLabel(pointage.statut) }}
                        </span>
                    </div>
                    <div v-if="derniersPointages.length === 0" class="text-center py-4 text-gray-500">
                        Aucun pointage aujourd'hui
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Congés en attente -->
        <div class="card" v-if="congesEnAttente.length > 0">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Demandes de congés en attente</h3>
                <Link href="/conges" class="text-sm text-blue-600 hover:underline">Gérer les congés</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jours</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="conge in congesEnAttente" :key="conge.id">
                            <td class="px-4 py-3 text-sm text-gray-800">{{ conge.employe?.nom }} {{ conge.employe?.prenom }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ getTypeCongeLabel(conge.type) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(conge.date_debut) }} - {{ formatDate(conge.date_fin) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ conge.nombre_jours }}</td>
                            <td class="px-4 py-3">
                                <Link :href="`/conges/${conge.id}`" class="text-blue-600 hover:underline text-sm">
                                    Voir détails
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Users, UserCheck, CalendarClock, Banknote, User } from 'lucide-vue-next';
import { formatDate, formatMoney, formatTime } from '@/utils/formatters';

const props = defineProps({
    stats: Object,
    presencesStats: Object,
    derniersPointages: Array,
    congesEnCours: Array,
    congesEnAttente: Array,
    masseSalariale: Number,
    presencesSemaine: Array,
});

const getStatutClass = (statut) => ({
    present: 'bg-green-100 text-green-800',
    absent: 'bg-red-100 text-red-800',
    retard: 'bg-yellow-100 text-yellow-800',
    conge: 'bg-blue-100 text-blue-800',
    maladie: 'bg-gray-100 text-gray-800',
    mission: 'bg-purple-100 text-purple-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatutLabel = (statut) => ({
    present: 'Présent', absent: 'Absent', retard: 'Retard',
    conge: 'Congé', maladie: 'Maladie', mission: 'Mission',
}[statut] || statut);

const getTypeCongeLabel = (type) => ({
    annuel: 'Congé annuel', maladie: 'Congé maladie',
    maternite: 'Congé maternité', paternite: 'Congé paternité',
    sans_solde: 'Sans solde', exceptionnel: 'Exceptionnel',
}[type] || type);
</script>
