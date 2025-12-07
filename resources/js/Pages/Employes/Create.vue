<template>
    <Head title="Nouvel employé" />
    
    <div class="max-w-4xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Nouvel employé</h2>
                <Link href="/employes" class="text-gray-500 hover:text-gray-700">
                    <X class="w-5 h-5" />
                </Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Informations personnelles -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Informations personnelles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Matricule *</label>
                            <input v-model="form.matricule" type="text" class="input" :class="{ 'border-red-500': errors.matricule }" />
                            <p v-if="errors.matricule" class="text-red-500 text-sm mt-1">{{ errors.matricule }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CIN</label>
                            <input v-model="form.cin" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.nom" type="text" class="input" :class="{ 'border-red-500': errors.nom }" />
                            <p v-if="errors.nom" class="text-red-500 text-sm mt-1">{{ errors.nom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input v-model="form.prenom" type="text" class="input" :class="{ 'border-red-500': errors.prenom }" />
                            <p v-if="errors.prenom" class="text-red-500 text-sm mt-1">{{ errors.prenom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="form.telephone" type="text" class="input" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="form.adresse" type="text" class="input" />
                        </div>
                    </div>
                </div>
                
                <!-- Informations professionnelles -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Informations professionnelles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Poste</label>
                            <input v-model="form.poste" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                            <input v-model="form.departement" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche *</label>
                            <input v-model="form.date_embauche" type="date" class="input" :class="{ 'border-red-500': errors.date_embauche }" />
                            <p v-if="errors.date_embauche" class="text-red-500 text-sm mt-1">{{ errors.date_embauche }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select v-model="form.statut" class="input">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="conge">En congé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire de base (DZD) *</label>
                            <input v-model="form.salaire_base" type="number" step="0.01" class="input" :class="{ 'border-red-500': errors.salaire_base }" />
                            <p v-if="errors.salaire_base" class="text-red-500 text-sm mt-1">{{ errors.salaire_base }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° CNSS</label>
                            <input v-model="form.cnss" type="text" class="input" />
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/employes" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, Loader2 } from 'lucide-vue-next';

const form = useForm({
    matricule: '',
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    poste: '',
    departement: '',
    date_embauche: '',
    salaire_base: '',
    statut: 'actif',
    adresse: '',
    cin: '',
    cnss: '',
});

const errors = form.errors;

const submit = () => {
    form.post('/employes');
};
</script>
