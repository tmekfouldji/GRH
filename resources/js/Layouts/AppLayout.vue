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
                    <span v-show="sidebarOpen" class="text-xl font-bold">Talentee</span>
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
                        isActiveExact('/pointages') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
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
                <Link 
                    href="/simulateur-salaire"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/simulateur-salaire') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <Calculator class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Simulateur</span>
                </Link>
                <Link 
                    href="/simulateur-inverse"
                    :class="[
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                        isActive('/simulateur-inverse') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                    ]"
                >
                    <ArrowUpDown class="w-5 h-5 flex-shrink-0" />
                    <span v-show="sidebarOpen">Simulateur Inversé</span>
                </Link>

                <!-- Section Admin (visible only to admins) -->
                <template v-if="$page.props.auth?.user?.is_admin">
                    <div v-show="sidebarOpen" class="pt-4 pb-1">
                        <span class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Administration</span>
                    </div>
                    <Link 
                        href="/users"
                        :class="[
                            'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                            isActive('/users') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                        ]"
                    >
                        <UserCog class="w-5 h-5 flex-shrink-0" />
                        <span v-show="sidebarOpen">Utilisateurs</span>
                    </Link>
                    <Link 
                        href="/activity-logs"
                        :class="[
                            'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors',
                            isActive('/activity-logs') ? 'bg-blue-700' : 'hover:bg-blue-700/50'
                        ]"
                    >
                        <ScrollText class="w-5 h-5 flex-shrink-0" />
                        <span v-show="sidebarOpen">Journal</span>
                    </Link>
                </template>
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
                        <h1 class="text-2xl font-bold text-gray-800">{{ pageTitle }}</h1>
                        <p class="text-sm text-gray-500">{{ pageSubtitle }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ currentDate }}</span>
                        
                        <!-- User Menu -->
                        <div class="relative" ref="userMenuRef">
                            <button @click="showUserMenu = !showUserMenu" 
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-sm">{{ userInitial }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $page.props.auth?.user?.name }}</span>
                                <ChevronDown class="w-4 h-4 text-gray-400" />
                            </button>
                            
                            <div v-if="showUserMenu" 
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ $page.props.auth?.user?.name }}</p>
                                    <p class="text-xs text-gray-500">{{ $page.props.auth?.user?.email }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full"
                                        :class="$page.props.auth?.user?.is_admin ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'">
                                        {{ $page.props.auth?.user?.is_admin ? 'Administrateur' : 'Utilisateur' }}
                                    </span>
                                </div>
                                <button @click="logout" 
                                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                    <LogOut class="w-4 h-4" />
                                    Déconnexion
                                </button>
                            </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
    Users, LayoutDashboard, UserCircle, Clock, CalendarDays, 
    FileText, BarChart3, ChevronLeft, ChevronRight, User,
    CheckCircle, AlertCircle, BookOpen, Wallet, UserCog, ScrollText,
    ChevronDown, LogOut, Calculator, ArrowUpDown
} from 'lucide-vue-next';

const sidebarOpen = ref(true);
const showUserMenu = ref(false);
const userMenuRef = ref(null);
const page = usePage();

const currentDate = computed(() => {
    return new Date().toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const userInitial = computed(() => {
    return page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U';
});

const pageTitles = {
    '/': { title: 'Tableau de bord', subtitle: 'Vue d\'ensemble de l\'activité' },
    '/employes': { title: 'Employés', subtitle: 'Gestion du personnel' },
    '/pointages': { title: 'Pointages', subtitle: 'Suivi des présences' },
    '/pointages/rapport': { title: 'Rapports', subtitle: 'Rapports de présences' },
    '/conges': { title: 'Congés', subtitle: 'Gestion des congés' },
    '/fiches-paie': { title: 'Fiches de paie', subtitle: 'Bulletins de salaire' },
    '/paies-mensuelles': { title: 'Paies mensuelles', subtitle: 'Gestion des paies' },
    '/users': { title: 'Utilisateurs', subtitle: 'Gestion des comptes' },
    '/activity-logs': { title: 'Journal d\'activité', subtitle: 'Historique des actions' },
    '/guide-salaire': { title: 'Guide salaire', subtitle: 'Informations sur le système de paie' },
    '/simulateur-salaire': { title: 'Simulateur de salaire', subtitle: 'Calcul dynamique des salaires' },
    '/simulateur-inverse': { title: 'Simulateur inversé', subtitle: 'Calcul du brut à partir du net' },
};

const pageTitle = computed(() => {
    const path = page.url.split('?')[0];
    for (const [route, data] of Object.entries(pageTitles)) {
        if (path === route || (route !== '/' && path.startsWith(route))) {
            return data.title;
        }
    }
    return 'Talentee';
});

const pageSubtitle = computed(() => {
    const path = page.url.split('?')[0];
    for (const [route, data] of Object.entries(pageTitles)) {
        if (path === route || (route !== '/' && path.startsWith(route))) {
            return data.subtitle;
        }
    }
    return 'Système de gestion des ressources humaines';
});

const isActive = (path) => {
    const currentPath = page.url.split('?')[0];
    if (path === '/') {
        return currentPath === '/';
    }
    return currentPath.startsWith(path);
};

const isActiveExact = (path) => {
    const currentPath = page.url.split('?')[0];
    // For /pointages, match /pointages and /pointages/* but NOT /pointages/rapport/*
    if (path === '/pointages') {
        return currentPath === '/pointages' || 
               (currentPath.startsWith('/pointages/') && !currentPath.startsWith('/pointages/rapport'));
    }
    return currentPath === path || currentPath.startsWith(path + '/');
};

const logout = () => {
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
