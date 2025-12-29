<template>
    <Head :title="`Validation Présences - ${fiche.employe?.prenom} ${fiche.employe?.nom}`" />
    
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="`/paies-mensuelles/${fiche.paie_mensuelle_id}`" class="text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Validation des Présences</h2>
                    <p class="text-sm text-gray-500">{{ fiche.employe?.prenom }} {{ fiche.employe?.nom }} - {{ fiche.periode }}</p>
                </div>
            </div>
            <span :class="getValidationStatusClass(fiche.statut_validation)" class="px-3 py-1 text-sm rounded-full font-medium">
                {{ fiche.statut_validation_label }}
            </span>
        </div>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Jours Ouvrés</p>
                <p class="text-2xl font-bold text-gray-800">{{ joursOuvres }}</p>
                <p class="text-xs text-gray-400">Dim-Jeu</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Jours Travaillés</p>
                <p class="text-2xl font-bold text-green-600">{{ fiche.jours_travailles }}</p>
                <p class="text-xs text-gray-400">avec pointage</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Absences</p>
                <p class="text-2xl font-bold text-red-600">{{ fiche.jours_absence }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Justifiés</p>
                <p class="text-2xl font-bold text-blue-600">{{ fiche.jours_justifies }}</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Heures Normales</p>
                <p class="text-2xl font-bold text-gray-800">{{ fiche.heures_normales }}h</p>
            </div>
            <div class="card text-center">
                <p class="text-xs text-gray-500 uppercase">Heures Sup.</p>
                <p class="text-2xl font-bold text-purple-600">{{ fiche.heures_supplementaires }}h</p>
            </div>
        </div>
        
        <!-- Salary Calculation Info -->
        <div class="card bg-blue-50 border-blue-200">
            <div class="flex items-center gap-3 mb-3">
                <Calculator class="w-5 h-5 text-blue-600" />
                <h3 class="font-semibold text-blue-800">Calcul du Salaire</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-blue-600">Salaire de base</p>
                    <p class="font-bold">{{ formatMoney(fiche.salaire_base) }} DZD</p>
                </div>
                <div>
                    <p class="text-blue-600">Ratio présence</p>
                    <p class="font-bold">{{ ratioPresence }}%</p>
                    <p class="text-xs text-blue-500">({{ fiche.jours_travailles + fiche.jours_justifies }}/{{ joursOuvres }} jours)</p>
                </div>
                <div>
                    <p class="text-blue-600">Salaire brut calculé</p>
                    <p class="font-bold">{{ formatMoney(fiche.salaire_brut) }} DZD</p>
                </div>
                <div>
                    <p class="text-green-600">Salaire net</p>
                    <p class="font-bold text-green-700">{{ formatMoney(fiche.salaire_net) }} DZD</p>
                </div>
            </div>
        </div>
        
        <!-- Ajustement existant -->
        <div v-if="fiche.ajustement_heures != 0" class="card bg-yellow-50 border-yellow-200">
            <div class="flex items-center gap-3">
                <AlertTriangle class="w-5 h-5 text-yellow-600" />
                <div>
                    <p class="font-medium text-yellow-800">Ajustement appliqué: {{ fiche.ajustement_heures > 0 ? '+' : '' }}{{ fiche.ajustement_heures }}h</p>
                    <p class="text-sm text-yellow-700">{{ fiche.motif_ajustement }}</p>
                </div>
            </div>
        </div>
        
        <!-- Pointages Table -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">📅 Détail des Pointages</h3>
                <span class="text-sm text-gray-500">{{ pointages.length }} jours</span>
            </div>
            
            <div v-if="pointages.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-center">Entrée</th>
                            <th class="px-3 py-2 text-center">Sortie</th>
                            <th class="px-3 py-2 text-center">Heures</th>
                            <th class="px-3 py-2 text-center">H. Sup</th>
                            <th class="px-3 py-2 text-center">Statut</th>
                            <th class="px-3 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="p in pointages" :key="p.id" :class="getRowClass(p.statut)">
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ formatDateShort(p.date) }}</span>
                            </td>
                            <td class="px-3 py-2 text-center font-mono">{{ formatTime(p.heure_entree) }}</td>
                            <td class="px-3 py-2 text-center font-mono">{{ formatTime(p.heure_sortie) }}</td>
                            <td class="px-3 py-2 text-center font-medium">{{ p.heures_travaillees || 0 }}h</td>
                            <td class="px-3 py-2 text-center text-purple-600">{{ p.heures_supplementaires || 0 }}h</td>
                            <td class="px-3 py-2 text-center">
                                <span :class="getStatutClass(p.statut)" class="px-2 py-1 text-xs rounded-full">
                                    {{ getStatutLabel(p.statut) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button 
                                    @click="openEditModal(p)" 
                                    class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                                    title="Modifier"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
                <Calendar class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p>Aucun pointage trouvé pour cette période</p>
            </div>
        </div>
        
        <!-- Validation Actions -->
        <div v-if="fiche.statut_validation === 'en_attente'" class="card">
            <h3 class="font-semibold text-gray-800 mb-4">✅ Validation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Valider tel quel -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-medium text-green-700 mb-2 flex items-center gap-2">
                        <CheckCircle class="w-5 h-5" />
                        Valider les présences
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Les présences sont correctes, aucun ajustement nécessaire.
                    </p>
                    <textarea 
                        v-model="validationForm.notes" 
                        placeholder="Notes optionnelles..."
                        class="input mb-3"
                        rows="2"
                    ></textarea>
                    <button @click="valider" :disabled="processing" class="btn btn-success w-full">
                        <Loader2 v-if="processing" class="w-4 h-4 animate-spin mr-2" />
                        Valider
                    </button>
                </div>
                
                <!-- Ajuster -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-medium text-yellow-700 mb-2 flex items-center gap-2">
                        <Edit class="w-5 h-5" />
                        Ajuster les heures
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Corriger les heures en cas d'erreur ou d'absence justifiée.
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ajustement (heures)</label>
                            <input 
                                v-model="ajustementForm.heures" 
                                type="number" 
                                step="0.5" 
                                class="input"
                                placeholder="Ex: -8 pour retirer 8h, +4 pour ajouter 4h"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motif *</label>
                            <textarea 
                                v-model="ajustementForm.motif" 
                                class="input"
                                rows="2"
                                placeholder="Ex: Absence justifiée maladie le 15/12..."
                            ></textarea>
                        </div>
                        <button 
                            @click="ajuster" 
                            :disabled="processing || !ajustementForm.motif"
                            class="btn btn-warning w-full"
                        >
                            <Loader2 v-if="processing" class="w-4 h-4 animate-spin mr-2" />
                            Appliquer l'ajustement
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Already validated -->
        <div v-else class="card bg-green-50 border-green-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <CheckCircle class="w-6 h-6 text-green-600" />
                    <div>
                        <p class="font-medium text-green-800">Présences validées</p>
                        <p class="text-sm text-green-700">
                            Par {{ fiche.valide_par }} le {{ formatDate(fiche.date_validation) }}
                        </p>
                        <p v-if="fiche.notes_validation" class="text-sm text-green-600 mt-1">
                            {{ fiche.notes_validation }}
                        </p>
                    </div>
                </div>
                <button @click="reouvrir" :disabled="processing" class="btn btn-warning flex items-center gap-2">
                    <RotateCcw class="w-4 h-4" />
                    Réouvrir la validation
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Pointage -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Modifier le pointage</h3>
            <p class="text-sm text-gray-500 mb-4">{{ formatDateShort(editForm.date) }}</p>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure entrée</label>
                        <input v-model="editForm.heure_entree" type="time" class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure sortie</label>
                        <input v-model="editForm.heure_sortie" type="time" class="input" />
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heures travaillées</label>
                        <input v-model="editForm.heures_travaillees" type="number" step="0.5" class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heures sup.</label>
                        <input v-model="editForm.heures_supplementaires" type="number" step="0.5" class="input" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select v-model="editForm.statut" class="input">
                        <option value="present">Présent</option>
                        <option value="absent">Absent</option>
                        <option value="conge">Congé</option>
                        <option value="maladie">Maladie</option>
                        <option value="mission">Mission</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif de modification</label>
                    <textarea v-model="editForm.motif" class="input" rows="2" placeholder="Raison de la modification..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showEditModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="savePointage" :disabled="processing" class="btn btn-primary">
                    <Loader2 v-if="processing" class="w-4 h-4 animate-spin mr-2" />
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, Edit, Loader2, AlertTriangle, Calendar, Pencil, RotateCcw, Calculator } from 'lucide-vue-next';

const props = defineProps({
    fiche: Object,
    pointages: Array,
    joursOuvres: {
        type: Number,
        default: 22,
    },
});

const processing = ref(false);

const joursOuvres = computed(() => props.joursOuvres || 22);

const ratioPresence = computed(() => {
    const jours_payes = (props.fiche.jours_travailles || 0) + (props.fiche.jours_justifies || 0);
    const total = joursOuvres.value || 22;
    return Math.round((jours_payes / total) * 100);
});

const formatMoney = (val) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(val || 0));
};

