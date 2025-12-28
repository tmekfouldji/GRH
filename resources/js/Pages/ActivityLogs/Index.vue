<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Journal d'activité</h1>
                <p class="text-gray-500 mt-1">Historique des actions effectuées dans l'application</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[150px]">
                        <select v-model="filterAction" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Toutes les actions</option>
                            <option v-for="action in actions" :key="action" :value="action">{{ getActionLabel(action) }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <select v-model="filterModelType" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Tous les types</option>
                            <option v-for="type in modelTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <input type="date" v-model="filterDate" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
                    </div>
                    <button v-if="hasFilters" @click="clearFilters"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date/Heure</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Détails</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ log.created_at }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 text-xs font-semibold">{{ log.user_name?.charAt(0) || 'S' }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ log.user_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="getActionClass(log.action)" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ getActionLabel(log.action) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-md truncate">{{ log.description }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ log.model_type || '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button v-if="log.old_values || log.new_values" @click="showDetails(log)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                    <Eye class="w-4 h-4" />
                                </button>
                                <span v-else class="text-gray-300">-</span>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucune activité enregistrée</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links && logs.links.length > 3" class="flex justify-center gap-1">
                <Link v-for="link in logs.links" :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 rounded-lg text-sm',
                        link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedLog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Détails de l'action</h3>
                    <button @click="selectedLog = null" class="p-1 text-gray-400 hover:text-gray-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh] space-y-4">
                    <div v-if="selectedLog.old_values">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Anciennes valeurs</h4>
                        <pre class="bg-red-50 text-red-800 p-3 rounded-lg text-sm overflow-x-auto">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
                    </div>
                    <div v-if="selectedLog.new_values">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Nouvelles valeurs</h4>
                        <pre class="bg-green-50 text-green-800 p-3 rounded-lg text-sm overflow-x-auto">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
                    </div>
                    <div class="text-sm text-gray-500">
                        <p><strong>IP:</strong> {{ selectedLog.ip_address }}</p>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Eye, X } from 'lucide-vue-next';

const props = defineProps({
    logs: Object,
    actions: Array,
    modelTypes: Array,
    filters: Object,
});

const filterAction = ref(props.filters?.action || '');
const filterModelType = ref(props.filters?.model_type || '');
const filterDate = ref(props.filters?.date || '');
const selectedLog = ref(null);

const hasFilters = computed(() => filterAction.value || filterModelType.value || filterDate.value);

const applyFilters = () => {
    router.get('/activity-logs', {
        action: filterAction.value || undefined,
        model_type: filterModelType.value || undefined,
        date: filterDate.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    filterAction.value = '';
    filterModelType.value = '';
    filterDate.value = '';
    router.get('/activity-logs');
};

const showDetails = (log) => {
    selectedLog.value = log;
};

const getActionLabel = (action) => ({
    login: 'Connexion',
    logout: 'Déconnexion',
    create: 'Création',
    update: 'Modification',
    delete: 'Suppression',
}[action] || action);

const getActionClass = (action) => ({
    login: 'bg-green-100 text-green-700',
    logout: 'bg-gray-100 text-gray-700',
    create: 'bg-blue-100 text-blue-700',
    update: 'bg-yellow-100 text-yellow-700',
    delete: 'bg-red-100 text-red-700',
}[action] || 'bg-gray-100 text-gray-700');
</script>
