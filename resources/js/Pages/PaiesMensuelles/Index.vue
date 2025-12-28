<template>
    <Head title="Paies Mensuelles" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">📅 Paies Mensuelles</h2>
                <p class="text-sm text-gray-500">Gestion des paies par période</p>
            </div>
            <Link href="/paies-mensuelles/create" class="btn btn-primary flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Nouvelle paie mensuelle
            </Link>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <form @submit.prevent="search" class="flex flex-wrap gap-4">
                <select v-model="searchForm.annee" class="input w-auto">
                    <option value="">Toutes les années</option>
                    <option v-for="a in anneesList" :key="a" :value="a">{{ a }}</option>
                </select>
                <select v-model="searchForm.statut" class="input w-auto">
                    <option value="">Tous les statuts</option>
                    <option value="brouillon">Brouillon</option>
                    <option value="valide">Validé</option>
                    <option value="en_paiement">En paiement</option>
                    <option value="cloture">Clôturé</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <button type="button" @click="resetFilters" class="btn btn-secondary">Réinitialiser</button>
            </form>
        </div>
        
        <!-- Grid de paies -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link 
                v-for="paie in paies.data" 
                :key="paie.id"
                :href="`/paies-mensuelles/${paie.id}`"
                class="card hover:shadow-lg transition-shadow cursor-pointer border-l-4"
                :class="getStatutBorderClass(paie.statut)"
            >
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ paie.periode }}</h3>
                        <p class="text-xs text-gray-500">{{ paie.reference }}</p>
                    </div>
                    <span :class="getStatutClass(paie.statut)" class="px-2 py-1 text-xs rounded-full font-medium">
                        {{ paie.statut_label }}
                    </span>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Employés</span>
                        <span class="font-medium">{{ paie.nombre_employes }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Brut</span>
                        <span class="font-medium">{{ formatMoney(paie.total_brut) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Net</span>
                        <span class="font-bold text-green-600">{{ formatMoney(paie.total_net) }}</span>
                    </div>
                </div>
                
                <!-- Progress bar for payment -->
                <div v-if="paie.statut === 'en_paiement'" class="mt-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Remise des fiches</span>
                        <span>{{ paie.progression_paiement }}%</span>
                    </div>
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-blue-500 rounded-full transition-all"
                            :style="{ width: paie.progression_paiement + '%' }"
                        ></div>
                    </div>
                </div>
                
                <!-- Tax status badges -->
                <div class="mt-3 flex gap-2">
                    <span :class="getTaxStatusClass(paie.statut_cnas)" class="text-xs px-2 py-1 rounded">
                        CNAS: {{ getTaxStatusLabel(paie.statut_cnas) }}
                    </span>
                    <span :class="getTaxStatusClass(paie.statut_irg)" class="text-xs px-2 py-1 rounded">
                        IRG: {{ getTaxStatusLabel(paie.statut_irg) }}
                    </span>
                </div>
            </Link>
        </div>
        
        <div v-if="paies.data.length === 0" class="card text-center py-12">
            <Calendar class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">Aucune paie mensuelle trouvée</p>
            <Link href="/paies-mensuelles/create" class="btn btn-primary mt-4">
                Créer une nouvelle paie
            </Link>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Calendar } from 'lucide-vue-next';
import { formatMoney } from '@/utils/formatters';

const props = defineProps({
    paies: Object,
    annees: Array,
    filters: Object,
});

const searchForm = reactive({
    annee: props.filters?.annee || '',
    statut: props.filters?.statut || '',
});

const anneesList = computed(() => {
    const years = props.annees?.length ? [...props.annees] : [];
    const currentYear = new Date().getFullYear();
    if (!years.includes(currentYear)) years.push(currentYear);
    return years.sort((a, b) => b - a);
});

const search = () => router.get('/paies-mensuelles', searchForm, { preserveState: true });
const resetFilters = () => {
    searchForm.annee = '';
    searchForm.statut = '';
    router.get('/paies-mensuelles');
};

const getStatutClass = (statut) => ({
    brouillon: 'bg-gray-100 text-gray-800',
    valide: 'bg-blue-100 text-blue-800',
    en_paiement: 'bg-yellow-100 text-yellow-800',
    cloture: 'bg-green-100 text-green-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatutBorderClass = (statut) => ({
    brouillon: 'border-gray-300',
    valide: 'border-blue-500',
    en_paiement: 'border-yellow-500',
    cloture: 'border-green-500',
}[statut] || 'border-gray-300');

const getTaxStatusClass = (statut) => ({
    non_declare: 'bg-red-100 text-red-700',
    declare: 'bg-yellow-100 text-yellow-700',
    paye: 'bg-green-100 text-green-700',
}[statut] || 'bg-gray-100 text-gray-700');

const getTaxStatusLabel = (statut) => ({
    non_declare: 'Non déclaré',
    declare: 'Déclaré',
    paye: 'Payé',
}[statut] || statut);
</script>