const validationForm = reactive({
    notes: '',
});

const ajustementForm = reactive({
    heures: 0,
    motif: '',
});

const showEditModal = ref(false);
const editForm = reactive({
    id: null,
    date: null,
    heure_entree: '',
    heure_sortie: '',
    heures_travaillees: 0,
    heures_supplementaires: 0,
    statut: 'present',
    motif: '',
});

const extractTime = (datetime) => {
    if (!datetime) return '';
    const d = new Date(datetime);
    if (isNaN(d.getTime())) return '';
    return d.toTimeString().substring(0, 5); // HH:MM format for input
};

const openEditModal = (pointage) => {
    editForm.id = pointage.id;
    editForm.date = pointage.date_pointage;
    editForm.heure_entree = extractTime(pointage.heure_entree);
    editForm.heure_sortie = extractTime(pointage.heure_sortie);
    editForm.heures_travaillees = pointage.heures_travaillees || 0;
    editForm.heures_supplementaires = pointage.heures_supplementaires || 0;
    editForm.statut = pointage.statut || 'present';
    editForm.motif = '';
    showEditModal.value = true;
};

const savePointage = () => {
    processing.value = true;
    router.post(`/pointages/${editForm.id}/modifier-validation`, {
        heure_entree: editForm.heure_entree,
        heure_sortie: editForm.heure_sortie,
        heures_travaillees: editForm.heures_travaillees,
        heures_supplementaires: editForm.heures_supplementaires,
        statut: editForm.statut,
        motif: editForm.motif,
    }, {
        onSuccess: () => showEditModal.value = false,
        onFinish: () => processing.value = false,
    });
};

