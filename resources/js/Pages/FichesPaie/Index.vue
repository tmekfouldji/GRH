<template>
    <Head title="Fiches de paie" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Fiches de paie</h2>
                <p class="text-sm text-gray-500">{{ fichesPaie.total }} fiche(s)</p>
            </div>
            <div class="flex gap-2">
                <Link href="/fiches-paie/create" class="btn btn-primary flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Nouvelle fiche
                </Link>
                <button @click="showGenererModal = true" class="btn btn-secondary flex items-center gap-2">
                    <FileSpreadsheet class="w-4 h-4" />
                    Générer en masse
                </button>
                <a :href="`/fiches-paie/exporter/excel?mois=${filters.mois || ''}&annee=${filters.annee || ''}`" class="btn btn-success flex items-center gap-2">
                    <Download class="w-4 h-4" />
                    Exporter Excel
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <form @submit.prevent="search" class="flex flex-wrap gap-4">
                <select v-model="searchForm.employe_id" class="input w-auto">
                    <option value="">Tous les employés</option>
                    <option v-for="emp in employes" :key="emp.id" :value="emp.id">{{ emp.prenom }} {{ emp.nom }}</option>
                </select>
                <select v-model="searchForm.mois" class="input w-auto">
                    <option value="">Tous les mois</option>
                    <option v-for="(nom, index) in moisNoms" :key="index" :value="index + 1">{{ nom }}</option>
                </select>
                <select v-model="searchForm.annee" class="input w-auto">
                    <option value="">Toutes les années</option>
                    <option v-for="a in anneesList" :key="a" :value="a">{{ a }}</option>
                </select>
                <select v-model="searchForm.statut" class="input w-auto">
                    <option value="">Tous les statuts</option>
                    <option value="brouillon">Brouillon</option>
                    <option value="valide">Validé</option>
                    <option value="paye">Payé</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <button type="button" @click="resetFilters" class="btn btn-secondary">Réinitialiser</button>
            </form>
        </div>
        
        <!-- Table -->
        <div class="card p-0 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Salaire Base</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Salaire Brut</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Déductions</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Salaire Net</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="fiche in fichesPaie.data" :key="fiche.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ fiche.employe?.prenom }} {{ fiche.employe?.nom }}</div>
                            <div class="text-sm text-gray-500">{{ fiche.employe?.matricule }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ getMoisNom(fiche.mois) }} {{ fiche.annee }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatMoney(fiche.salaire_base) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ formatMoney(fiche.salaire_brut) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-{{ formatMoney(fiche.total_deductions) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">{{ formatMoney(fiche.salaire_net) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatutClass(fiche.statut)" class="px-2 py-1 text-xs rounded-full font-medium">
                                {{ getStatutLabel(fiche.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <Link :href="`/fiches-paie/${fiche.id}`" class="text-blue-600 hover:text-blue-900">
                                <Eye class="w-4 h-4 inline" />
                            </Link>
                            <Link :href="`/fiches-paie/${fiche.id}/edit`" class="text-yellow-600 hover:text-yellow-900">
                                <Pencil class="w-4 h-4 inline" />
                            </Link>
                            <Link :href="`/fiches-paie/${fiche.id}/imprimer`" target="_blank" class="text-green-600 hover:text-green-900">
                                <Printer class="w-4 h-4 inline" />
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="fichesPaie.data.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">Aucune fiche de paie trouvée</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Génération en masse Modal -->
    <div v-if="showGenererModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Générer les fiches de paie</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mois *</label>
                    <select v-model="genererForm.mois" class="input">
                        <option v-for="(nom, index) in moisNoms" :key="index" :value="index + 1">{{ nom }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
                    <input v-model="genererForm.annee" type="number" class="input" />
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showGenererModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="genererMasse" class="btn btn-primary">Générer</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, FileSpreadsheet, Download, Eye, Pencil, Printer } from 'lucide-vue-next';
import { formatMoney, getMonthName } from '@/utils/formatters';

const props = defineProps({ fichesPaie: Object, employes: Array, annees: Array, filters: Object });

const searchForm = reactive({
    employe_id: props.filters?.employe_id || '',
    mois: props.filters?.mois || '',
    annee: props.filters?.annee || '',
    statut: props.filters?.statut || '',
});

const showGenererModal = ref(false);
const genererForm = reactive({ mois: new Date().getMonth() + 1, annee: new Date().getFullYear() });

const anneesList = computed(() => {
    const years = props.annees?.length ? [...props.annees] : [];
    const currentYear = new Date().getFullYear();
    if (!years.includes(currentYear)) years.push(currentYear);
    return years.sort((a, b) => b - a);
});

const search = () => router.get('/fiches-paie', searchForm, { preserveState: true });
const resetFilters = () => { searchForm.employe_id = ''; searchForm.mois = ''; searchForm.annee = ''; searchForm.statut = ''; router.get('/fiches-paie'); };

const genererMasse = () => {
    router.post('/fiches-paie/generer-masse', genererForm, {
        onSuccess: () => { showGenererModal.value = false; }
    });
};

const getMoisNom = (mois) => getMonthName(mois);
const getStatutClass = (statut) => ({ brouillon: 'bg-gray-100 text-gray-800', valide: 'bg-yellow-100 text-yellow-800', paye: 'bg-green-100 text-green-800' }[statut] || 'bg-gray-100 text-gray-800');
const getStatutLabel = (statut) => ({ brouillon: 'Brouillon', valide: 'Validé', paye: 'Payé' }[statut] || statut);
</script>
