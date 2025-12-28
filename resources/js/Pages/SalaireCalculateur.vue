<template>
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Simulateur de Salaire</h1>
                <p class="text-gray-500 mt-1">Calculez le salaire net avec toutes les cotisations algériennes</p>
            </div>
            <Link href="/guide-salaire" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
                <BookOpen class="w-5 h-5" />
                <span>Guide complet</span>
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Input Form -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <Calculator class="w-5 h-5 text-blue-600" />
                        Paramètres
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire de base (DZD)</label>
                            <input 
                                v-model.number="salaireBase" 
                                type="number" 
                                min="20000"
                                step="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="50000"
                            />
                            <p v-if="salaireBase < 20000" class="mt-1 text-xs text-red-500">
                                ⚠️ SNMG minimum: 20,000 DZD
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ancienneté (années)</label>
                            <input 
                                v-model.number="anciennete" 
                                type="number" 
                                min="0"
                                max="30"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="5"
                            />
                            <p class="mt-1 text-xs text-gray-500">Prime: {{ Math.min(anciennete, 25) }}% (max 25%)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prime de transport (DZD)</label>
                            <input 
                                v-model.number="primeTransport" 
                                type="number" 
                                min="0"
                                step="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="3000"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prime de panier (DZD)</label>
                            <input 
                                v-model.number="primePanier" 
                                type="number" 
                                min="0"
                                step="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="2000"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Autres primes (DZD)</label>
                            <input 
                                v-model.number="autresPrimes" 
                                type="number" 
                                min="0"
                                step="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="0"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Heures supplémentaires</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input 
                                    v-model.number="heuresSup" 
                                    type="number" 
                                    min="0"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Heures"
                                />
                                <select v-model="tauxHeuresSup" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <option value="1.5">+50% (jour)</option>
                                    <option value="1.75">+75% (nuit)</option>
                                    <option value="2">+100% (férié)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Exemples rapides</h3>
                    <div class="flex flex-wrap gap-2">
                        <button @click="setPreset('smic')" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            SNMG
                        </button>
                        <button @click="setPreset('moyen')" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Moyen
                        </button>
                        <button @click="setPreset('cadre')" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cadre
                        </button>
                        <button @click="setPreset('directeur')" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Directeur
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <p class="text-xs text-blue-600 font-medium">Salaire Brut</p>
                        <p class="text-xl font-bold text-blue-800">{{ formatNumber(salaireBrut) }}</p>
                        <p class="text-xs text-blue-500">DZD</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                        <p class="text-xs text-red-600 font-medium">CNAS (9%)</p>
                        <p class="text-xl font-bold text-red-800">{{ formatNumber(cotisationCNAS) }}</p>
                        <p class="text-xs text-red-500">DZD</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
                        <p class="text-xs text-orange-600 font-medium">IRG</p>
                        <p class="text-xl font-bold text-orange-800">{{ formatNumber(irg) }}</p>
                        <p class="text-xs text-orange-500">DZD</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                        <p class="text-xs text-green-600 font-medium">Salaire Net</p>
                        <p class="text-xl font-bold text-green-800">{{ formatNumber(salaireNet) }}</p>
                        <p class="text-xs text-green-500">DZD</p>
                    </div>
                </div>

                <!-- Detailed Breakdown -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Détail du calcul</h2>
                    
                    <div class="space-y-4">
                        <!-- Salaire Brut Section -->
                        <div class="border-b pb-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Plus class="w-4 h-4 text-blue-600" />
                                Composition du Salaire Brut
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Salaire de base</span>
                                    <span class="font-medium">{{ formatNumber(salaireBase) }} DZD</span>
                                </div>
                                <div v-if="primeAnciennete > 0" class="flex justify-between text-green-600">
                                    <span>+ Prime d'ancienneté ({{ Math.min(anciennete, 25) }}%)</span>
                                    <span>{{ formatNumber(primeAnciennete) }} DZD</span>
                                </div>
                                <div v-if="primeTransport > 0" class="flex justify-between text-green-600">
                                    <span>+ Prime de transport</span>
                                    <span>{{ formatNumber(primeTransport) }} DZD</span>
                                </div>
                                <div v-if="primePanier > 0" class="flex justify-between text-green-600">
                                    <span>+ Prime de panier</span>
                                    <span>{{ formatNumber(primePanier) }} DZD</span>
                                </div>
                                <div v-if="autresPrimes > 0" class="flex justify-between text-green-600">
                                    <span>+ Autres primes</span>
                                    <span>{{ formatNumber(autresPrimes) }} DZD</span>
                                </div>
                                <div v-if="montantHeuresSup > 0" class="flex justify-between text-green-600">
                                    <span>+ Heures supplémentaires ({{ heuresSup }}h × {{ tauxHeuresSup }})</span>
                                    <span>{{ formatNumber(montantHeuresSup) }} DZD</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t">
                                    <span>= Salaire Brut</span>
                                    <span class="text-blue-600">{{ formatNumber(salaireBrut) }} DZD</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cotisations Section -->
                        <div class="border-b pb-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Minus class="w-4 h-4 text-red-600" />
                                Cotisations Sociales (CNAS)
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between text-red-600">
                                    <span>Cotisation salarié (9%)</span>
                                    <span>-{{ formatNumber(cotisationCNAS) }} DZD</span>
                                </div>
                                <div class="flex justify-between font-medium pt-2 border-t">
                                    <span>= Salaire Net Imposable (SNI)</span>
                                    <span class="text-purple-600">{{ formatNumber(sni) }} DZD</span>
                                </div>
                            </div>
                        </div>

                        <!-- IRG Section -->
                        <div class="border-b pb-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Minus class="w-4 h-4 text-orange-600" />
                                Impôt sur le Revenu (IRG)
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div v-if="sni <= 30000" class="p-2 bg-green-50 rounded text-green-700 text-xs">
                                    ✅ Exonération totale (SNI ≤ 30,000 DZD)
                                </div>
                                <div v-else-if="sni <= 35000" class="p-2 bg-blue-50 rounded text-blue-700 text-xs">
                                    📉 Zone dégressive (30,001 - 35,000 DZD)
                                </div>
                                <template v-else>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">IRG brut (barème progressif)</span>
                                        <span>{{ formatNumber(irgBrut) }} DZD</span>
                                    </div>
                                    <div class="flex justify-between text-green-600">
                                        <span>- Abattement 40% (min 1000, max 1500)</span>
                                        <span>-{{ formatNumber(abattement) }} DZD</span>
                                    </div>
                                </template>
                                <div class="flex justify-between font-medium text-orange-600 pt-2 border-t">
                                    <span>= IRG à payer</span>
                                    <span>-{{ formatNumber(irg) }} DZD</span>
                                </div>
                            </div>
                        </div>

                        <!-- Final Result -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-green-700">Salaire Net à Payer</p>
                                    <p class="text-3xl font-bold text-green-800">{{ formatNumber(salaireNet) }} DZD</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-green-600">Taux de rétention</p>
                                    <p class="text-lg font-bold text-green-700">{{ tauxRetention }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employer Cost -->
                <div class="bg-orange-50 rounded-xl border border-orange-100 p-6">
                    <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center gap-2">
                        <Building class="w-5 h-5" />
                        Coût Total Employeur
                    </h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-orange-700">Salaire brut</p>
                            <p class="font-bold text-orange-800">{{ formatNumber(salaireBrut) }} DZD</p>
                        </div>
                        <div>
                            <p class="text-orange-700">Charges patronales (25%)</p>
                            <p class="font-bold text-orange-800">{{ formatNumber(chargesPatronales) }} DZD</p>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-orange-200">
                            <div class="flex justify-between items-center">
                                <p class="font-medium text-orange-800">Coût total employeur</p>
                                <p class="text-2xl font-bold text-orange-900">{{ formatNumber(coutEmployeur) }} DZD</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calculator, BookOpen, Plus, Minus, Building } from 'lucide-vue-next';

