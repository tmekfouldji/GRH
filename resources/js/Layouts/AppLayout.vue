<template>
    <div class="flex h-screen overflow-hidden bg-gray-100">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'" 
            class="bg-gradient-to-b from-blue-800 to-blue-900 text-white transition-all duration-300 flex flex-col"
        >
            <!-- Logo -->
            <div class="p-4 border-b border-blue-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <Users class="w-6 h-6 text-blue-800" />
                    </div>
                    <span v-show="sidebarOpen" class="text-xl font-bold">GRH Textile</span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <Link 
                    href="/"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <LayoutDashboard class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Tableau de bord</span>
                </Link>
                
                <!-- Section Personnel -->
                <div v-show="sidebarOpen" class="pt-4 pb-1">
                    <span class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Personnel</span>
                </div>
                <Link 
                    href="/employes"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/employes') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <UserCircle class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Employés</span>
                </Link>
                <Link 
                    href="/conges"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/conges') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <CalendarDays class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Congés</span>
                </Link>
                
                <!-- Section Temps -->
                <div v-show="sidebarOpen" class="pt-4 pb-1">
                    <span class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Temps</span>
                </div>
                <Link 
                    href="/pointages"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/pointages') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <Clock class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Pointages</span>
                </Link>
                
                <!-- Section Paie -->
                <div v-show="sidebarOpen" class="pt-4 pb-1">
                    <span class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Paie</span>
                </div>
                <Link 
                    href="/paies-mensuelles"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/paies-mensuelles') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <Wallet class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Paies Mensuelles</span>
                </Link>
                <Link 
                    href="/fiches-paie"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/fiches-paie') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <FileText class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Fiches de Paie</span>
                </Link>
                
                <!-- Section Outils -->
                <div v-show="sidebarOpen" class="pt-4 pb-1">
                    <span class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Outils</span>
                </div>
                <Link 
                    href="/pointages/rapport/journalier"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/pointages/rapport') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <BarChart3 class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Rapports</span>
                </Link>
                <Link 
                    href="/guide-salaire"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/guide-salaire') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <BookOpen class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Guide Salaire</span>
                </Link>
            </nav>
            
            <!-- Toggle Button -->
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="p-4 border-t border-blue-700 hover:bg-blue-700/50 transition-colors"
            >
                <ChevronLeft v-if="sidebarOpen" class="w-5 h-5 mx-auto" />
                <ChevronRight v-else class="w-5 h-5 mx-auto" />
            </button>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $page.props.title || 'Tableau de bord' }}</h1>
                        <p class="text-sm text-gray-500">{{ $page.props.subtitle || 'Système de gestion des ressources humaines' }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ currentDate }}</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <User class="w-5 h-5 text-blue-600" />
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <CheckCircle class="w-5 h-5 mr-2" />
                        {{ $page.props.flash.success }}
                    </div>
                </div>
                
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <AlertCircle class="w-5 h-5 mr-2" />
                        {{ $page.props.flash.error }}
                    </div>
                </div>
                
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Users, LayoutDashboard, UserCircle, Clock, CalendarDays, 
    FileText, BarChart3, ChevronLeft, ChevronRight, User,
    CheckCircle, AlertCircle, BookOpen, Wallet
} from 'lucide-vue-next';

const sidebarOpen = ref(true);
const page = usePage();

const currentDate = computed(() => {
    return new Date().toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const isActive = (path) => {
    const currentPath = page.url;
    if (path === '/') {
        return currentPath === '/';
    }
    return currentPath.startsWith(path);
};
</script>
