<template>
    <Head title="Nouveau pointage" />
    
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Nouveau pointage</h2>
                <Link href="/pointages" class="text-gray-500 hover:text-gray-700">
                    <X class="w-5 h-5" />
                </Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
                    <select v-model="form.employe_id" class="input" :class="{ 'border-red-500': form.errors.employe_id }">
                        <option value="">Sélectionner un employé</option>
                        <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                            {{ emp.prenom }} {{ emp.nom }} ({{ emp.matricule }})
                        </option>
                    </select>
                    <p v-if="form.errors.employe_id" class="text-red-500 text-sm mt-1">{{ form.errors.employe_id }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input v-model="form.date_pointage" type="date" class="input" :class="{ 'border-red-500': form.errors.date_pointage }" />
                    <p v-if="form.errors.date_pointage" class="text-red-500 text-sm mt-1">{{ form.errors.date_pointage }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure d'entrée</label>
                        <input v-model="form.heure_entree" type="time" class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure de sortie</label>
                        <input v-model="form.heure_sortie" type="time" class="input" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                    <select v-model="form.statut" class="input">
                        <option value="present">Présent</option>
                        <option value="absent">Absent</option>
                        <option value="retard">Retard</option>
                        <option value="conge">Congé</option>
                        <option value="maladie">Maladie</option>
                        <option value="mission">Mission</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                    <textarea v-model="form.commentaire" rows="3" class="input"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/pointages" class="btn btn-secondary">Annuler</Link>
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

const props = defineProps({ employes: Array });

const form = useForm({
    employe_id: '',
    date_pointage: new Date().toISOString().split('T')[0],
    heure_entree: '',
    heure_sortie: '',
    statut: 'present',
    commentaire: '',
});

const submit = () => form.post('/pointages');
</script>
