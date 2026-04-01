<template>
    <Head title="Guide du Système de Paie Algérien" />
    
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800">📚 Guide du Système de Paie Algérien</h1>
            <p class="text-gray-500 mt-2">Comprendre les cotisations, taxes et calculs de salaire</p>
        </div>
        
        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card bg-blue-50 border-blue-200">
                <div class="text-center">
                    <p class="text-sm text-blue-600 font-medium">SNMG (Salaire Minimum)</p>
                    <p class="text-2xl font-bold text-blue-800">20,000 DZD</p>
                    <p class="text-xs text-blue-500">Depuis Juin 2020</p>
                </div>
            </div>
            <div class="card bg-red-50 border-red-200">
                <div class="text-center">
                    <p class="text-sm text-red-600 font-medium">Cotisation CNAS Salarié</p>
                    <p class="text-2xl font-bold text-red-800">9%</p>
                    <p class="text-xs text-red-500">Du salaire brut</p>
                </div>
            </div>
            <div class="card bg-green-50 border-green-200">
                <div class="text-center">
                    <p class="text-sm text-green-600 font-medium">Exonération IRG</p>
                    <p class="text-2xl font-bold text-green-800">≤ 30,000 DZD</p>
                    <p class="text-xs text-green-500">Salaire net imposable</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cotisations Sociales -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">1</span>
                        Cotisations Sociales (CNAS)
                    </h2>
                    
                    <p class="text-gray-600 mb-4">
                        La sécurité sociale en Algérie est gérée par la <strong>CNAS</strong> (Caisse Nationale des Assurances Sociales). 
                        Les cotisations sont obligatoires et couvrent la maladie, maternité, invalidité, retraite et chômage.
                    </p>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Contribution</th>
                                    <th class="px-4 py-2 text-right">Taux Salarié</th>
                                    <th class="px-4 py-2 text-right">Taux Employeur</th>
                                    <th class="px-4 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="px-4 py-2">Assurances sociales</td>
                                    <td class="px-4 py-2 text-right">1.5%</td>
                                    <td class="px-4 py-2 text-right">12.5%</td>
                                    <td class="px-4 py-2 text-right font-medium">14%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Retraite</td>
                                    <td class="px-4 py-2 text-right">6.75%</td>
                                    <td class="px-4 py-2 text-right">10%</td>
                                    <td class="px-4 py-2 text-right font-medium">16.75%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Assurance chômage</td>
                                    <td class="px-4 py-2 text-right">0.5%</td>
                                    <td class="px-4 py-2 text-right">1%</td>
                                    <td class="px-4 py-2 text-right font-medium">1.5%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Retraite anticipée</td>
                                    <td class="px-4 py-2 text-right">0.25%</td>
                                    <td class="px-4 py-2 text-right">0.25%</td>
                                    <td class="px-4 py-2 text-right font-medium">0.5%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Œuvres sociales</td>
                                    <td class="px-4 py-2 text-right">-</td>
                                    <td class="px-4 py-2 text-right">0.5%</td>
                                    <td class="px-4 py-2 text-right font-medium">0.5%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Logement</td>
                                    <td class="px-4 py-2 text-right">-</td>
                                    <td class="px-4 py-2 text-right">0.5%</td>
                                    <td class="px-4 py-2 text-right font-medium">0.5%</td>
                                </tr>
                                <tr class="bg-gray-100 font-bold">
                                    <td class="px-4 py-2">TOTAL</td>
                                    <td class="px-4 py-2 text-right text-red-600">9%</td>
                                    <td class="px-4 py-2 text-right text-orange-600">25%</td>
                                    <td class="px-4 py-2 text-right">34%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 p-3 bg-yellow-50 rounded-lg text-sm">
                        <p class="text-yellow-800">
                            <strong>💡 Important:</strong> Seuls les <strong>9%</strong> du salarié sont déduits du salaire brut. 
                            Les 25% de l'employeur sont une charge supplémentaire non visible sur la fiche de paie.
                        </p>
                    </div>
                </div>
                
                <!-- IRG -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">2</span>
                        IRG (Impôt sur le Revenu Global)
                    </h2>
                    
                    <p class="text-gray-600 mb-4">
                        L'IRG est calculé sur le <strong>Salaire Net Imposable (SNI)</strong> = Salaire Brut - Cotisations CNAS (9%).
                        Le barème progressif a été révisé par la Loi de Finances 2022.
                    </p>
                    
                    <h3 class="font-medium text-gray-700 mb-2">Barème Annuel IRG</h3>
                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tranche Annuelle</th>
                                    <th class="px-4 py-2 text-left">Tranche Mensuelle</th>
                                    <th class="px-4 py-2 text-right">Taux</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr class="bg-green-50">
                                    <td class="px-4 py-2">0 - 240,000 DZD</td>
                                    <td class="px-4 py-2">0 - 20,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-bold text-green-600">0%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">240,001 - 480,000 DZD</td>
                                    <td class="px-4 py-2">20,001 - 40,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-medium">23%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">480,001 - 960,000 DZD</td>
                                    <td class="px-4 py-2">40,001 - 80,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-medium">27%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">960,001 - 1,920,000 DZD</td>
                                    <td class="px-4 py-2">80,001 - 160,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-medium">30%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">1,920,001 - 3,840,000 DZD</td>
                                    <td class="px-4 py-2">160,001 - 320,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-medium">33%</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">> 3,840,000 DZD</td>
                                    <td class="px-4 py-2">> 320,000 DZD</td>
                                    <td class="px-4 py-2 text-right font-medium">35%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h3 class="font-medium text-gray-700 mb-2">Règles Spéciales</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="font-medium text-green-800">🎉 Exonération Totale</p>
                            <p class="text-sm text-green-700">SNI ≤ 30,000 DZD → IRG = 0</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="font-medium text-blue-800">📉 Zone Dégressive (30,001 - 35,000 DZD)</p>
                            <p class="text-sm text-blue-700">Formule spéciale pour transition douce vers l'imposition</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <p class="font-medium text-purple-800">✂️ Abattement 40%</p>
                            <p class="text-sm text-purple-700">Appliqué sur l'IRG brut (min 1,000 DZD, max 1,500 DZD/mois)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Primes -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600">3</span>
                        Primes et Indemnités
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-gray-800">Prime d'Ancienneté</h4>
                            <p class="text-sm text-gray-600">
                                <strong>1% du salaire de base par année d'ancienneté</strong>, plafonnée à 25%.
                                Calculée automatiquement selon la date d'embauche.
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Exemple: 10 ans = 10% du salaire de base</p>
                        </div>
                        
                        <div class="border-l-4 border-purple-500 pl-4">
                            <h4 class="font-medium text-gray-800">Prime de Rendement</h4>
                            <p class="text-sm text-gray-600">
                                Variable selon la performance. Entièrement <strong>imposable</strong> 
                                et soumise aux cotisations.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Formule de Calcul -->
                <div class="card bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-600">📊</span>
                        Formule de Calcul
                    </h2>
                    
                    <div class="space-y-3 font-mono text-sm">
                        <div class="p-3 bg-white rounded border">
                            <span class="text-blue-600">Salaire Brut</span> = Salaire Base + Primes
                        </div>
                        <div class="p-3 bg-white rounded border">
                            <span class="text-red-600">Cotisation CNAS</span> = Salaire Brut × 9%
                        </div>
                        <div class="p-3 bg-white rounded border">
                            <span class="text-purple-600">SNI</span> = Salaire Brut - Cotisation CNAS
                        </div>
                        <div class="p-3 bg-white rounded border">
                            <span class="text-orange-600">IRG</span> = Barème(SNI) - Abattement 40%
                        </div>
                        <div class="p-3 bg-green-100 rounded border-2 border-green-500">
                            <span class="text-green-700 font-bold">Salaire Net</span> = Salaire Brut - CNAS - IRG
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Coût Employeur -->
                <div class="card bg-orange-50 border-orange-200">
                    <h3 class="font-semibold text-orange-800 mb-3">💼 Coût Total Employeur</h3>
                    <p class="text-sm text-orange-700 mb-3">
                        Pour chaque salarié, l'employeur paie <strong>25% supplémentaires</strong> en charges patronales.
                    </p>
                    <div class="bg-white rounded p-3 text-sm">
                        <p class="font-mono">Coût Total = Brut × 1.25</p>
                        <p class="text-xs text-gray-500 mt-2">
                            Ex: Salaire brut 50,000 DZD<br>
                            Coût employeur = 62,500 DZD
                        </p>
                    </div>
                </div>
                
                <!-- Exemple Pratique -->
                <div class="card">
                    <h3 class="font-semibold text-gray-800 mb-3">📝 Exemple Pratique</h3>
                    <div class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <span>Salaire Base:</span>
                            <span class="font-medium">50,000 DZD</span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span>+ Prime Ancienneté (5%):</span>
                            <span>2,500 DZD</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-medium">Salaire Brut:</span>
                            <span class="font-bold">52,500 DZD</span>
                        </div>
                        <div class="flex justify-between text-red-600">
                            <span>- CNAS (9%):</span>
                            <span>4,725 DZD</span>
                        </div>
                        <div class="flex justify-between">
                            <span>= SNI:</span>
                            <span>47,775 DZD</span>
                        </div>
                        <div class="flex justify-between text-red-600">
                            <span>- IRG:</span>
                            <span>5,388 DZD</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 bg-green-50 -mx-3 px-3 py-2 rounded">
                            <span class="font-bold text-green-800">Salaire Net:</span>
                            <span class="font-bold text-green-600">42,387 DZD</span>
                        </div>
                    </div>
                </div>
                
                <!-- Conseils -->
                <div class="card bg-blue-50 border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-3">💡 Conseils de Paramétrage</h3>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li>✓ Définir le <strong>salaire de base</strong> ≥ SNMG (20,000 DZD)</li>
                        <li>✓ Configurer les <strong>primes par défaut</strong> dans le profil employé</li>
                        <li>✓ Renseigner la <strong>date d'embauche</strong> pour le calcul auto de l'ancienneté</li>
                        <li>✓ Ajouter le <strong>numéro CNAS</strong> pour les déclarations</li>
                    </ul>
                </div>
                
                <!-- Références -->
                <div class="card text-sm">
                    <h3 class="font-semibold text-gray-800 mb-2">📚 Références</h3>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Loi de Finances 2022 (IRG)</li>
                        <li>• Code des Impôts Directs (CIDTA)</li>
                        <li>• Décret 21-137 (SNMG)</li>
                        <li>• Loi 83-14 (Cotisations CNAS)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>
