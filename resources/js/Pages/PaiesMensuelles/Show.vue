<template>
    <Head :title="`Paie ${paie.periode}`" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link href="/paies-mensuelles" class="text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ paie.periode }}</h2>
                    <p class="text-sm text-gray-500">{{ paie.reference }} • Créé le {{ formatDate(paie.date_creation) }}</p>
                </div>
                <span :class="getStatutClass(paie.statut)" class="px-3 py-1 text-sm rounded-full font-medium">
                    {{ paie.statut_label }}
                </span>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <!-- Actions selon le statut -->
                <button v-if="paie.statut === 'brouillon'" @click="valider" class="btn btn-primary flex items-center gap-2">
                    <CheckCircle class="w-4 h-4" />
                    Valider
                </button>
                <button v-if="paie.statut === 'valide'" @click="demarrerPaiement" class="btn btn-primary flex items-center gap-2">
                    <Play class="w-4 h-4" />
                    Démarrer paiement
                </button>
                <button v-if="paie.statut === 'en_paiement'" @click="cloturer" class="btn btn-success flex items-center gap-2">
                    <Lock class="w-4 h-4" />
                    Clôturer
                </button>
                
                <a :href="`/paies-mensuelles/${paie.id}/imprimer-tout`" target="_blank" class="btn btn-secondary flex items-center gap-2">
                    <Printer class="w-4 h-4" />
                    Imprimer tout
                </a>
                <a :href="`/paies-mensuelles/${paie.id}/rapport`" target="_blank" class="btn btn-secondary flex items-center gap-2">
                    <FileText class="w-4 h-4" />
                    Rapport
                </a>
                
                <button v-if="paie.statut !== 'cloture'" @click="showAnnulerModal = true" class="btn btn-danger flex items-center gap-2">
                    <Trash2 class="w-4 h-4" />
                    Annuler
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Employés</p>
                <p class="text-2xl font-bold text-gray-800">{{ paie.nombre_employes }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Total Brut</p>
                <p class="text-xl font-bold text-blue-600">{{ formatMoney(paie.total_brut) }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">CNAS</p>
                <p class="text-xl font-bold text-red-600">{{ formatMoney(paie.total_cotisations_cnas) }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">IRG</p>
                <p class="text-xl font-bold text-orange-600">{{ formatMoney(paie.total_irg) }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Total Net à Payer</p>
                <p class="text-xl font-bold text-green-600">{{ formatMoney(totalNetAPayer) }}</p>
            </div>
            <div class="card text-center bg-orange-50">
                <p class="text-xs text-orange-600 uppercase">Coût Employeur</p>
                <p class="text-xl font-bold text-orange-700">{{ formatMoney(paie.cout_total_employeur) }}</p>
            </div>
        </div>
        
        <!-- Validation des Présences -->
        <div v-if="paie.statut === 'brouillon'" class="card border-yellow-200 bg-yellow-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <ClipboardCheck class="w-6 h-6 text-yellow-600" />
                    <div>
                        <h3 class="font-semibold text-yellow-800">Validation des Présences</h3>
                        <p class="text-sm text-yellow-700">
                            {{ validationStats.valide + validationStats.ajuste }} / {{ paie.nombre_employes }} validées
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="calculerPresences" class="btn btn-sm btn-secondary">
                        Calculer présences
                    </button>
                    <button 
                        v-if="validationStats.en_attente > 0"
                        @click="validerToutesPresences" 
                        class="btn btn-sm btn-warning"
                    >
                        Valider tout
                    </button>
                </div>
            </div>
            <div class="mt-3 h-2 bg-yellow-200 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-green-500 rounded-full transition-all"
                    :style="{ width: ((validationStats.valide + validationStats.ajuste) / paie.nombre_employes * 100) + '%' }"
                ></div>
            </div>
        </div>
        
        <!-- Tax Status & Reception Progress -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Tax Status -->
            <div class="card">
                <h3 class="font-semibold text-gray-800 mb-4">📋 Statuts Fiscaux</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Déclaration CNAS</span>
                        <select 
                            v-model="taxForm.statut_cnas" 
                            @change="updateTaxStatus('cnas')"
                            class="input w-auto text-sm py-1"
                        >
                            <option value="non_declare">Non déclaré</option>
                            <option value="declare">Déclaré</option>
                            <option value="paye">Payé</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Déclaration IRG</span>
                        <select 
                            v-model="taxForm.statut_irg" 
                            @change="updateTaxStatus('irg')"
                            class="input w-auto text-sm py-1"
                        >
                            <option value="non_declare">Non déclaré</option>
                            <option value="declare">Déclaré</option>
                            <option value="paye">Payé</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Reception Progress -->
            <div class="card lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">📨 Remise des Fiches</h3>
                    <button 
                        v-if="paie.statut === 'en_paiement' && stats.en_attente > 0"
                        @click="marquerTousRemis"
                        class="btn btn-sm btn-secondary"
                    >
                        Tout marquer remis
                    </button>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-600">{{ stats.en_attente }}</p>
                        <p class="text-xs text-gray-500">En attente</p>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.confirme }}</p>
                        <p class="text-xs text-yellow-600">Confirmé</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ stats.remis }}</p>
                        <p class="text-xs text-green-600">Remis</p>
                    </div>
                </div>
                
                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full flex">
                        <div class="bg-green-500" :style="{ width: (stats.remis / paie.nombre_employes * 100) + '%' }"></div>
                        <div class="bg-yellow-500" :style="{ width: (stats.confirme / paie.nombre_employes * 100) + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fiches de paie table -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">📄 Fiches de Paie ({{ paie.fiches_paie?.length || 0 }})</h3>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Rechercher un employé..."
                    class="input w-64"
                />
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Présences</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net à Payer</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Validation</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Remise</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="fiche in filteredFiches" :key="fiche.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">{{ fiche.employe?.prenom }} {{ fiche.employe?.nom }}</p>
                                    <p class="text-xs text-gray-500">{{ fiche.employe?.matricule }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-xs">
                                    <span class="text-green-600">{{ fiche.jours_travailles }}j</span>
                                    <span v-if="fiche.jours_absence > 0" class="text-red-600 ml-1">-{{ fiche.jours_absence }}abs</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatMoney(fiche.salaire_brut) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ formatMoney(fiche.salaire_net) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">{{ formatMoney(fiche.net_a_payer) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="getValidationClass(fiche.statut_validation)" class="px-2 py-1 text-xs rounded-full">
                                    {{ fiche.statut_validation_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <select 
                                    :value="fiche.statut_reception"
                                    @change="(e) => updateReception(fiche.id, e.target.value)"
                                    class="input w-auto text-xs py-1"
                                    :disabled="paie.statut === 'cloture'"
                                >
                                    <option value="en_attente">En attente</option>
                                    <option value="confirme">Confirmé</option>
                                    <option value="remis">Remis</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <button 
                                        v-if="paie.statut === 'brouillon'"
                                        @click="openRetardsModal(fiche)"
                                        class="p-1 text-orange-600 hover:bg-orange-50 rounded"
                                        title="Gérer retards & absences"
                                    >
                                        <Clock class="w-4 h-4" />
                                    </button>
                                    <Link 
                                        v-if="paie.statut === 'brouillon'"
                                        :href="`/fiches-paie/${fiche.id}/validation-presences`" 
                                        class="p-1 text-yellow-600 hover:bg-yellow-50 rounded"
                                        title="Valider présences"
                                    >
                                        <ClipboardCheck class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/fiches-paie/${fiche.id}`" class="p-1 text-blue-600 hover:bg-blue-50 rounded">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <a :href="`/fiches-paie/${fiche.id}/imprimer`" target="_blank" class="p-1 text-gray-600 hover:bg-gray-100 rounded">
                                        <Printer class="w-4 h-4" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Annuler -->
    <div v-if="showAnnulerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-red-600 mb-2">⚠️ Annuler cette paie?</h3>
            <p class="text-sm text-gray-600 mb-4">
                Cette action va supprimer les fiches de paie en brouillon et délier les autres. 
                Cette action est irréversible.
            </p>
            <div class="flex justify-end gap-3">
                <button @click="showAnnulerModal = false" class="btn btn-secondary">Non, garder</button>
                <button @click="annuler" class="btn btn-danger">Oui, annuler</button>
            </div>
        </div>
    </div>

    <!-- Modal Gestion Retards -->
    <GestionRetardsModal 
        :show="showRetardsModal"
        :fiche="selectedFiche"
        :pointages="selectedPointages"
        :periode="paie.periode"
        @close="closeRetardsModal"
        @saved="onRetardsSaved"
    />
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle, Play, Lock, Printer, FileText, 
    Trash2, Eye, AlertTriangle, ClipboardCheck, Clock 
} from 'lucide-vue-next';
import { formatMoney, formatDate } from '@/utils/formatters';
import GestionRetardsModal from '@/Components/GestionRetardsModal.vue';

const props = defineProps({
    paie: Object,
    stats: Object,
});

const showAnnulerModal = ref(false);
const showRetardsModal = ref(false);
const selectedFiche = ref(null);
const selectedPointages = ref([]);

const validationStats = computed(() => {
    const fiches = props.paie.fiches_paie || [];
    return {
        en_attente: fiches.filter(f => f.statut_validation === 'en_attente').length,
        valide: fiches.filter(f => f.statut_validation === 'valide').length,
        ajuste: fiches.filter(f => f.statut_validation === 'ajuste').length,
    };
});

const totalNetAPayer = computed(() => {
    const fiches = props.paie.fiches_paie || [];
    return fiches.reduce((sum, f) => sum + (parseFloat(f.net_a_payer) || 0), 0);
});

const getValidationClass = (statut) => ({
    en_attente: 'bg-yellow-100 text-yellow-800',
    valide: 'bg-green-100 text-green-800',
    ajuste: 'bg-blue-100 text-blue-800',
}[statut] || 'bg-gray-100 text-gray-800');
const searchQuery = ref('');

const taxForm = reactive({
    statut_cnas: props.paie.statut_cnas,
    statut_irg: props.paie.statut_irg,
});

const filteredFiches = computed(() => {
    if (!searchQuery.value) return props.paie.fiches_paie || [];
    const q = searchQuery.value.toLowerCase();
    return (props.paie.fiches_paie || []).filter(f => 
        f.employe?.nom?.toLowerCase().includes(q) ||
        f.employe?.prenom?.toLowerCase().includes(q) ||
        f.employe?.matricule?.toLowerCase().includes(q)
    );
});

const getStatutClass = (statut) => ({
    brouillon: 'bg-gray-100 text-gray-800',
    valide: 'bg-blue-100 text-blue-800',
    en_paiement: 'bg-yellow-100 text-yellow-800',
    cloture: 'bg-green-100 text-green-800',
}[statut] || 'bg-gray-100 text-gray-800');

const valider = () => router.post(`/paies-mensuelles/${props.paie.id}/valider`);
const demarrerPaiement = () => router.post(`/paies-mensuelles/${props.paie.id}/demarrer-paiement`);
const cloturer = () => router.post(`/paies-mensuelles/${props.paie.id}/cloturer`);
const annuler = () => {
    router.delete(`/paies-mensuelles/${props.paie.id}`);
    showAnnulerModal.value = false;
};

const marquerTousRemis = () => router.post(`/paies-mensuelles/${props.paie.id}/marquer-tous-remis`);

const updateReception = (ficheId, statut) => {
    router.post(`/fiches-paie/${ficheId}/marquer-remis`, { statut_reception: statut }, { preserveScroll: true });
};

const updateTaxStatus = (type) => {
    const data = type === 'cnas' 
        ? { statut_cnas: taxForm.statut_cnas }
        : { statut_irg: taxForm.statut_irg };
    router.post(`/paies-mensuelles/${props.paie.id}/statut-taxes`, data, { preserveScroll: true });
};

const calculerPresences = () => router.post(`/paies-mensuelles/${props.paie.id}/calculer-presences`, {}, { preserveScroll: true });
const validerToutesPresences = () => router.post(`/paies-mensuelles/${props.paie.id}/valider-toutes-presences`, {}, { preserveScroll: true });

const openRetardsModal = async (fiche) => {
    selectedFiche.value = fiche;
    try {
        const response = await fetch(`/fiches-paie/${fiche.id}/pointages`);
        const data = await response.json();
        console.log('Pointages data:', data);
        console.log('Debug info:', data.debug);
        selectedPointages.value = data.pointages || [];
        // Update fiche with fresh data from server
        if (data.fiche) {
            selectedFiche.value = data.fiche;
        }
        showRetardsModal.value = true;
    } catch (error) {
        console.error('Error fetching pointages:', error);
    }
};

const closeRetardsModal = () => {
    showRetardsModal.value = false;
    selectedFiche.value = null;
    selectedPointages.value = [];
};

const onRetardsSaved = () => {
    router.reload({ preserveScroll: true });
};
</script>
