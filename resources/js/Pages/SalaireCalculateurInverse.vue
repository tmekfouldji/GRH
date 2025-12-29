<template>
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Simulateur Inversé</h1>
                <p class="text-gray-500 mt-1">Calculez le salaire brut à partir du salaire net souhaité</p>
            </div>
            <div class="flex gap-2">
                <Link href="/simulateur-salaire" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
                    <Calculator class="w-5 h-5" />
                    <span>Simulateur normal</span>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Input Form -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <ArrowUpDown class="w-5 h-5 text-green-600" />
                        Salaire Net Souhaité
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salaire net souhaité (DZD)</label>
                            <input 
                                v-model.number="salaireNetSouhaite" 
                                type="number" 
                                min="18200"
                                step="1000"
                                class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                placeholder="40000"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Net minimum possible: ~18,200 DZD (SNMG)
                            </p>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Options supplémentaires</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <input 
                                        v-model="inclurePrimeTransport" 
                                        type="checkbox" 
                                        id="primeTransport"
                                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                    />
                                    <label for="primeTransport" class="text-sm text-gray-700">Inclure prime transport (3,000 DZD)</label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input 
                                        v-model="inclurePrimePanier" 
                                        type="checkbox" 
                                        id="primePanier"
                                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                    />
                                    <label for="primePanier" class="text-sm text-gray-700">Inclure prime panier (2,000 DZD)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Salaires nets cibles</h3>
                    <div class="flex flex-wrap gap-2">
                        <button @click="salaireNetSouhaite = 25000" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            25,000
                        </button>
                        <button @click="salaireNetSouhaite = 35000" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            35,000
                        </button>
                        <button @click="salaireNetSouhaite = 50000" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            50,000
                        </button>
                        <button @click="salaireNetSouhaite = 75000" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            75,000
                        </button>
                        <button @click="salaireNetSouhaite = 100000" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            100,000
                        </button>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 rounded-xl border border-blue-100 p-4">
                    <h3 class="text-sm font-medium text-blue-800 mb-2 flex items-center gap-2">
                        <Info class="w-4 h-4" />
                        Comment ça marche ?
                    </h3>
                    <p class="text-xs text-blue-700">
                        Ce simulateur calcule le salaire brut nécessaire pour obtenir le net souhaité, 
                        en tenant compte des cotisations CNAS (9%) et de l'IRG avec abattement.
                    </p>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                        <p class="text-xs text-green-600 font-medium">Salaire Net Cible</p>
                        <p class="text-xl font-bold text-green-800">{{ formatNumber(salaireNetSouhaite) }}</p>
                        <p class="text-xs text-green-500">DZD</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <p class="text-xs text-blue-600 font-medium">Salaire Brut Requis</p>
                        <p class="text-xl font-bold text-blue-800">{{ formatNumber(salaireBrutRequis) }}</p>
                        <p class="text-xs text-blue-500">DZD</p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                        <p class="text-xs text-purple-600 font-medium">Salaire de Base</p>
                        <p class="text-xl font-bold text-purple-800">{{ formatNumber(salaireBaseRequis) }}</p>
                        <p class="text-xs text-purple-500">DZD</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
                        <p class="text-xs text-orange-600 font-medium">Coût Employeur</p>
                        <p class="text-xl font-bold text-orange-800">{{ formatNumber(coutEmployeur) }}</p>
                        <p class="text-xs text-orange-500">DZD</p>
                    </div>
                </div>

                <!-- Detailed Breakdown -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Détail du calcul inversé</h2>
                    
                    <div class="space-y-4">
                        <!-- Reverse Flow -->
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <p class="text-xs text-green-600 font-medium">Net Souhaité</p>
                                    <p class="text-lg font-bold text-green-800">{{ formatNumber(salaireNetSouhaite) }}</p>
                                </div>
                                <ArrowRight class="w-6 h-6 text-gray-400" />
                                <div class="text-center">
                                    <p class="text-xs text-blue-600 font-medium">Brut Calculé</p>
                                    <p class="text-lg font-bold text-blue-800">{{ formatNumber(salaireBrutRequis) }}</p>
                                </div>
                                <ArrowRight class="w-6 h-6 text-gray-400" />
                                <div class="text-center">
                                    <p class="text-xs text-purple-600 font-medium">Base Requise</p>
                                    <p class="text-lg font-bold text-purple-800">{{ formatNumber(salaireBaseRequis) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Composition -->
                        <div class="border-b pb-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Composition du Salaire Brut Requis</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Salaire de base requis</span>
                                    <span class="font-medium">{{ formatNumber(salaireBaseRequis) }} DZD</span>
                                </div>
                                <div v-if="primeAnciennete > 0" class="flex justify-between text-green-600">
                                    <span>+ Prime d'ancienneté ({{ Math.min(anciennete, 25) }}%)</span>
                                    <span>{{ formatNumber(primeAnciennete) }} DZD</span>
                                </div>
                                <div v-if="inclurePrimeTransport" class="flex justify-between text-green-600">
                                    <span>+ Prime de transport</span>
                                    <span>3,000 DZD</span>
                                </div>
                                <div v-if="inclurePrimePanier" class="flex justify-between text-green-600">
                                    <span>+ Prime de panier</span>
                                    <span>2,000 DZD</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t">
                                    <span>= Salaire Brut</span>
                                    <span class="text-blue-600">{{ formatNumber(salaireBrutRequis) }} DZD</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div class="border-b pb-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Déductions calculées</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between text-red-600">
                                    <span>- CNAS (9%)</span>
                                    <span>{{ formatNumber(cotisationCNAS) }} DZD</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">= SNI (Salaire Net Imposable)</span>
                                    <span>{{ formatNumber(sni) }} DZD</span>
                                </div>
                                <div class="flex justify-between text-orange-600">
                                    <span>- IRG</span>
                                    <span>{{ formatNumber(irg) }} DZD</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-green-800 mb-2 flex items-center gap-2">
                                <CheckCircle class="w-4 h-4" />
                                Vérification
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-green-700">Salaire Brut</span>
                                    <span class="font-medium">{{ formatNumber(salaireBrutRequis) }} DZD</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span>- CNAS</span>
                                    <span>{{ formatNumber(cotisationCNAS) }} DZD</span>
                                </div>
                                <div class="flex justify-between text-orange-600">
                                    <span>- IRG</span>
                                    <span>{{ formatNumber(irg) }} DZD</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t border-green-200">
                                    <span class="text-green-800">= Salaire Net Calculé</span>
                                    <span class="text-green-600">{{ formatNumber(salaireNetCalcule) }} DZD</span>
                                </div>
                                <div v-if="Math.abs(salaireNetCalcule - salaireNetSouhaite) <= 1" class="text-xs text-green-600 mt-2">
                                    ✅ Correspond au net souhaité
                                </div>
                                <div v-else class="text-xs text-yellow-600 mt-2">
                                    ⚠️ Écart de {{ formatNumber(Math.abs(salaireNetCalcule - salaireNetSouhaite)) }} DZD (arrondi)
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
                            <p class="font-bold text-orange-800">{{ formatNumber(salaireBrutRequis) }} DZD</p>
                        </div>
                        <div>
                            <p class="text-orange-700">Charges patronales (25%)</p>
                            <p class="font-bold text-orange-800">{{ formatNumber(chargesPatronales) }} DZD</p>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-orange-200">
                            <div class="flex justify-between items-center">
                                <p class="font-medium text-orange-800">Coût total pour un net de {{ formatNumber(salaireNetSouhaite) }} DZD</p>
                                <p class="text-2xl font-bold text-orange-900">{{ formatNumber(coutEmployeur) }} DZD</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Tableau comparatif</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Élément</th>
                                    <th class="px-4 py-2 text-right">Montant</th>
                                    <th class="px-4 py-2 text-right">% du Brut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="px-4 py-2 font-medium">Salaire Brut</td>
                                    <td class="px-4 py-2 text-right">{{ formatNumber(salaireBrutRequis) }} DZD</td>
                                    <td class="px-4 py-2 text-right">100%</td>
                                </tr>
                                <tr class="text-red-600">
                                    <td class="px-4 py-2">CNAS Salarié</td>
                                    <td class="px-4 py-2 text-right">-{{ formatNumber(cotisationCNAS) }} DZD</td>
                                    <td class="px-4 py-2 text-right">9%</td>
                                </tr>
                                <tr class="text-orange-600">
                                    <td class="px-4 py-2">IRG</td>
                                    <td class="px-4 py-2 text-right">-{{ formatNumber(irg) }} DZD</td>
                                    <td class="px-4 py-2 text-right">{{ salaireBrutRequis > 0 ? ((irg / salaireBrutRequis) * 100).toFixed(1) : 0 }}%</td>
                                </tr>
                                <tr class="bg-green-50 font-bold">
                                    <td class="px-4 py-2 text-green-800">Salaire Net</td>
                                    <td class="px-4 py-2 text-right text-green-600">{{ formatNumber(salaireNetCalcule) }} DZD</td>
                                    <td class="px-4 py-2 text-right text-green-600">{{ salaireBrutRequis > 0 ? ((salaireNetCalcule / salaireBrutRequis) * 100).toFixed(1) : 0 }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calculator, ArrowUpDown, ArrowRight, Building, CheckCircle, Info } from 'lucide-vue-next';
import { calculateFromNet, calculateEmployerCost } from '@/utils/salaryCalculator';

// Input values
const salaireNetSouhaite = ref(40000);
const inclurePrimeTransport = ref(false);
const inclurePrimePanier = ref(false);

// Calculate primes
const primesTotal = computed(() => {
    let primes = 0;
    if (inclurePrimeTransport.value) primes += 3000;
    if (inclurePrimePanier.value) primes += 2000;
    return primes;
});

// Use unified salary calculator
const salaryResult = computed(() => {
    return calculateFromNet(salaireNetSouhaite.value, {
        primeTransport: inclurePrimeTransport.value ? 3000 : 0,
        primePanier: inclurePrimePanier.value ? 2000 : 0,
    });
});

const salaireBrutRequis = computed(() => salaryResult.value.totalBrut);
const salaireBaseRequis = computed(() => salaryResult.value.salaireBrut);
const cotisationCNAS = computed(() => salaryResult.value.cotisationCNAS);
const sni = computed(() => salaryResult.value.sni);
const irg = computed(() => salaryResult.value.irg);
const salaireNetCalcule = computed(() => salaryResult.value.salaireNet);

const chargesPatronales = computed(() => {
    return Math.round(salaireBrutRequis.value * 0.25);
});

const coutEmployeur = computed(() => {
    return salaireBrutRequis.value + chargesPatronales.value;
});

// Format numbers
const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(num));
};
</script>