// Input values
const salaireBase = ref(50000);
const anciennete = ref(5);
const primeTransport = ref(3000);
const primePanier = ref(2000);
const autresPrimes = ref(0);
const heuresSup = ref(0);
const tauxHeuresSup = ref(1.5);

// Computed values
const primeAnciennete = computed(() => {
    const taux = Math.min(anciennete.value, 25) / 100;
    return Math.round(salaireBase.value * taux);
});

const tauxHoraire = computed(() => {
    return salaireBase.value / 173.33; // 173.33 heures par mois
});

const montantHeuresSup = computed(() => {
    return Math.round(heuresSup.value * tauxHoraire.value * tauxHeuresSup.value);
});

const salaireBrut = computed(() => {
    return salaireBase.value + primeAnciennete.value + primeTransport.value + 
           primePanier.value + autresPrimes.value + montantHeuresSup.value;
});

const cotisationCNAS = computed(() => {
    return Math.round(salaireBrut.value * 0.09);
});

const sni = computed(() => {
    return salaireBrut.value - cotisationCNAS.value;
});

const irgBrut = computed(() => {
    const sniVal = sni.value;
    
    // Exonération totale si SNI <= 30,000
    if (sniVal <= 30000) {
        return 0;
    }
    
    // Zone dégressive 30,001 - 35,000
    if (sniVal <= 35000) {
        // Formule spéciale pour transition douce
        const depassement = sniVal - 30000;
        return Math.round(depassement * 0.2); // ~20% du dépassement
    }
    
    // Barème progressif mensuel
    let irg = 0;
    
    // Tranche 1: 0 - 20,000 → 0%
    // Tranche 2: 20,001 - 40,000 → 23%
    if (sniVal > 20000) {
        const tranche2 = Math.min(sniVal, 40000) - 20000;
        irg += tranche2 * 0.23;
    }
    
    // Tranche 3: 40,001 - 80,000 → 27%
    if (sniVal > 40000) {
        const tranche3 = Math.min(sniVal, 80000) - 40000;
        irg += tranche3 * 0.27;
    }
    
    // Tranche 4: 80,001 - 160,000 → 30%
    if (sniVal > 80000) {
        const tranche4 = Math.min(sniVal, 160000) - 80000;
        irg += tranche4 * 0.30;
    }
    
    // Tranche 5: 160,001 - 320,000 → 33%
    if (sniVal > 160000) {
        const tranche5 = Math.min(sniVal, 320000) - 160000;
        irg += tranche5 * 0.33;
    }
    
    // Tranche 6: > 320,000 → 35%
    if (sniVal > 320000) {
        const tranche6 = sniVal - 320000;
        irg += tranche6 * 0.35;
    }
    
    return Math.round(irg);
});

