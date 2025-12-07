<template>
    <Head :title="`Congé - ${conge.employe?.prenom} ${conge.employe?.nom}`" />
    
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <Link href="/conges" class="flex items-center text-gray-500 hover:text-gray-700">
                <ArrowLeft class="w-5 h-5 mr-2" /> Retour aux congés
            </Link>
            <span :class="getStatutClass(conge.statut)" class="px-3 py-1 text-sm rounded-full font-medium">
                {{ getStatutLabel(conge.statut) }}
            </span>
        </div>
        
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Détails de la demande de congé</h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Employé</p>
                    <p class="font-medium text-lg">{{ conge.employe?.prenom }} {{ conge.employe?.nom }}</p>
                    <p class="text-sm text-gray-500">{{ conge.employe?.matricule }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type de congé</p>
                    <p class="font-medium">{{ getTypeLabel(conge.type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date de début</p>
                    <p class="font-medium">{{ formatDate(conge.date_debut) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date de fin</p>
                    <p class="font-medium">{{ formatDate(conge.date_fin) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nombre de jours</p>
                    <p class="font-medium text-lg">{{ conge.nombre_jours }} jour(s)</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date de validation</p>
                    <p class="font-medium">{{ conge.date_validation ? formatDate(conge.date_validation) : '-' }}</p>
                </div>
            </div>
            
            <div v-if="conge.motif" class="mt-6 pt-6 border-t">
                <p class="text-sm text-gray-500 mb-2">Motif</p>
                <p class="text-gray-700">{{ conge.motif }}</p>
            </div>
            
            <div v-if="conge.commentaire_responsable" class="mt-6 pt-6 border-t">
                <p class="text-sm text-gray-500 mb-2">Commentaire du responsable</p>
                <p class="text-gray-700">{{ conge.commentaire_responsable }}</p>
            </div>
        </div>
        
        <!-- Actions -->
        <div v-if="conge.statut === 'en_attente'" class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
            <div class="flex gap-3">
                <button @click="approuver" class="btn btn-success flex items-center gap-2">
                    <Check class="w-4 h-4" /> Approuver
                </button>
                <button @click="showRefusModal = true" class="btn btn-danger flex items-center gap-2">
                    <X class="w-4 h-4" /> Refuser
                </button>
            </div>
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Check, X } from 'lucide-vue-next';
import { formatDate as formatDateUtil } from '@/utils/formatters';

const props = defineProps({ conge: Object });

const showRefusModal = ref(false);
const refusMotif = ref('');

const approuver = () => router.post(`/conges/${props.conge.id}/approuver`);
const refuser = () => {
    router.post(`/conges/${props.conge.id}/refuser`, { commentaire_responsable: refusMotif.value }, {
        onSuccess: () => { showRefusModal.value = false; }
    });
};

const formatDate = (date) => formatDateUtil(date, 'long');
const getTypeLabel = (type) => ({ annuel: 'Congé annuel', maladie: 'Congé maladie', maternite: 'Congé maternité', paternite: 'Congé paternité', sans_solde: 'Congé sans solde', exceptionnel: 'Congé exceptionnel' }[type] || type);
const getStatutClass = (statut) => ({ en_attente: 'bg-yellow-100 text-yellow-800', approuve: 'bg-green-100 text-green-800', refuse: 'bg-red-100 text-red-800', annule: 'bg-gray-100 text-gray-800' }[statut] || 'bg-gray-100 text-gray-800');
const getStatutLabel = (statut) => ({ en_attente: 'En attente', approuve: 'Approuvé', refuse: 'Refusé', annule: 'Annulé' }[statut] || statut);
</script>
