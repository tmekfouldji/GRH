<template>
    <Head :title="`Saisie des pièces - ${paie.periode}`" />

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="`/paies-mensuelles/${paie.id}`" class="text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Saisie des pièces fabriquées</h2>
                    <p class="text-sm text-gray-500">{{ paie.periode }} - {{ fichesPiece.length }} employé(s) à la pièce</p>
                </div>
            </div>
        </div>

        <div v-if="fichesPiece.length === 0" class="card text-center py-12 text-gray-500">
            <p>Aucun employé à la pièce dans cette paie mensuelle.</p>
            <Link :href="`/paies-mensuelles/${paie.id}`" class="btn btn-secondary mt-4 inline-block">Retour</Link>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-6">
            <div class="card p-0 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matricule</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prénom</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prime/pièce</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pièces fabriquées</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(fiche, index) in form.pieces" :key="fiche.fiche_id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ fichesPiece[index].employe_matricule }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ fichesPiece[index].employe_nom }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ fichesPiece[index].employe_prenom }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ formatMoney(fichesPiece[index].prime_par_piece_snapshot) }}</td>
                            <td class="px-4 py-3 text-center">
                                <input
                                    v-model.number="fiche.pieces_fabriquees"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="input w-28 text-center mx-auto"
                                />
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">
                                {{ formatMoney((fichesPiece[index].prime_par_piece_snapshot || 0) * (fiche.pieces_fabriquees || 0)) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">Total primes pièces:</td>
                            <td class="px-4 py-3 text-right font-bold text-green-700 text-lg">{{ formatMoney(totalPrimes) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <Link :href="`/paies-mensuelles/${paie.id}`" class="btn btn-secondary">Annuler</Link>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                    Enregistrer les pièces
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';
import { formatMoney } from '@/utils/formatters';

const props = defineProps({
    paie: Object,
    fichesPiece: Array,
});

const form = useForm({
    pieces: props.fichesPiece.map(f => ({
        fiche_id: f.id,
        pieces_fabriquees: f.pieces_fabriquees || 0,
    })),
});

const totalPrimes = computed(() => {
    return form.pieces.reduce((sum, entry, i) => {
        const prime = props.fichesPiece[i]?.prime_par_piece_snapshot || 0;
        return sum + (prime * (entry.pieces_fabriquees || 0));
    }, 0);
});

const submit = () => {
    form.post(`/paies-mensuelles/${props.paie.id}/saisie-pieces`);
};
</script>
