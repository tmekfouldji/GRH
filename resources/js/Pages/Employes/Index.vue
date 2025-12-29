<template>
    <Head title="Employés" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Liste des employés</h2>
                <p class="text-sm text-gray-500">{{ employes.total }} employé(s) trouvé(s)</p>
            </div>
            <Link href="/employes/create" class="btn btn-primary flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Nouvel employé
            </Link>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <form @submit.prevent="search" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        v-model="searchForm.search" 
                        type="text" 
                        placeholder="Rechercher par nom, prénom, matricule..."
                        class="input"
                    />
                </div>
                <select v-model="searchForm.statut" class="input w-auto">
                    <option value="">Tous les statuts</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="conge">En congé</option>
                </select>
                <select v-model="searchForm.departement" class="input w-auto">
                    <option value="">Tous les départements</option>
                    <option v-for="dept in departements" :key="dept" :value="dept">{{ dept }}</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <Search class="w-4 h-4" />
                </button>
                <button type="button" @click="resetFilters" class="btn btn-secondary">
                    Réinitialiser
                </button>
            </form>
        </div>
        
        <!-- Table -->
        <div class="card p-0 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom complet</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salaire Base</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Primes</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salaire Net</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="employe in employes.data" :key="employe.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ employe.matricule }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-medium text-sm">{{ employe.prenom[0] }}{{ employe.nom[0] }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ employe.prenom }} {{ employe.nom }}</div>
                                    <div class="text-sm text-gray-500">{{ employe.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ employe.poste || '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span :class="getStatutClass(employe.statut)" class="px-2 py-1 text-xs rounded-full font-medium">
                                {{ getStatutLabel(employe.statut) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ formatNumber(employe.salaire_base) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                            <div class="text-xs text-gray-500">
                                <span v-if="employe.prime_transport_defaut" class="block">Transport: {{ formatNumber(employe.prime_transport_defaut) }}</span>
                                <span v-if="employe.prime_panier_defaut" class="block">Panier: {{ formatNumber(employe.prime_panier_defaut) }}</span>
                                <span v-if="!employe.prime_transport_defaut && !employe.prime_panier_defaut">-</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-green-600">{{ formatNumber(getSalaireNet(employe)) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm space-x-2">
                            <Link :href="`/employes/${employe.id}`" class="text-blue-600 hover:text-blue-900">
                                <Eye class="w-4 h-4 inline" />
                            </Link>
                            <Link :href="`/employes/${employe.id}/edit`" class="text-yellow-600 hover:text-yellow-900">
                                <Pencil class="w-4 h-4 inline" />
                            </Link>
                            <button @click="confirmDelete(employe)" class="text-red-600 hover:text-red-900">
                                <Trash2 class="w-4 h-4 inline" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="employes.data.length === 0">
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            Aucun employé trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Afficher</span>
                <select v-model="searchForm.per_page" @change="search" class="input w-auto text-sm py-1">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">Tout</option>
                </select>
                <span class="text-sm text-gray-600">sur {{ employes.total }} employé(s)</span>
            </div>
            <div v-if="employes.last_page > 1" class="flex gap-2">
                <Link 
                    v-for="link in employes.links" 
                    :key="link.label"
                    :href="link.url ? link.url + '&per_page=' + searchForm.per_page : '#'"
                    :class="[
                        'px-3 py-2 text-sm rounded-lg',
                        link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirmer la suppression</h3>
            <p class="text-gray-600 mb-6">
                Êtes-vous sûr de vouloir supprimer l'employé <strong>{{ employeToDelete?.prenom }} {{ employeToDelete?.nom }}</strong> ?
                Cette action est irréversible.
            </p>
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="deleteEmploye" class="btn btn-danger">Supprimer</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, Search, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { calculateFromBrut } from '@/utils/salaryCalculator';

const props = defineProps({
    employes: Object,
    departements: Array,
    filters: Object,
});

const searchForm = reactive({
    search: props.filters?.search || '',
    statut: props.filters?.statut || '',
    departement: props.filters?.departement || '',
    per_page: props.filters?.per_page || '25',
});

const showDeleteModal = ref(false);
const employeToDelete = ref(null);

const search = () => {
    router.get('/employes', searchForm, { preserveState: true });
};

const resetFilters = () => {
    searchForm.search = '';
    searchForm.statut = '';
    searchForm.departement = '';
    router.get('/employes');
};

const confirmDelete = (employe) => {
    employeToDelete.value = employe;
    showDeleteModal.value = true;
};

const deleteEmploye = () => {
    router.delete(`/employes/${employeToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            employeToDelete.value = null;
        }
    });
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(value || 0));
};

const getSalaireNet = (employe) => {
    const base = parseFloat(employe.salaire_base) || 0;
    const transport = parseFloat(employe.prime_transport_defaut) || 0;
    const panier = parseFloat(employe.prime_panier_defaut) || 0;
    
    const result = calculateFromBrut(base, {
        primeTransport: transport,
        primePanier: panier,
    });
    return result.salaireNet;
};

const getStatutClass = (statut) => {
    return {
        actif: 'bg-green-100 text-green-800',
        inactif: 'bg-red-100 text-red-800',
        conge: 'bg-yellow-100 text-yellow-800',
    }[statut] || 'bg-gray-100 text-gray-800';
};

const getStatutLabel = (statut) => {
    return { actif: 'Actif', inactif: 'Inactif', conge: 'En congé' }[statut] || statut;
};
</script>
