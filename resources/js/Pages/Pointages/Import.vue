<template>
    <Head title="Importer Pointages" />
    
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Importer les pointages</h2>
                <p class="text-sm text-gray-500">Importez les données depuis un fichier Excel de la pointeuse</p>
            </div>
            <Link href="/pointages" class="btn btn-secondary flex items-center gap-2">
                <ArrowLeft class="w-4 h-4" />
                Retour
            </Link>
        </div>

        <!-- Instructions -->
        <div class="card bg-blue-50 border-blue-200">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                <Info class="w-5 h-5" />
                Format du fichier attendu
            </h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p>Le fichier Excel doit contenir les colonnes suivantes:</p>
                <table class="w-full mt-2 text-left">
                    <thead class="bg-blue-100">
                        <tr>
                            <th class="px-3 py-2 rounded-l">Colonne</th>
                            <th class="px-3 py-2">Contenu</th>
                            <th class="px-3 py-2 rounded-r">Exemple</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="px-3 py-1">A</td><td>Numéro</td><td>1</td></tr>
                        <tr><td class="px-3 py-1 font-bold">B</td><td class="font-bold">AC-No. (Code employé)</td><td class="font-bold">01</td></tr>
                        <tr><td class="px-3 py-1">C</td><td>Nom employé</td><td>NABIL BBS</td></tr>
                        <tr><td class="px-3 py-1">D</td><td>Date/Heure</td><td>01/11/2025 08:30</td></tr>
                        <tr><td class="px-3 py-1">E</td><td>Type</td><td>C/In ou C/Out</td></tr>
                    </tbody>
                </table>
            </div>
            <a href="/pointages/import/template" class="inline-flex items-center gap-2 mt-4 text-blue-600 hover:text-blue-800 font-medium">
                <Download class="w-4 h-4" />
                Télécharger le modèle Excel
            </a>
        </div>

        <!-- Auto-creation notice -->
        <div class="card bg-green-50 border-green-200">
            <h3 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
                <UserPlus class="w-5 h-5" />
                Création automatique des employés
            </h3>
            <p class="text-sm text-green-700">
                Si un <strong>AC-No.</strong> (code employé) n'existe pas dans la base de données, 
                l'employé sera <strong>créé automatiquement</strong> avec son nom. 
                Les autres informations (salaire, téléphone, etc.) pourront être complétées plus tard.
            </p>
        </div>

        <!-- Upload Form -->
        <div class="card">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Dropzone -->
                <div 
                    @dragover.prevent="isDragging = true"
                    @dragleave="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="[
                        'border-2 border-dashed rounded-xl p-8 text-center transition-colors',
                        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400',
                        form.file ? 'border-green-500 bg-green-50' : ''
                    ]"
                >
                    <input 
                        type="file" 
                        ref="fileInput"
                        @change="handleFileSelect"
                        accept=".xlsx,.xls,.csv"
                        class="hidden"
                    />
                    
                    <div v-if="!form.file" class="space-y-3">
                        <FileSpreadsheet class="w-12 h-12 mx-auto text-gray-400" />
                        <div>
                            <button type="button" @click="$refs.fileInput.click()" class="text-blue-600 font-medium hover:text-blue-800">
                                Choisir un fichier
                            </button>
                            <span class="text-gray-500"> ou glisser-déposer ici</span>
                        </div>
                        <p class="text-sm text-gray-400">Formats acceptés: .xlsx, .xls, .csv (max 10 Mo)</p>
                    </div>

                    <div v-else class="flex items-center justify-center gap-4">
                        <FileSpreadsheet class="w-10 h-10 text-green-600" />
                        <div class="text-left">
                            <p class="font-medium text-gray-800">{{ form.file.name }}</p>
                            <p class="text-sm text-gray-500">{{ formatFileSize(form.file.size) }}</p>
                        </div>
                        <button type="button" @click="removeFile" class="text-red-500 hover:text-red-700">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="space-y-3">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" v-model="options.updateExisting" class="w-4 h-4 text-blue-600 rounded" />
                        <span class="text-sm text-gray-700">Mettre à jour les pointages existants</span>
                    </label>
                </div>

                <!-- Error -->
                <div v-if="form.errors.file" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
                    {{ form.errors.file }}
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link href="/pointages" class="btn btn-secondary">Annuler</Link>
                    <button 
                        type="submit" 
                        :disabled="!form.file || form.processing"
                        class="btn btn-primary flex items-center gap-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        <Upload v-else class="w-4 h-4" />
                        Importer les pointages
                    </button>
                </div>
            </form>
        </div>

        <!-- Employés existants -->
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Employés existants ({{ employes.length }})</h3>
            <p class="text-sm text-gray-500 mb-3">
                Les employés avec ces codes (AC-No.) seront mis à jour. Les nouveaux codes seront créés automatiquement.
            </p>
            <div class="flex flex-wrap gap-2" v-if="employes.length > 0">
                <span 
                    v-for="emp in employes" 
                    :key="emp.id"
                    class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
                >
                    <span class="font-medium text-blue-600">{{ emp.matricule }}</span> - {{ emp.prenom }} {{ emp.nom }}
                </span>
            </div>
            <p v-else class="text-sm text-gray-400 italic">Aucun employé enregistré. Ils seront créés automatiquement lors de l'import.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft, Info, Download, FileSpreadsheet, X, Upload, Loader2, UserPlus } from 'lucide-vue-next';

const props = defineProps({
    employes: Array,
});

const isDragging = ref(false);
const fileInput = ref(null);
const options = reactive({
    updateExisting: true,
});

const form = useForm({
    file: null,
});

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.file = file;
    }
};

const handleDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
        form.file = file;
    }
};

const removeFile = () => {
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
};

const submit = () => {
    form.post('/pointages/import', {
        forceFormData: true,
    });
};
</script>
