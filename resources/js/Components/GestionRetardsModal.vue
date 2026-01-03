<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>
            
            <!-- Modal -->
            <div class="relative bg-white rounded-xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Clock class="w-6 h-6 text-white" />
                            <div>
                                <h2 class="text-xl font-bold text-white">Gestion Retards & Absences</h2>
                                <p class="text-orange-100 text-sm">{{ fiche?.employe?.prenom }} {{ fiche?.employe?.nom }} - {{ periode }}</p>
                            </div>
                        </div>
                        <button @click="close" class="text-white hover:bg-white/20 rounded-full p-2">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-6 gap-3 mb-6">
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-blue-600">{{ totalDaysCount }}</p>
                            <p class="text-xs text-blue-600">Jours Total</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-green-600">{{ presentCount }}</p>
                            <p class="text-xs text-green-600">Présents</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-yellow-600">{{ lateCount }}</p>
                            <p class="text-xs text-yellow-600">Retards</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-red-600">{{ excludedCount }}</p>
                            <p class="text-xs text-red-600">Exclus</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-purple-600">{{ includedCount }}</p>
                            <p class="text-xs text-purple-600">Inclus</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-orange-600">{{ formatMoney(totalPenalty) }}</p>
                            <p class="text-xs text-orange-600">Total Pénalité</p>
                        </div>
                    </div>
                    
                    <!-- Days Table -->
                    <div class="border rounded-lg overflow-hidden mb-6">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">Date</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Entrée</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Sortie</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Heures</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        <span class="flex items-center justify-center gap-1">
                                            <input type="checkbox" @change="toggleAllLate" class="rounded" />
                                            Retard
                                        </span>
                                    </th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Pénalité (DA)</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        <span class="flex items-center justify-center gap-1">
                                            <input type="checkbox" :checked="allIncluded" @change="toggleAllIncluded" class="rounded" />
                                            Inclure
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(day, index) in allDays" :key="day.date" 
                                    :class="getDayRowClass(day)">
                                    <td class="px-2 py-1">
                                        <span class="font-medium text-xs" :class="day.isWeekend ? 'text-orange-600' : ''">
                                            {{ day.label }}
                                        </span>
                                        <span v-if="day.isWeekend" class="ml-1 text-xs bg-orange-200 text-orange-700 px-1 rounded">WE</span>
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <select v-model="day.statut" 
                                            class="input text-xs py-0.5 w-24"
                                            :class="day.isWeekend ? 'bg-orange-50 border-orange-200' : ''"
                                            @change="onDayChange(index)">
                                            <option value="absent">Absent</option>
                                            <option value="present">Présent</option>
                                            <option value="conge">Congé</option>
                                            <option value="maladie">Maladie</option>
                                            <option value="mission">Mission</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="time" v-model="day.heure_entree" 
                                            class="input text-sm py-1 px-2 w-28"
                                            :class="day.isWeekend ? 'bg-orange-50 border-orange-200' : ''"
                                            @change="onTimeChange(index)" />
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="time" v-model="day.heure_sortie" 
                                            class="input text-sm py-1 px-2 w-28"
                                            :class="day.isWeekend ? 'bg-orange-50 border-orange-200' : ''"
                                            @change="onTimeChange(index)" />
                                    </td>
                                    <td class="px-2 py-1 text-center text-xs">
                                        {{ day.heures_travaillees || 0 }}h
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="checkbox" v-model="day.isLate" 
                                            class="rounded text-yellow-500 focus:ring-yellow-500"
                                            @change="onLateChange(index)" />
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="number" v-model.number="day.penalty" 
                                            class="input text-xs py-0.5 w-20 text-center"
                                            :class="day.penalty > 0 ? 'bg-red-50 border-red-300 text-red-700' : ''"
                                            min="0" step="100" />
                                    </td>
                                    <td class="px-2 py-1 text-center">
                                        <input type="checkbox" v-model="day.included" 
                                            class="rounded text-green-500 focus:ring-green-500" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Deductions Summary -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-800">💰 Total Déductions du Mois</h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ includedCount }} jour(s) inclus avec pénalités
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-red-600">{{ formatMoney(totalPenalty) }}</p>
                                <p class="text-xs text-gray-500">Somme des pénalités incluses</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center gap-4">
                            <div>
                                <span class="text-xs text-gray-500">Brut proraté:</span>
                                <span class="font-medium ml-1">{{ formatMoney(calculatedBrutProrata) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">CNAS:</span>
                                <span class="font-medium text-red-600 ml-1">-{{ formatMoney(calculatedCNAS) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Pénalités:</span>
                                <span class="font-medium text-red-600 ml-1">-{{ formatMoney(totalPenalty) }}</span>
                            </div>
                            <div class="border-l pl-4">
                                <span class="font-bold text-green-600">Net à payer: {{ formatMoney(calculatedNetAPayer) }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ includedWorkingDays }}/{{ workingDaysInMonth }} jours ({{ Math.round(calculatedRatio * 100) }}%)</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="close" class="btn btn-secondary">Annuler</button>
                        <button @click="save" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving">Enregistrement...</span>
                            <span v-else>Enregistrer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Clock, X } from 'lucide-vue-next';
import { formatMoney } from '@/utils/formatters';

const props = defineProps({
    show: Boolean,
    fiche: Object,
    pointages: Array,
    periode: String,
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const allDays = ref([]);

// Generate all days of the month
const generateMonthDays = (year, month, pointages) => {
    const days = [];
    const daysInMonth = new Date(year, month, 0).getDate();
    
    // Create a map of existing pointages by date
    const pointageMap = {};
    (pointages || []).forEach(p => {
        const dateStr = p.date_pointage.substring(0, 10);
        pointageMap[dateStr] = p;
    });
    
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month - 1, day);
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = date.getDay();
        // In Algeria, weekends are Friday (5) and Saturday (6)
        const isWeekend = dayOfWeek === 5 || dayOfWeek === 6;
        
        const existingPointage = pointageMap[dateStr];
        const hasPointage = !!existingPointage;
        
        days.push({
            date: dateStr,
            dayOfMonth: day,
            dayOfWeek: dayOfWeek,
            isWeekend: isWeekend,
            label: date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' }),
            id: existingPointage?.id || null,
            statut: existingPointage?.statut || (isWeekend ? 'absent' : 'absent'),
            heure_entree: existingPointage?.heure_entree ? existingPointage.heure_entree.substring(11, 16) : '',
            heure_sortie: existingPointage?.heure_sortie ? existingPointage.heure_sortie.substring(11, 16) : '',
            heures_travaillees: existingPointage?.heures_travaillees || 0,
            isLate: existingPointage?.statut === 'retard' || false,
            penalty: 0,
            included: !isWeekend && hasPointage,
        });
    }
    
    return days;
};

watch([() => props.show, () => props.fiche, () => props.pointages], ([showVal, ficheVal, pointagesVal]) => {
    if (showVal && ficheVal) {
        allDays.value = generateMonthDays(
            parseInt(ficheVal.annee), 
            parseInt(ficheVal.mois), 
            pointagesVal || []
        );
    }
}, { immediate: true });

const totalDaysCount = computed(() => allDays.value.length);

const presentCount = computed(() => 
    allDays.value.filter(d => d.statut === 'present' && !d.isWeekend).length
);

const lateCount = computed(() => 
    allDays.value.filter(d => d.isLate).length
);

const excludedCount = computed(() => 
    allDays.value.filter(d => !d.included && !d.isWeekend).length
);

const includedCount = computed(() => 
    allDays.value.filter(d => d.included).length
);

const allIncluded = computed(() => 
    allDays.value.filter(d => !d.isWeekend).every(d => d.included)
);

const totalPenalty = computed(() => 
    allDays.value
        .filter(d => d.included)
        .reduce((sum, d) => sum + (parseFloat(d.penalty) || 0), 0)
);

// Calculate ratio based on included working days
const workingDaysInMonth = computed(() => 
    allDays.value.filter(d => !d.isWeekend).length
);

const includedWorkingDays = computed(() => 
    allDays.value.filter(d => !d.isWeekend && d.included && d.statut !== 'absent').length
);

const calculatedRatio = computed(() => {
    const total = workingDaysInMonth.value || 22;
    return includedWorkingDays.value / total;
});

// Calculate prorated salary with CNAS/IRG (matching backend logic)
const calculatedBrutProrata = computed(() => {
    if (!props.fiche) return 0;
    const salaireBase = parseFloat(props.fiche.salaire_base) || 0;
    const primes = (parseFloat(props.fiche.prime_rendement) || 0) + 
                   (parseFloat(props.fiche.prime_transport) || 0) +
                   (parseFloat(props.fiche.autres_primes) || 0);
    return (salaireBase + primes) * calculatedRatio.value;
});

const calculatedCNAS = computed(() => calculatedBrutProrata.value * 0.09);

const calculatedNetProrata = computed(() => {
    // Simplified: brut proraté - CNAS (9%) - IRG estimate (~10% for simplicity)
    const brutProrata = calculatedBrutProrata.value;
    const cnas = calculatedCNAS.value;
    const imposable = brutProrata - cnas;
    // Simplified IRG estimate (actual calculation is complex)
    const irg = imposable > 30000 ? (imposable - 20000) * 0.23 * 0.6 : 0;
    return brutProrata - cnas - irg;
});

const calculatedNetAPayer = computed(() => {
    return Math.round((calculatedNetProrata.value - totalPenalty.value) * 100) / 100;
});

const formatHours = (minutes) => {
    const m = parseInt(minutes) || 0;
    const h = Math.floor(m / 60);
    const mins = m % 60;
    return h > 0 ? `${h}h ${mins}min` : `${mins}min`;
};

const getDayRowClass = (day) => {
    if (day.isWeekend) return 'bg-orange-100/50';
    if (!day.included) return 'bg-gray-100 opacity-60';
    if (day.isLate) return 'bg-yellow-50';
    if (day.statut === 'present') return 'bg-green-50';
    if (day.statut === 'absent') return 'bg-red-50';
    if (day.statut === 'conge' || day.statut === 'mission') return 'bg-blue-50';
    if (day.statut === 'maladie') return 'bg-purple-50';
    return '';
};

const onDayChange = (index) => {
    const day = allDays.value[index];
    if (day.statut === 'present') {
        day.isLate = false;
        day.penalty = 0;
    }
};

const onLateChange = (index) => {
    const day = allDays.value[index];
    if (!day.isLate) {
        day.penalty = 0;
    }
};

const onTimeChange = (index) => {
    const d = allDays.value[index];
    if (d.heure_entree && d.heure_sortie) {
        const [eh, em] = d.heure_entree.split(':').map(Number);
        const [sh, sm] = d.heure_sortie.split(':').map(Number);
        let minutes = (sh * 60 + sm) - (eh * 60 + em);
        if (minutes < 0) minutes += 24 * 60;
        d.heures_travaillees = (minutes / 60).toFixed(2);
    }
};

const toggleAllLate = (e) => {
    const checked = e.target.checked;
    allDays.value.forEach(d => {
        if (!d.isWeekend) {
            d.isLate = checked;
            if (!checked) d.penalty = 0;
        }
    });
};

const toggleAllIncluded = (e) => {
    const checked = e.target.checked;
    allDays.value.forEach(d => {
        if (!d.isWeekend) {
            d.included = checked;
        }
    });
};

const close = () => emit('close');

const save = () => {
    saving.value = true;
    
    // Send all days (including weekends if they have data)
    const daysToSave = allDays.value.map(d => ({
        id: d.id,
        date: d.date,
        statut: d.isLate ? 'retard' : d.statut,
        heure_entree: d.heure_entree,
        heure_sortie: d.heure_sortie,
        is_late: d.isLate,
        penalty: parseFloat(d.penalty) || 0,
        included: d.included,
    }));
    
    router.post(`/fiches-paie/${props.fiche.id}/update-retards`, {
        days: daysToSave,
        total_penalty: totalPenalty.value,
        net_a_payer: calculatedNetAPayer.value,
        jours_travailles: includedWorkingDays.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            saving.value = false;
            emit('saved');
            close();
        },
        onError: () => {
            saving.value = false;
        },
    });
};
</script>
