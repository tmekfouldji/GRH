<template>
    <Head title="Pointages" />
    
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Pointages</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ pointages.total }} pointage(s) trouvé(s)
                    <span v-if="filters.date"> • {{ formatDate(filters.date) }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link href="/pointages/rapport/journalier" class="btn btn-secondary flex items-center gap-2">
                    <BarChart3 class="w-4 h-4" />
                    Rapport
                </Link>
                <Link href="/pointages/import" class="btn btn-secondary flex items-center gap-2">
                    <FileUp class="w-4 h-4" />
                    Importer
                </Link>
                <Link href="/pointages/create" class="btn btn-primary flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Nouveau
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                <div class="flex items-center gap-2">
                    <UserCheck class="w-5 h-5 text-green-600" />
                    <span class="text-xs font-medium text-green-600 uppercase">Présents</span>
                </div>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ stats.presents }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                <div class="flex items-center gap-2">
                    <Clock class="w-5 h-5 text-yellow-600" />
                    <span class="text-xs font-medium text-yellow-600 uppercase">Retards</span>
                </div>
                <p class="text-2xl font-bold text-yellow-700 mt-1">{{ stats.retards }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                <div class="flex items-center gap-2">
                    <UserX class="w-5 h-5 text-red-600" />
                    <span class="text-xs font-medium text-red-600 uppercase">Absents</span>
                </div>
                <p class="text-2xl font-bold text-red-700 mt-1">{{ stats.absents }}</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                <div class="flex items-center gap-2">
                    <Timer class="w-5 h-5 text-blue-600" />
                    <span class="text-xs font-medium text-blue-600 uppercase">Total Heures</span>
                </div>
                <p class="text-2xl font-bold text-blue-700 mt-1">{{ stats.totalHeures }}h</p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="flex items-center gap-2 mb-3">
                <Zap class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-gray-800">Pointage rapide</h3>
            </div>
            <div class="flex flex-wrap gap-3">
                <select v-model="selectedEmploye" class="input flex-1 min-w-[200px]">
                    <option value="">-- Sélectionner un employé --</option>
                    <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                        {{ emp.matricule }} - {{ emp.prenom }} {{ emp.nom }}
                    </option>
                </select>
                <button @click="enregistrerEntree" :disabled="!selectedEmploye" 
                    class="btn btn-success flex items-center gap-2 disabled:opacity-50">
                    <LogIn class="w-4 h-4" />
                    Entrée
                </button>
                <button @click="enregistrerSortie" :disabled="!selectedEmploye" 
                    class="btn btn-danger flex items-center gap-2 disabled:opacity-50">
                    <LogOut class="w-4 h-4" />
                    Sortie
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <div class="flex items-center gap-2 mb-3">
                <Filter class="w-5 h-5 text-gray-500" />
                <h3 class="font-semibold text-gray-800">Filtres</h3>
            </div>
            <form @submit.prevent="search" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date début</label>
                    <input v-model="searchForm.date_debut" type="date" class="input w-full text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date fin</label>
                    <input v-model="searchForm.date_fin" type="date" class="input w-full text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Employé</label>
                    <select v-model="searchForm.employe_id" class="input w-full text-sm">
                        <option value="">Tous</option>
                        <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                            {{ emp.matricule }} - {{ emp.nom }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                    <select v-model="searchForm.statut" class="input w-full text-sm">
                        <option value="">Tous</option>
                        <option value="present">Présent</option>
                        <option value="absent">Absent</option>
                        <option value="retard">Retard</option>
                        <option value="conge">Congé</option>
                        <option value="maladie">Maladie</option>
                        <option value="mission">Mission</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-1 flex items-center justify-center gap-1">
                        <Search class="w-4 h-4" />
                        Filtrer
                    </button>
                    <button type="button" @click="resetFilters" class="btn btn-secondary px-3">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Table -->
        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Employé</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Entrée</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Sortie</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Heures</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Statut</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="pointage in pointages.data" :key="pointage.id" 
                            class="hover:bg-blue-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-medium text-xs">
                                            {{ getInitials(pointage.employe) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate">
                                            {{ pointage.employe?.prenom }} {{ pointage.employe?.nom }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ pointage.employe?.matricule }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <div class="flex items-center gap-1">
                                    <Calendar class="w-4 h-4 text-gray-400" />
                                    {{ formatDate(pointage.date_pointage) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="pointage.heure_entree" class="inline-flex items-center gap-1 text-green-600 font-medium">
                                    <ArrowDownCircle class="w-4 h-4" />
                                    {{ formatTime(pointage.heure_entree) }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="pointage.heure_sortie" class="inline-flex items-center gap-1 text-red-600 font-medium">
                                    <ArrowUpCircle class="w-4 h-4" />
                                    {{ formatTime(pointage.heure_sortie) }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold text-gray-800">{{ pointage.heures_travaillees }}h</span>
                                <span v-if="pointage.heures_supplementaires > 0" 
                                    class="ml-1 text-xs text-orange-600 font-medium">
                                    +{{ pointage.heures_supplementaires }}h
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="getStatutClass(pointage.statut)" 
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ getStatutLabel(pointage.statut) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="`/pointages/${pointage.id}/edit`" 
                                        class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(pointage)" 
                                        class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pointages.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <Calendar class="w-12 h-12 text-gray-300" />
                                    <p class="text-gray-500">Aucun pointage trouvé</p>
                                    <p class="text-sm text-gray-400">Essayez de modifier vos filtres</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="pointages.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">
                Affichage de {{ pointages.from }} à {{ pointages.to }} sur {{ pointages.total }} résultats
            </p>
            <div class="flex items-center gap-1">
                <Link 
                    :href="pointages.prev_page_url || '#'"
                    :class="[
                        'px-3 py-2 text-sm rounded-lg border transition-colors',
                        pointages.prev_page_url 
                            ? 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300' 
                            : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    ]"
                >
                    <ChevronLeft class="w-4 h-4" />
                </Link>
                <template v-for="link in paginationLinks" :key="link.label">
                    <Link 
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm rounded-lg border transition-colors min-w-[40px] text-center',
                            link.active 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300'
                        ]"
                    >
                        {{ link.label }}
                    </Link>
                    <span v-else class="px-2 text-gray-400">...</span>
                </template>
                <Link 
                    :href="pointages.next_page_url || '#'"
                    :class="[
                        'px-3 py-2 text-sm rounded-lg border transition-colors',
                        pointages.next_page_url 
                            ? 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300' 
                            : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    ]"
                >
                    <ChevronRight class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <Trash2 class="w-5 h-5 text-red-600" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Supprimer le pointage</h3>
            </div>
            <p class="text-gray-600 mb-6">
                Êtes-vous sûr de vouloir supprimer ce pointage de 
                <strong>{{ pointageToDelete?.employe?.prenom }} {{ pointageToDelete?.employe?.nom }}</strong> 
                du {{ formatDate(pointageToDelete?.date_pointage) }} ?
            </p>
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="deletePointage" class="btn btn-danger">Supprimer</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Plus, LogIn, LogOut, Pencil, Trash2, FileUp, Filter, Search, X,
    Calendar, Clock, UserCheck, UserX, Timer, Zap, BarChart3,
    ChevronLeft, ChevronRight, ArrowDownCircle, ArrowUpCircle
} from 'lucide-vue-next';
import { formatDate as formatDateUtil, formatTime as formatTimeUtil, getInitials as getInitialsUtil } from '@/utils/formatters';

const props = defineProps({
    pointages: Object,
    employes: Array,
    filters: Object,
});

const selectedEmploye = ref('');
const showDeleteModal = ref(false);
const pointageToDelete = ref(null);

const searchForm = reactive({
    date_debut: props.filters?.date_debut || props.filters?.date || '',
    date_fin: props.filters?.date_fin || props.filters?.date || '',
    employe_id: props.filters?.employe_id || '',
    statut: props.filters?.statut || '',
});

// Calcul des stats
const stats = computed(() => {
    const data = props.pointages.data || [];
    return {
        presents: data.filter(p => p.statut === 'present').length,
        retards: data.filter(p => p.statut === 'retard').length,
        absents: data.filter(p => p.statut === 'absent').length,
        totalHeures: data.reduce((sum, p) => sum + parseFloat(p.heures_travaillees || 0), 0).toFixed(1)
    };
});

// Pagination links (filter out prev/next labels)
const paginationLinks = computed(() => {
    if (!props.pointages.links) return [];
    return props.pointages.links.filter(link => 
        !link.label.includes('Previous') && !link.label.includes('Next') &&
        !link.label.includes('Précédent') && !link.label.includes('Suivant')
    );
});

const search = () => router.get('/pointages', searchForm, { preserveState: true });

const resetFilters = () => {
    searchForm.date_debut = '';
    searchForm.date_fin = '';
    searchForm.employe_id = '';
    searchForm.statut = '';
    router.get('/pointages');
};

const enregistrerEntree = () => {
    router.post('/pointages/entree', { employe_id: selectedEmploye.value });
};

const enregistrerSortie = () => {
    router.post('/pointages/sortie', { employe_id: selectedEmploye.value });
};

const confirmDelete = (pointage) => {
    pointageToDelete.value = pointage;
    showDeleteModal.value = true;
};

const deletePointage = () => {
    router.delete(`/pointages/${pointageToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            pointageToDelete.value = null;
        }
    });
};

const getInitials = (employe) => getInitialsUtil(employe);
const formatDate = (date) => formatDateUtil(date, 'dayMonth');
const formatTime = (datetime) => formatTimeUtil(datetime);

const getStatutClass = (statut) => ({
    present: 'bg-green-100 text-green-700',
    absent: 'bg-red-100 text-red-700',
    retard: 'bg-yellow-100 text-yellow-700',
    conge: 'bg-blue-100 text-blue-700',
    maladie: 'bg-gray-100 text-gray-700',
    mission: 'bg-purple-100 text-purple-700',
}[statut] || 'bg-gray-100 text-gray-700');

const getStatutLabel = (statut) => ({
    present: 'Présent', absent: 'Absent', retard: 'Retard',
    conge: 'Congé', maladie: 'Maladie', mission: 'Mission'
}[statut] || statut);
</script>
