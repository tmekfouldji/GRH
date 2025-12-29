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
                            <p class="text-sm text-gray-500">Ancienneté</p>
                            <p class="font-medium">{{ employe.anciennete }} an(s)</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° CNAS</p>
                            <p class="font-medium">{{ employe.numero_cnas || employe.cnss || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Catégorie / Échelon</p>
                            <p class="font-medium">{{ employe.categorie || '-' }} / {{ employe.echelon || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Mode de paiement</p>
                            <p class="font-medium">{{ getModePaiementLabel(employe.mode_paiement) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Aperçu Salaire -->
                <div class="card bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">💰 Aperçu Salaire Mensuel</h3>
                        <button @click="showGenererModal = true" class="btn btn-primary btn-sm flex items-center gap-1">
                            <FileText class="w-4 h-4" />
                            Générer fiche
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Gains -->
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500 uppercase mb-2">Gains</p>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Salaire de base</span>
                                    <span class="font-medium">{{ formatMoney(employe.salaire_base) }}</span>
                                </div>
                                <div v-if="employe.prime_transport_defaut > 0" class="flex justify-between text-green-600">
                                    <span>Prime transport</span>
                                    <span class="font-medium">+{{ formatMoney(employe.prime_transport_defaut) }}</span>
                                </div>
                                <div v-if="employe.prime_panier_defaut > 0" class="flex justify-between text-green-600">
                                    <span>Prime panier</span>
                                    <span class="font-medium">+{{ formatMoney(employe.prime_panier_defaut) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t font-semibold">
                                    <span>Salaire Brut</span>
                                    <span class="text-blue-600">{{ formatMoney(getSalairePreview('salaire_brut')) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Déductions -->
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500 uppercase mb-2">Retenues</p>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between text-red-600">
                                    <span>CNAS (9%)</span>
                                    <span class="font-medium">-{{ formatMoney(getSalairePreview('cotisation_cnas')) }}</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span>IRG</span>
                                    <span class="font-medium">-{{ formatMoney(getSalairePreview('irg')) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Net -->
                        <div class="bg-green-600 text-white rounded-lg p-4 flex justify-between items-center">
                            <span class="font-semibold">SALAIRE NET</span>
                            <span class="text-2xl font-bold">{{ formatMoney(getSalairePreview('salaire_net')) }}</span>
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
    
    <!-- Modal Générer Fiche de Paie -->
    <div v-if="showGenererModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Générer une fiche de paie</h3>
            <p class="text-sm text-gray-500 mb-4">Pour {{ employe.prenom }} {{ employe.nom }}</p>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                        <select v-model="genererForm.mois" class="input">
                            <option v-for="(nom, idx) in moisNoms" :key="idx" :value="idx + 1">{{ nom }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                        <input v-model="genererForm.annee" type="number" class="input" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prime de rendement</label>
                    <input v-model="genererForm.prime_rendement" type="number" step="100" class="input" placeholder="0" />
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showGenererModal = false" class="btn btn-secondary">Annuler</button>
                <button @click="genererFiche" :disabled="genererForm.processing" class="btn btn-primary">
                    Générer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, FileText } from 'lucide-vue-next';
import { formatDate, formatMoney, getMonthName } from '@/utils/formatters';

const props = defineProps({
    employe: Object,
});

const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const showGenererModal = ref(false);
const genererForm = reactive({
    mois: new Date().getMonth() + 1,
    annee: new Date().getFullYear(),
    prime_rendement: 0,
    processing: false,
});

const getSalairePreview = (key) => {
    return props.employe.salaire_preview?.[key] ?? 0;
};

const getModePaiementLabel = (mode) => ({
    virement: 'Virement bancaire',
    especes: 'Espèces',
    cheque: 'Chèque',
}[mode] || mode || 'Non défini');

const genererFiche = () => {
    genererForm.processing = true;
    router.post('/fiches-paie', {
        employe_id: props.employe.id,
        mois: genererForm.mois,
        annee: genererForm.annee,
        prime_rendement: genererForm.prime_rendement,
        prime_transport: props.employe.prime_transport_defaut || 0,
        autres_primes: props.employe.prime_panier_defaut || 0,
    }, {
        onSuccess: () => {
            showGenererModal.value = false;
            genererForm.processing = false;
        },
        onError: () => {
            genererForm.processing = false;
        }
    });
};

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