const abattement = computed(() => {
    if (sni.value <= 30000) return 0;
    
    // Abattement 40% avec min 1000 et max 1500
    const abat = irgBrut.value * 0.40;
    return Math.round(Math.min(Math.max(abat, 1000), 1500));
});

const irg = computed(() => {
    if (sni.value <= 30000) return 0;
    if (sni.value <= 35000) return irgBrut.value; // Pas d'abattement en zone dégressive
    
    return Math.max(0, irgBrut.value - abattement.value);
});

const salaireNet = computed(() => {
    return salaireBrut.value - cotisationCNAS.value - irg.value;
});

const chargesPatronales = computed(() => {
    return Math.round(salaireBrut.value * 0.25);
});

const coutEmployeur = computed(() => {
    return salaireBrut.value + chargesPatronales.value;
});

const tauxRetention = computed(() => {
    if (salaireBrut.value === 0) return 0;
    return Math.round((salaireNet.value / salaireBrut.value) * 100);
});

// Format numbers
const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(num));
};

// Presets
const setPreset = (preset) => {
    switch (preset) {
        case 'smic':
            salaireBase.value = 20000;
            anciennete.value = 0;
            primeTransport.value = 2000;
            primePanier.value = 0;
            autresPrimes.value = 0;
            break;
        case 'moyen':
            salaireBase.value = 45000;
            anciennete.value = 5;
            primeTransport.value = 3000;
            primePanier.value = 2000;
            autresPrimes.value = 0;
            break;
        case 'cadre':
            salaireBase.value = 80000;
            anciennete.value = 10;
            primeTransport.value = 5000;
            primePanier.value = 3000;
            autresPrimes.value = 5000;
            break;
        case 'directeur':
            salaireBase.value = 150000;
            anciennete.value = 15;
            primeTransport.value = 10000;
            primePanier.value = 5000;
            autresPrimes.value = 20000;
            break;
    }
    heuresSup.value = 0;
};
</script>
