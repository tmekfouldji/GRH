<template>
    <Head :title="`${employe.prenom} ${employe.nom}`" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <Link href="/employes" class="text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ employe.prenom }} {{ employe.nom }}</h2>
                    <p class="text-gray-500">{{ employe.matricule }} • {{ employe.poste || 'Poste non défini' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Link :href="`/employes/${employe.id}/edit`" class="btn btn-primary flex items-center gap-2">
                    <Pencil class="w-4 h-4" />
                    Modifier
                </Link>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Info principale -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations personnelles</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">CIN</p>
                            <p class="font-medium">{{ employe.cin || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium">{{ employe.email || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Téléphone</p>
                            <p class="font-medium">{{ employe.telephone || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Adresse</p>
                            <p class="font-medium">{{ employe.adresse || '-' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations professionnelles</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Département</p>
                            <p class="font-medium">{{ employe.departement || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date d'embauche</p>
                            <p class="font-medium">{{ formatDate(employe.date_embauche) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Salaire de base</p>
                            <p class="font-medium text-lg">{{ formatMoney(employe.salaire_base) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° CNSS</p>
                            <p class="font-medium">{{ employe.cnss || '-' }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Derniers pointages -->
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Derniers pointages</h3>
                        <Link href="/pointages" class="text-sm text-blue-600 hover:underline">Voir tout</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Entrée</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Sortie</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Heures</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="pointage in employe.pointages" :key="pointage.id">
                                    <td class="px-4 py-2 text-sm">{{ formatDate(pointage.date_pointage) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ pointage.heure_entree || '-' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ pointage.heure_sortie || '-' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ pointage.heures_travaillees }}h</td>
                                    <td class="px-4 py-2">
                                        <span :class="getStatutClass(pointage.statut)" class="px-2 py-1 text-xs rounded-full">
                                            {{ getStatutLabel(pointage.statut) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!employe.pointages?.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucun pointage</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="card text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">{{ employe.prenom[0] }}{{ employe.nom[0] }}</span>
                    </div>
                    <h3 class="text-lg font-semibold">{{ employe.prenom }} {{ employe.nom }}</h3>
                    <p class="text-gray-500">{{ employe.poste }}</p>
                    <span :class="getStatusBadgeClass(employe.statut)" class="inline-block mt-2 px-3 py-1 text-sm rounded-full">
                        {{ getStatusLabel(employe.statut) }}
                    </span>
                </div>
                
                <!-- Congés récents -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Congés récents</h3>
                    <div class="space-y-3">
                        <div v-for="conge in employe.conges" :key="conge.id" class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ getTypeCongeLabel(conge.type) }}</span>
                                <span :class="getCongeStatutClass(conge.statut)" class="text-xs px-2 py-1 rounded-full">
                                    {{ getCongeStatutLabel(conge.statut) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ formatDate(conge.date_debut) }} - {{ formatDate(conge.date_fin) }}
                            </p>
                        </div>
                        <p v-if="!employe.conges?.length" class="text-center text-gray-500 text-sm py-4">Aucun congé</p>
                    </div>
                </div>
                
                <!-- Fiches de paie -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Dernières fiches de paie</h3>
                    <div class="space-y-2">
                        <Link 
                            v-for="fiche in employe.fiches_paie" 
                            :key="fiche.id"
                            :href="`/fiches-paie/${fiche.id}`"
                            class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ getMoisLabel(fiche.mois) }} {{ fiche.annee }}</span>
                                <span class="text-sm font-semibold text-green-600">{{ formatMoney(fiche.salaire_net) }}</span>
                            </div>
                        </Link>
                        <p v-if="!employe.fiches_paie?.length" class="text-center text-gray-500 text-sm py-4">Aucune fiche de paie</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { formatDate, formatMoney, getMonthName } from '@/utils/formatters';

const props = defineProps({
    employe: Object,
});

const getStatutClass = (statut) => ({
    present: 'bg-green-100 text-green-800',
    absent: 'bg-red-100 text-red-800',
    retard: 'bg-yellow-100 text-yellow-800',
    conge: 'bg-blue-100 text-blue-800',
    maladie: 'bg-gray-100 text-gray-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatutLabel = (statut) => ({
    present: 'Présent', absent: 'Absent', retard: 'Retard', conge: 'Congé', maladie: 'Maladie'
}[statut] || statut);

const getStatusBadgeClass = (statut) => ({
    actif: 'bg-green-100 text-green-800',
    inactif: 'bg-red-100 text-red-800',
    conge: 'bg-yellow-100 text-yellow-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatusLabel = (statut) => ({ actif: 'Actif', inactif: 'Inactif', conge: 'En congé' }[statut] || statut);

const getTypeCongeLabel = (type) => ({
    annuel: 'Congé annuel', maladie: 'Maladie', maternite: 'Maternité', 
    paternite: 'Paternité', sans_solde: 'Sans solde', exceptionnel: 'Exceptionnel'
}[type] || type);

const getCongeStatutClass = (statut) => ({
    en_attente: 'bg-yellow-100 text-yellow-800',
    approuve: 'bg-green-100 text-green-800',
    refuse: 'bg-red-100 text-red-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getCongeStatutLabel = (statut) => ({
    en_attente: 'En attente', approuve: 'Approuvé', refuse: 'Refusé', annule: 'Annulé'
}[statut] || statut);

const getMoisLabel = (mois) => getMonthName(mois);
</script>
