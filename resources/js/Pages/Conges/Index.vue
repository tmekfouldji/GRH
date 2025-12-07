<template>
    <Head title="Congés" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Gestion des congés</h2>
                <p class="text-sm text-gray-500">{{ conges.total }} demande(s)</p>
            </div>
            <Link href="/conges/create" class="btn btn-primary flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Nouvelle demande
            </Link>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <form @submit.prevent="search" class="flex flex-wrap gap-4">
                <select v-model="searchForm.employe_id" class="input w-auto">
                    <option value="">Tous les employés</option>
                    <option v-for="emp in employes" :key="emp.id" :value="emp.id">{{ emp.prenom }} {{ emp.nom }}</option>
                </select>
                <select v-model="searchForm.statut" class="input w-auto">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="approuve">Approuvé</option>
                    <option value="refuse">Refusé</option>
                    <option value="annule">Annulé</option>
                </select>
                <select v-model="searchForm.type" class="input w-auto">
                    <option value="">Tous les types</option>
                    <option value="annuel">Congé annuel</option>
                    <option value="maladie">Maladie</option>
                    <option value="maternite">Maternité</option>
                    <option value="paternite">Paternité</option>
                    <option value="sans_solde">Sans solde</option>
                    <option value="exceptionnel">Exceptionnel</option>
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
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Jours</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="conge in conges.data" :key="conge.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ conge.employe?.prenom }} {{ conge.employe?.nom }}</div>
                            <div class="text-sm text-gray-500">{{ conge.employe?.matricule }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ getTypeLabel(conge.type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ formatDate(conge.date_debut) }} - {{ formatDate(conge.date_fin) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ conge.nombre_jours }} j</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatutClass(conge.statut)" class="px-2 py-1 text-xs rounded-full font-medium">
                                {{ getStatutLabel(conge.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <Link :href="`/conges/${conge.id}`" class="text-blue-600 hover:text-blue-900">
                                <Eye class="w-4 h-4 inline" />
                            </Link>
                            <button v-if="conge.statut === 'en_attente'" @click="approuver(conge)" class="text-green-600 hover:text-green-900">
                                <Check class="w-4 h-4 inline" />
                            </button>
                            <button v-if="conge.statut === 'en_attente'" @click="showRefusModal = true; congeToRefuse = conge" class="text-red-600 hover:text-red-900">
                                <X class="w-4 h-4 inline" />
                            </button>
                            <Link :href="`/conges/${conge.id}/edit`" class="text-yellow-600 hover:text-yellow-900">
                                <Pencil class="w-4 h-4 inline" />
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="conges.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">Aucun congé trouvé</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Refus Modal -->
    <div v-if="showRefusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Refuser la demande</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motif du refus *</label>
                <textarea v-model="refusMotif" rows="3" class="input"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button @click="showRefusModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="refuser" class="btn btn-danger" :disabled="!refusMotif">Refuser</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, Eye, Check, X, Pencil } from 'lucide-vue-next';
import { formatDate } from '@/utils/formatters';

const props = defineProps({ conges: Object, employes: Array, filters: Object });

const searchForm = reactive({
    employe_id: props.filters?.employe_id || '',
    statut: props.filters?.statut || '',
    type: props.filters?.type || '',
});

const showRefusModal = ref(false);
const congeToRefuse = ref(null);
const refusMotif = ref('');

const search = () => router.get('/conges', searchForm, { preserveState: true });
const resetFilters = () => { searchForm.employe_id = ''; searchForm.statut = ''; searchForm.type = ''; router.get('/conges'); };

const approuver = (conge) => router.post(`/conges/${conge.id}/approuver`);
const refuser = () => {
    router.post(`/conges/${congeToRefuse.value.id}/refuser`, { commentaire_responsable: refusMotif.value }, {
        onSuccess: () => { showRefusModal.value = false; refusMotif.value = ''; }
    });
};

const getTypeLabel = (type) => ({ annuel: 'Congé annuel', maladie: 'Maladie', maternite: 'Maternité', paternite: 'Paternité', sans_solde: 'Sans solde', exceptionnel: 'Exceptionnel' }[type] || type);
const getStatutClass = (statut) => ({ en_attente: 'bg-yellow-100 text-yellow-800', approuve: 'bg-green-100 text-green-800', refuse: 'bg-red-100 text-red-800', annule: 'bg-gray-100 text-gray-800' }[statut] || 'bg-gray-100 text-gray-800');
const getStatutLabel = (statut) => ({ en_attente: 'En attente', approuve: 'Approuvé', refuse: 'Refusé', annule: 'Annulé' }[statut] || statut);
</script>