const reouvrir = () => {
    processing.value = true;
    router.post(`/fiches-paie/${props.fiche.id}/reouvrir-validation`, {}, {
        onFinish: () => processing.value = false,
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatDateShort = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short' });
};

const formatTime = (datetime) => {
    if (!datetime) return '-';
    const d = new Date(datetime);
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const getValidationStatusClass = (statut) => ({
    en_attente: 'bg-yellow-100 text-yellow-800',
    valide: 'bg-green-100 text-green-800',
    ajuste: 'bg-blue-100 text-blue-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatutClass = (statut) => ({
    present: 'bg-green-100 text-green-800',
    absent: 'bg-red-100 text-red-800',
    conge: 'bg-blue-100 text-blue-800',
    maladie: 'bg-purple-100 text-purple-800',
    mission: 'bg-orange-100 text-orange-800',
}[statut] || 'bg-gray-100 text-gray-800');

const getStatutLabel = (statut) => ({
    present: 'Présent',
    absent: 'Absent',
    conge: 'Congé',
    maladie: 'Maladie',
    mission: 'Mission',
}[statut] || statut);

const getRowClass = (statut) => ({
    absent: 'bg-red-50',
    conge: 'bg-blue-50',
    maladie: 'bg-purple-50',
}[statut] || '');

const valider = () => {
    processing.value = true;
    router.post(`/fiches-paie/${props.fiche.id}/valider-presences`, {
        action: 'valider',
        notes: validationForm.notes,
    }, {
        onFinish: () => processing.value = false,
    });
};

const ajuster = () => {
    processing.value = true;
    router.post(`/fiches-paie/${props.fiche.id}/valider-presences`, {
        action: 'ajuster',
        ajustement_heures: ajustementForm.heures,
        motif_ajustement: ajustementForm.motif,
    }, {
        onFinish: () => processing.value = false,
    });
};
</script>
