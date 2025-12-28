<template>
    <Head :title="`Modifier - ${employe.prenom} ${employe.nom}`" />
    
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-3 card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Modifier l'employé</h2>
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
                            <input v-model="form.matricule" type="text" class="input" :class="{ 'border-red-500': form.errors.matricule }" />
                            <p v-if="form.errors.matricule" class="text-red-500 text-sm mt-1">{{ form.errors.matricule }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CIN</label>
                            <input v-model="form.cin" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.nom" type="text" class="input" :class="{ 'border-red-500': form.errors.nom }" />
                            <p v-if="form.errors.nom" class="text-red-500 text-sm mt-1">{{ form.errors.nom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input v-model="form.prenom" type="text" class="input" :class="{ 'border-red-500': form.errors.prenom }" />
                            <p v-if="form.errors.prenom" class="text-red-500 text-sm mt-1">{{ form.errors.prenom }}</p>
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Poste</label>
                            <input v-model="form.poste" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                            <input v-model="form.categorie" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Échelon</label>
                            <input v-model="form.echelon" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                            <input v-model="form.departement" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche *</label>
                            <input v-model="form.date_embauche" type="date" class="input" :class="{ 'border-red-500': form.errors.date_embauche }" />
                            <p v-if="form.errors.date_embauche" class="text-red-500 text-sm mt-1">{{ form.errors.date_embauche }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select v-model="form.statut" class="input">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="conge">En congé</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration Salaire -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">💰 Configuration Salaire</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire de base (DZD) *</label>
                            <input v-model="form.salaire_base" type="number" step="100" class="input" :class="{ 'border-red-500': form.errors.salaire_base }" />
                            <p v-if="form.errors.salaire_base" class="text-red-500 text-sm mt-1">{{ form.errors.salaire_base }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prime transport (DZD)</label>
                            <input v-model="form.prime_transport_defaut" type="number" step="100" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prime panier (DZD)</label>
                            <input v-model="form.prime_panier_defaut" type="number" step="100" class="input" />
                        </div>
                    </div>
                    
                    <div v-if="form.salaire_base > 0" class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-2">Aperçu salaire estimé</p>
                        <div class="flex justify-between text-sm">
                            <span>Salaire net estimé:</span>
                            <span class="font-bold text-green-600">~ {{ formatMoney(estimerSalaireNet()) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Sécurité sociale & Paiement -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Sécurité sociale & Paiement</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° CNAS</label>
                            <input v-model="form.numero_cnas" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                            <select v-model="form.mode_paiement" class="input">
                                <option value="virement">Virement bancaire</option>
                                <option value="especes">Espèces</option>
                                <option value="cheque">Chèque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RIB</label>
                            <input v-model="form.rib" type="text" class="input" />
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/employes" class="btn btn-secondary">Annuler</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Info Sidebar -->
        <div class="space-y-4">
            <div class="card bg-blue-50 border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                    <Info class="w-4 h-4" />
                    Guide Rapide
                </h3>
                <ul class="text-sm text-blue-700 space-y-2">
                    <li>• <strong>SNMG:</strong> 20,000 DZD minimum</li>
                    <li>• <strong>CNAS:</strong> 9% du brut (salarié)</li>
                    <li>• <strong>IRG:</strong> Exonéré si SNI ≤ 30,000 DZD</li>
                </ul>
                <Link href="/guide-salaire" class="mt-3 text-xs text-blue-600 hover:underline flex items-center gap-1">
                    Voir le guide complet →
                </Link>
            </div>
            
            <div class="card">
                <h3 class="font-semibold text-gray-800 mb-3">📊 Barème IRG</h3>
                <div class="text-xs space-y-1">
                    <div class="flex justify-between">
                        <span>0 - 20k</span>
                        <span class="text-green-600 font-medium">0%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>20k - 40k</span>
                        <span>23%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>40k - 80k</span>
                        <span>27%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>80k - 160k</span>
                        <span>30%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>> 160k</span>
                        <span>33-35%</span>
                    </div>
                </div>
            </div>
            
            <div class="card bg-green-50 border-green-200">
                <h3 class="font-semibold text-green-800 mb-2">💡 Conseil</h3>
                <p class="text-xs text-green-700">
                    Configurez les primes par défaut pour qu'elles soient automatiquement appliquées à chaque fiche de paie.
                </p>
            </div>
            
            <div class="card bg-orange-50 border-orange-200">
                <h3 class="font-semibold text-orange-800 mb-2">💼 Coût Employeur</h3>
                <p class="text-xs text-orange-700">
                    L'employeur paie <strong>+25%</strong> en charges patronales sur le salaire brut.
                </p>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { X, Loader2, Info } from 'lucide-vue-next';
import { formatMoney } from '@/utils/formatters';

const props = defineProps({
    employe: Object,
});

const form = useForm({
    matricule: props.employe.matricule,
    nom: props.employe.nom,
    prenom: props.employe.prenom,
    email: props.employe.email || '',
    telephone: props.employe.telephone || '',
    poste: props.employe.poste || '',
    categorie: props.employe.categorie || '',
    echelon: props.employe.echelon || '',
    departement: props.employe.departement || '',
    date_embauche: props.employe.date_embauche?.split('T')[0] || '',
    salaire_base: props.employe.salaire_base,
    prime_transport_defaut: props.employe.prime_transport_defaut || 0,
    prime_panier_defaut: props.employe.prime_panier_defaut || 0,
    statut: props.employe.statut,
    adresse: props.employe.adresse || '',
    cin: props.employe.cin || '',
    numero_cnas: props.employe.numero_cnas || '',
    mode_paiement: props.employe.mode_paiement || 'virement',
    rib: props.employe.rib || '',
});

const estimerSalaireNet = () => {
    const base = parseFloat(form.salaire_base) || 0;
    const transport = parseFloat(form.prime_transport_defaut) || 0;
    const panier = parseFloat(form.prime_panier_defaut) || 0;
    const brut = base + transport + panier;
    const cnas = brut * 0.09;
    const sni = brut - cnas;
    
    let irg = 0;
    if (sni > 30000) {
        if (sni <= 40000) irg = (sni - 20000) * 0.23;
        else if (sni <= 80000) irg = 20000 * 0.23 + (sni - 40000) * 0.27;
        else irg = 20000 * 0.23 + 40000 * 0.27 + (sni - 80000) * 0.30;
        irg = Math.max(0, irg - 1000);
    }
    
    return brut - cnas - irg;
};

const submit = () => {
    form.put(`/employes/${props.employe.id}`);
};
</script>
