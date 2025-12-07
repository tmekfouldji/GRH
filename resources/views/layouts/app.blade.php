<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion des Employés') - GRH</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" 
               class="bg-gradient-to-b from-blue-800 to-blue-900 text-white transition-all duration-300 flex flex-col">
            
            <!-- Logo -->
            <div class="p-4 border-b border-blue-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6 text-blue-800"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-xl font-bold">GRH Textile</span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Tableau de bord</span>
                </a>
                
                <a href="{{ route('employes.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('employes.*') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="user-circle" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Employés</span>
                </a>
                
                <a href="{{ route('pointages.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('pointages.*') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Pointages</span>
                </a>
                
                <a href="{{ route('conges.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('conges.*') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Congés</span>
                </a>
                
                <a href="{{ route('fiches-paie.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('fiches-paie.*') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Fiches de paie</span>
                </a>
                
                <a href="{{ route('pointages.rapport') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('pointages.rapport') ? 'bg-blue-700' : 'hover:bg-blue-700/50' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span x-show="sidebarOpen">Rapports</span>
                </a>
            </nav>
            
            <!-- Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="p-4 border-t border-blue-700 hover:bg-blue-700/50 transition-colors">
                <i data-lucide="chevrons-left" x-show="sidebarOpen" class="w-5 h-5 mx-auto"></i>
                <i data-lucide="chevrons-right" x-show="!sidebarOpen" class="w-5 h-5 mx-auto"></i>
            </button>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Tableau de bord')</h1>
                        <p class="text-sm text-gray-500">@yield('subtitle', 'Système de gestion des ressources humaines')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                
                <!-- Flash Messages -->
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-700 hover:text-green-900">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif
                
                @if(session('error'))
                <div x-data="{ show: true }" x-show="show"
                     class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
                        {{ session('error') }}
                    </div>
                    <button @click="show = false" class="text-red-700 hover:text-red-900">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif
                
                @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>
                        <strong>Veuillez corriger les erreurs suivantes:</strong>
                    </div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    
    @stack('scripts')
</body>
</html>
