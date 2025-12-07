<template>
    <Head title="Modifier le congé" />
    
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Modifier le congé</h2>
                <Link href="/conges" class="text-gray-500 hover:text-gray-700"><X class="w-5 h-5" /></Link>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
                    <select v-model="form.employe_id" class="input">
                        <option v-for="emp in employes" :key="emp.id" :value="emp.id">{{ emp.prenom }} {{ emp.nom }}</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de congé *</label>
                    <select v-model="form.type" class="input">
                        <option value="annuel">Congé annuel</option>
                        <option value="maladie">Congé maladie</option>
                        <option value="maternite">Congé maternité</option>
                        <option value="paternite">Congé paternité</option>
                        <option value="sans_solde">Congé sans solde</option>
                        <option value="exceptionnel">Congé exceptionnel</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                        <input v-model="form.date_debut" type="date" class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
                        <input v-model="form.date_fin" type="date" class="input" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                    <select v-model="form.statut" class="input">
                        <option value="en_attente">En attente</option>
                        <option value="approuve">Approuvé</option>
                        <option value="refuse">Refusé</option>
                        <option value="annule">Annulé</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
                    <textarea v-model="form.motif" rows="3" class="input"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire responsable</label>
                    <textarea v-model="form.commentaire_responsable" rows="2" class="input"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/conges" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, Loader2 } from 'lucide-vue-next';

const props = defineProps({ conge: Object, employes: Array });

const form = useForm({
    employe_id: props.conge.employe_id,
    type: props.conge.type,
    date_debut: props.conge.date_debut?.split('T')[0],
    date_fin: props.conge.date_fin?.split('T')[0],
    statut: props.conge.statut,
    motif: props.conge.motif || '',
    commentaire_responsable: props.conge.commentaire_responsable || '',
});

const submit = () => form.put(`/conges/${props.conge.id}`);
</script>
