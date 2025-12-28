<template>
    <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestion des utilisateurs</h1>
                    <p class="text-gray-500 mt-1">Gérez les comptes utilisateurs de l'application</p>
                </div>
                <Link href="/users/create" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <UserPlus class="w-5 h-5" />
                    <span>Nouvel utilisateur</span>
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Rôle</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'" 
                                    class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ user.role === 'admin' ? 'Administrateur' : 'Utilisateur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" 
                                    class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ user.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 text-sm">{{ user.created_at }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="`/users/${user.id}/edit`" 
                                        class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(user)" 
                                        class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="flex justify-center gap-1">
                <Link v-for="link in users.links" :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 rounded-lg text-sm',
                        link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'utilisateur</h3>
                <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer {{ userToDelete?.name }} ?</p>
                <div class="flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Annuler
                    </button>
                    <button @click="deleteUser" class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { UserPlus, Pencil, Trash2 } from 'lucide-vue-next';

defineProps({
    users: Object,
});

const showDeleteModal = ref(false);
const userToDelete = ref(null);

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    router.delete(`/users/${userToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        }
    });
};
</script>
