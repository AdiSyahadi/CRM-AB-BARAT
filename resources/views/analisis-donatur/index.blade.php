<x-layouts.app active="dashboard" title="Analisis Donatur Dashboard" xData="dashboardApp()" :chartjs="true">

@push('styles')
<style>
        /* Liquid/Blob Background */
        .blob-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: blob-float 8s ease-in-out infinite;
        }
        .blob-1 { width: 400px; height: 400px; background: linear-gradient(135deg, #10B981 0%, #34D399 100%); top: -100px; right: -100px; }
        .blob-2 { width: 300px; height: 300px; background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); bottom: 100px; left: -150px; animation-delay: 2s; }
        .blob-3 { width: 200px; height: 200px; background: linear-gradient(135deg, #6EE7B7 0%, #10B981 100%); top: 50%; right: 10%; animation-delay: 4s; }
        
        @keyframes blob-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }
        
        /* Liquid Icon */
        .liquid-icon { position: relative; display: flex; align-items: center; justify-content: center; }
        .liquid-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(52, 211, 153, 0.3) 100%);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: liquid-morph 8s ease-in-out infinite;
        }
        @keyframes liquid-morph {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
        }
        
        /* Animations */
        .chart-container { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
        .chart-container:nth-child(1) { animation-delay: 0.1s; }
        .chart-container:nth-child(2) { animation-delay: 0.2s; }
        .chart-container:nth-child(3) { animation-delay: 0.3s; }
        .chart-container:nth-child(4) { animation-delay: 0.4s; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        
        /* Chart loading overlay */
        .chart-loading { position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; border-radius: 1rem; z-index: 10; }
        
        /* Page-specific Loading Animations */
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .loading-dots {
            display: flex;
            gap: 0.5rem;
        }
        .loading-dots span {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            animation: dotBounce 1.4s ease-in-out infinite;
        }
        .loading-dots span:nth-child(1) { animation-delay: 0s; }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotBounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        
        /* Header Scroll Behavior */
        .main-header {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, opacity;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .main-header.header-hidden {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        .filter-bar {
            transition: top 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: top;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .filter-bar.filter-sticky {
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.08);
        }
        
        /* Ensure no gap between header and content */
        @media (max-width: 767px) {
            .main-header { min-height: 48px; }
        }
        @media (min-width: 768px) {
            .main-header { min-height: 56px; }
        }
        
        /* Hide scrollbar for filter pills */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Tab Panel for Mobile */
        .tab-btn { transition: all 0.2s ease; }
        .tab-btn.active { background: #10B981; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* ===== Date Range Picker ===== */
        .drp-trigger {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px; background: #F9FAFB; border: 1px solid #E5E7EB;
            border-radius: 8px; cursor: pointer; transition: all 0.15s;
            font-size: 13px; color: #374151; width: 100%;
        }
        .drp-trigger:hover { border-color: #A7F3D0; background: #ECFDF5; }
        .drp-trigger.drp-active { border-color: #10B981; background: #ECFDF5; box-shadow: 0 0 0 2px rgba(16,185,129,0.15); }
        .drp-popover {
            position: absolute; top: calc(100% + 6px); left: 0; z-index: 60;
            background: white; border-radius: 16px; border: 1px solid #E5E7EB;
            box-shadow: 0 20px 40px -8px rgba(0,0,0,0.12); padding: 16px; width: 320px;
        }
        .drp-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .drp-nav button {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            background: #F3F4F6; cursor: pointer; display: flex; align-items: center;
            justify-content: center; color: #6B7280; transition: all 0.15s;
        }
        .drp-nav button:hover { background: #E5E7EB; color: #374151; }
        .drp-weekdays {
            display: grid; grid-template-columns: repeat(7, 1fr);
            text-align: center; font-size: 11px; color: #9CA3AF;
            font-weight: 600; margin-bottom: 4px; text-transform: uppercase;
        }
        .drp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px 0; }
        .drp-cell {
            height: 36px; display: flex; align-items: center; justify-content: center;
            font-size: 13px; cursor: pointer; position: relative; transition: all 0.08s;
            border-radius: 0;
        }
        .drp-cell:hover:not(.drp-disabled):not(.drp-empty) { background: #D1FAE5; }
        .drp-cell.drp-today { font-weight: 700; color: #059669; }
        .drp-cell.drp-disabled { opacity: 0.25; cursor: default; pointer-events: none; }
        .drp-cell.drp-empty { cursor: default; }
        .drp-cell.drp-in-range { background: #ECFDF5; }
        .drp-cell.drp-start { background: #10B981; color: white; border-radius: 50% 0 0 50%; font-weight: 600; }
        .drp-cell.drp-end { background: #10B981; color: white; border-radius: 0 50% 50% 0; font-weight: 600; }
        .drp-cell.drp-start.drp-end { border-radius: 50%; }
        .drp-cell.drp-hover-end { background: #A7F3D0; border-radius: 0 50% 50% 0; }
        .drp-presets { display: flex; flex-wrap: wrap; gap: 6px; padding-top: 12px; border-top: 1px solid #F3F4F6; margin-top: 12px; }
        .drp-preset-btn {
            padding: 5px 12px; border-radius: 20px; border: 1px solid #E5E7EB;
            background: white; font-size: 12px; color: #6B7280; cursor: pointer;
            transition: all 0.15s; font-weight: 500;
        }
        .drp-preset-btn:hover { border-color: #10B981; color: #059669; background: #ECFDF5; }
        @media (max-width: 640px) {
            .drp-popover { width: calc(100vw - 32px); left: 50%; transform: translateX(-50%); }
        }
        
    </style>
@endpush
@push('before-sidebar')
    <!-- Initial Loading Modal - Friendly Version -->
    <div x-show="initialLoading.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-md z-[9999] flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 max-w-md mx-4 text-center transform">
            <!-- Animated Icon -->
            <div class="relative w-24 h-24 mx-auto mb-6">
                <div class="absolute inset-0 bg-primary-100 rounded-full animate-ping opacity-30"></div>
                <div class="relative w-24 h-24 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center shadow-lg shadow-primary-500/40">
                    <i class="bi bi-bar-chart-line-fill text-white text-4xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Analisis</h2>
            <p class="text-gray-500 mb-6">Menyiapkan data untuk Anda...</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500 ease-out"
                     :style="'width: ' + initialLoading.progress + '%'"></div>
            </div>
            
            <!-- Loading Status -->
            <p class="text-sm text-primary-600 font-medium mb-6" x-text="initialLoading.status">Memuat statistik...</p>
            
            <!-- Fun Tips / Quotes - rotating -->
            <div class="bg-gradient-to-r from-primary-50 to-green-50 rounded-2xl p-4 border border-primary-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="text-primary-600 text-lg" :class="'bi ' + initialLoading.tips[initialLoading.currentTip].icon"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-semibold text-primary-700 mb-1">Tahukah Anda?</p>
                        <p class="text-sm text-gray-600 leading-relaxed" x-text="initialLoading.tips[initialLoading.currentTip].text">
                            Loading tips...
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Dots animation -->
            <div class="flex justify-center gap-1.5 mt-6">
                <template x-for="i in 3" :key="i">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce"
                         :style="'animation-delay: ' + (i * 0.15) + 's'"></div>
                </template>
            </div>
        </div>
    </div>
@endpush


    <!-- Modal Loading Overlay -->
    <div x-show="isLoading" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="loading-overlay">
        <div class="loading-modal">
            <div class="loading-spinner"></div>
            <div class="text-center">
                <p class="text-gray-800 font-semibold text-lg" x-text="loadingMessage"></p>
                <p class="text-gray-500 text-sm mt-1">Mohon tunggu sebentar...</p>
            </div>
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    
    <!-- Background Blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="blob-shape blob-1"></div>
        <div class="blob-shape blob-2"></div>
        <div class="blob-shape blob-3"></div>
    </div>
    
    <!-- Main Container -->
    <div class="relative z-10" x-data="{ 
            headerHidden: false, 
            lastScrollY: 0,
            scrollThreshold: 10,
            headerHeight: window.innerWidth >= 768 ? 56 : 48
         }" 
         x-init="
            window.addEventListener('resize', () => {
                headerHeight = window.innerWidth >= 768 ? 56 : 48;
            });
         "
         @scroll.window.throttle.50ms="
            const currentScrollY = window.scrollY;
            const delta = currentScrollY - lastScrollY;
            
            // Only trigger if scroll delta exceeds threshold (smoother)
            if (Math.abs(delta) > scrollThreshold) {
                if (currentScrollY > headerHeight && delta > 0) {
                    headerHidden = true;
                } else if (delta < 0 || currentScrollY <= headerHeight) {
                    headerHidden = false;
                }
                lastScrollY = currentScrollY;
            }
         ">
        
        <!-- Main Header (Scroll Away) -->
        <header class="main-header bg-white/95 backdrop-blur-lg border-b border-primary-100 z-50" 
                :class="{ 'header-hidden': headerHidden }"
                style="position: sticky; top: 0;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Desktop Main Header -->
                <div class="hidden md:flex items-center justify-between h-14">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-gray-800">Analisis Donatur</h1>
                            <p class="text-[10px] text-gray-500">Dashboard Insight & Analytics</p>
                        </div>
                    </div>
                    
                    <!-- User Menu Desktop -->
                    <div class="flex items-center gap-3">
                        <a href="/admin" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 hover:border-primary-300 hover:bg-primary-50 text-gray-700 text-sm font-medium rounded-xl transition-all">
                            <i class="bi bi-grid-fill"></i>
                            <span>Admin</span>
                        </a>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" 
                                    class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all border border-gray-200">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="text-left hidden lg:block">
                                    <p class="text-sm font-medium text-gray-800 truncate max-w-[120px]">{{ Auth::user()->name ?? 'User' }}</p>
                                    <p class="text-[10px] text-gray-500 truncate max-w-[120px]">{{ Auth::user()->email ?? '' }}</p>
                                </div>
                                <i class="bi bi-chevron-down text-gray-400 text-xs transition-transform" :class="{ 'rotate-180': userMenuOpen }"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95" 
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100" 
                                 x-transition:leave-end="opacity-0 scale-95"
                                 @click.away="userMenuOpen = false"
                                 class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                                
                                <!-- User Info -->
                                <div class="p-4 bg-gradient-to-br from-primary-50 to-white border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-primary-500/30">
                                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate">{{ Auth::user()->name ?? 'User' }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="p-2">
                                    <a href="/admin" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                        <i class="bi bi-grid text-gray-400"></i>
                                        <span>Admin Panel</span>
                                    </a>
                                    <a href="{{ route('coming-soon', ['menu' => 'Kelola Akun']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                        <i class="bi bi-person-gear text-gray-400"></i>
                                        <span>Kelola Akun</span>
                                    </a>
                                </div>
                                
                                <!-- Logout -->
                                <div class="p-2 border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <span>Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Main Header -->
                <div class="md:hidden flex items-center justify-between h-12">
                    <div class="flex items-center gap-2">
                        <!-- Hamburger Menu Button -->
                        <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center shadow-md shadow-primary-500/30">
                            <i class="bi bi-people-fill text-white text-sm"></i>
                        </div>
                        <h1 class="text-sm font-bold text-gray-800">Analisis Donatur</h1>
                    </div>
                    
                    <!-- User Menu Mobile -->
                    <div class="relative" x-data="{ userMenuMobile: false }">
                        <button @click="userMenuMobile = !userMenuMobile" 
                                class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </button>
                        
                        <!-- Dropdown Menu Mobile -->
                        <div x-show="userMenuMobile" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" 
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" 
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.away="userMenuMobile = false"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                            
                            <!-- User Info -->
                            <div class="p-4 bg-gradient-to-br from-primary-50 to-white border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-primary-500/30">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 truncate">{{ Auth::user()->name ?? 'User' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="p-2">
                                <a href="/admin" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                    <i class="bi bi-grid text-gray-400"></i>
                                    <span>Admin Panel</span>
                                </a>
                            </div>
                            
                            <!-- Logout -->
                            <div class="p-2 border-t border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Filter Bar (Sticky) -->
        <div class="filter-bar bg-white/95 backdrop-blur-lg border-b border-gray-200 sticky z-40"
             :class="{ 'filter-sticky': lastScrollY > 60 }"
             :style="{ top: headerHidden ? '0px' : headerHeight + 'px' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Desktop Filter Bar -->
                <div class="hidden md:flex items-center justify-between h-12 gap-4">
                    <div class="flex items-center gap-3">
                        <!-- Tahun Filter -->
                        <div class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-lg px-3 py-1.5 transition-colors">
                            <i class="bi bi-calendar3 text-primary-500 text-sm"></i>
                            <select x-model="filters.tahun" @change="applyHeaderFilters()" class="bg-transparent border-none text-gray-700 text-sm font-medium focus:outline-none cursor-pointer">
                                @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                                <option value="all">Semua Tahun</option>
                            </select>
                        </div>
                        
                        <!-- Tim Filter -->
                        <div class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-lg px-3 py-1.5 transition-colors">
                            <i class="bi bi-diagram-3 text-primary-500 text-sm"></i>
                            <select x-model="filters.tim" @change="applyHeaderFilters()" class="bg-transparent border-none text-gray-700 text-sm font-medium focus:outline-none cursor-pointer">
                                <option value="all">Semua Tim</option>
                                @foreach($timList as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- CS Filter -->
                        <div class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-lg px-3 py-1.5 transition-colors">
                            <i class="bi bi-person-badge text-primary-500 text-sm"></i>
                            <select x-model="filters.cs" @change="applyHeaderFilters()" class="bg-transparent border-none text-gray-700 text-sm font-medium focus:outline-none cursor-pointer max-w-[150px]">
                                <option value="all">Semua CS</option>
                                @foreach($csList as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Kategori Donasi Filter -->
                        <div class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-lg px-3 py-1.5 transition-colors">
                            <i class="bi bi-tag text-primary-500 text-sm"></i>
                            <select x-model="filters.kategori" @change="applyHeaderFilters()" class="bg-transparent border-none text-gray-700 text-sm font-medium focus:outline-none cursor-pointer max-w-[150px]">
                                <option value="all">Semua Kategori</option>
                                @foreach($kategoriList as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Active Filters Indicator -->
                        <template x-if="filters.tim !== 'all' || filters.cs !== 'all' || filters.kategori !== 'all'">
                            <button @click="filters.tim = 'all'; filters.cs = 'all'; filters.kategori = 'all'; applyHeaderFilters()" 
                                    class="flex items-center gap-1 text-xs text-red-500 hover:text-red-600 bg-red-50 px-2 py-1 rounded-full">
                                <i class="bi bi-x-circle"></i>
                                <span>Reset Filter</span>
                            </button>
                        </template>
                    </div>
                    
                    <!-- Export Button Desktop with Dropdown -->
                    <div class="relative" x-data="{ exportOpenDesktop: false }">
                        <button @click="exportOpenDesktop = !exportOpenDesktop" @click.away="exportOpenDesktop = false"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-lg transition-all shadow-md shadow-primary-500/30">
                            <i class="bi bi-download"></i>
                            <span>Export</span>
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu Desktop -->
                        <div x-show="exportOpenDesktop" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                            
                            <a :href="exportUrl" 
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="bi bi-file-earmark-excel text-green-600 text-lg"></i>
                                <div>
                                    <span class="font-medium">Export Excel</span>
                                    <p class="text-[10px] text-gray-400">Raw Data Donatur</p>
                                </div>
                            </a>
                            
                            <a :href="exportPdfUrl" 
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                                <i class="bi bi-file-earmark-pdf text-red-500 text-lg"></i>
                                <div>
                                    <span class="font-medium">Export PDF</span>
                                    <p class="text-[10px] text-gray-400">Laporan Rangkuman</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Filter Bar -->
                <div class="md:hidden py-2">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                        <!-- Tahun Pill -->
                        <div class="flex-shrink-0 flex items-center gap-1 bg-primary-50 border border-primary-200 rounded-full px-3 py-1.5">
                            <i class="bi bi-calendar3 text-primary-600 text-xs"></i>
                            <select x-model="filters.tahun" @change="applyHeaderFilters()" class="bg-transparent border-none text-primary-700 text-xs font-semibold focus:outline-none cursor-pointer">
                                @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                                <option value="all">All</option>
                            </select>
                        </div>
                        
                        <!-- Tim Pill -->
                        <div class="flex-shrink-0 flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5"
                             :class="filters.tim !== 'all' ? 'bg-primary-50 border-primary-200' : ''">
                            <i class="bi bi-diagram-3 text-xs" :class="filters.tim !== 'all' ? 'text-primary-600' : 'text-gray-500'"></i>
                            <select x-model="filters.tim" @change="applyHeaderFilters()" class="bg-transparent border-none text-xs font-medium focus:outline-none cursor-pointer max-w-[80px]"
                                    :class="filters.tim !== 'all' ? 'text-primary-700' : 'text-gray-600'">
                                <option value="all">Tim</option>
                                @foreach($timList as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- CS Pill -->
                        <div class="flex-shrink-0 flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5"
                             :class="filters.cs !== 'all' ? 'bg-primary-50 border-primary-200' : ''">
                            <i class="bi bi-person-badge text-xs" :class="filters.cs !== 'all' ? 'text-primary-600' : 'text-gray-500'"></i>
                            <select x-model="filters.cs" @change="applyHeaderFilters()" class="bg-transparent border-none text-xs font-medium focus:outline-none cursor-pointer max-w-[100px]"
                                    :class="filters.cs !== 'all' ? 'text-primary-700' : 'text-gray-600'">
                                <option value="all">CS</option>
                                @foreach($csList as $c)
                                <option value="{{ $c }}">{{ Str::limit($c, 12) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Kategori Pill -->
                        <div class="flex-shrink-0 flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5"
                             :class="filters.kategori !== 'all' ? 'bg-primary-50 border-primary-200' : ''">
                            <i class="bi bi-tag text-xs" :class="filters.kategori !== 'all' ? 'text-primary-600' : 'text-gray-500'"></i>
                            <select x-model="filters.kategori" @change="applyHeaderFilters()" class="bg-transparent border-none text-xs font-medium focus:outline-none cursor-pointer max-w-[100px]"
                                    :class="filters.kategori !== 'all' ? 'text-primary-700' : 'text-gray-600'">
                                <option value="all">Kategori</option>
                                @foreach($kategoriList as $k)
                                <option value="{{ $k }}">{{ Str::limit($k, 12) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Export Dropdown -->
                        <div class="flex-shrink-0 relative" x-data="{ exportOpen: false }">
                            <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                                    class="flex items-center gap-1 bg-primary-500 text-white rounded-full px-3 py-1.5 hover:bg-primary-600 transition-colors">
                                <i class="bi bi-download text-xs"></i>
                                <span class="text-xs font-medium">Export</span>
                                <i class="bi bi-chevron-down text-[10px] ml-0.5"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="exportOpen" x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                                
                                <a :href="exportUrl" 
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="bi bi-file-earmark-excel text-green-600"></i>
                                    <span>Export Excel</span>
                                    <span class="ml-auto text-[10px] text-gray-400">Raw Data</span>
                                </a>
                                
                                <a :href="exportPdfUrl" 
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                                    <span>Export PDF</span>
                                    <span class="ml-auto text-[10px] text-gray-400">Laporan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            
            <!-- Section 1: Hero Summary -->
            <section class="mb-6">
                <!-- Desktop: 4 columns grid -->
                <div class="hidden md:grid md:grid-cols-4 gap-4">
                    <!-- Hero Card: Total Perolehan (spans 2 cols) -->
                    <div class="col-span-2 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 rounded-2xl shadow-xl shadow-primary-500/30 p-6 text-white relative overflow-hidden">
                        <!-- Background decoration -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-cash-stack text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-primary-100 text-sm">Total Perolehan</p>
                                        <p class="text-primary-200 text-xs" x-text="filters.tahun === 'all' ? 'Semua Tahun' : 'Tahun ' + filters.tahun"></p>
                                    </div>
                                </div>
                                <!-- Growth Badge -->
                                <div class="flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-300"
                                     :class="stats.growth_rate >= 0 ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50' : 'bg-red-500 text-white shadow-lg shadow-red-500/50'">
                                    <i class="bi" :class="stats.growth_rate >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'"></i>
                                    <span x-text="(stats.growth_rate >= 0 ? '+' : '') + stats.growth_rate + '%'"></span>
                                    <span class="text-xs opacity-90">YoY</span>
                                </div>
                            </div>
                            <h2 class="text-4xl font-bold mb-2" x-text="'Rp ' + formatCompact(stats.total_perolehan)"></h2>
                            <p class="text-primary-200 text-sm">
                                vs tahun lalu: <span x-text="'Rp ' + formatCompact(stats.perolehan_tahun_lalu || 0)"></span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Avg per Transaksi -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-receipt text-blue-600"></i>
                            </div>
                            <p class="text-sm text-gray-500">Avg/Transaksi</p>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + formatCompact(stats.avg_per_transaksi || 0)"></h3>
                        <p class="text-xs text-gray-400 mt-1"><span x-text="formatNumber(stats.total_transaksi || 0)"></span> transaksi</p>
                    </div>
                    
                    <!-- Avg per Donatur -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-person-heart text-purple-600"></i>
                            </div>
                            <p class="text-sm text-gray-500">Avg/Donatur</p>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + formatCompact(stats.avg_donasi || 0)"></h3>
                        <p class="text-xs text-gray-400 mt-1">Repeat rate: <span x-text="(stats.repeat_donor_rate || 0) + '%'"></span></p>
                    </div>
                </div>
                
                <!-- Mobile: Stack layout -->
                <div class="md:hidden space-y-3">
                    <!-- Hero Card Mobile -->
                    <div class="bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 rounded-2xl shadow-lg p-5 text-white">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-cash-stack text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-primary-100 text-xs">Total Perolehan</p>
                                    <p class="text-primary-200 text-[10px]" x-text="filters.tahun === 'all' ? 'Semua Tahun' : filters.tahun"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold transition-all duration-300"
                                 :class="stats.growth_rate >= 0 ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50' : 'bg-red-500 text-white shadow-lg shadow-red-500/50'">
                                <i class="bi text-[10px]" :class="stats.growth_rate >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'"></i>
                                <span x-text="(stats.growth_rate >= 0 ? '+' : '') + stats.growth_rate + '%'"></span>
                            </div>
                        </div>
                        <h2 class="text-3xl font-bold" x-text="'Rp ' + formatCompact(stats.total_perolehan)"></h2>
                    </div>
                    
                    <!-- Avg Cards Mobile (2 columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="bi bi-receipt text-blue-500"></i>
                                <span class="text-xs text-gray-500">Avg/Trans</span>
                            </div>
                            <p class="text-lg font-bold text-gray-800" x-text="'Rp ' + formatCompact(stats.avg_per_transaksi || 0)"></p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="bi bi-person-heart text-purple-500"></i>
                                <span class="text-xs text-gray-500">Avg/Donor</span>
                            </div>
                            <p class="text-lg font-bold text-gray-800" x-text="'Rp ' + formatCompact(stats.avg_donasi || 0)"></p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 2: Donatur Stats Cards -->
            <section class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="bi bi-people text-primary-500 mr-1"></i>Statistik Donatur
                    </h3>
                    <p class="text-xs text-gray-400 hidden sm:block"><i class="bi bi-hand-index-thumb mr-1"></i>Klik untuk filter</p>
                </div>
                
                <!-- Desktop: 5 columns -->
                <div class="hidden sm:grid sm:grid-cols-5 gap-3">
                    <!-- Total Donatur -->
                    <div @click="filterByStatus('all')" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:border-primary-300" :class="filters.status === 'all' ? 'ring-2 ring-primary-500 border-primary-300' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-people-fill text-primary-500"></i>
                            <span class="text-xs text-gray-500">Total</span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800" x-text="formatNumber(stats.total_donatur)"></h4>
                        <p class="text-[10px] text-gray-400">Donatur Unik</p>
                    </div>
                    
                    <!-- Aktif Bulan Ini -->
                    <div @click="filterByStatus('aktif')" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:border-green-300" :class="filters.status === 'aktif' ? 'ring-2 ring-green-500 border-green-300' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-green-500"></i>
                            <span class="text-xs text-gray-500">Aktif</span>
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full pulse-dot"></span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_aktif_bulan_ini)"></h4>
                        <p class="text-[10px] text-gray-400">Bulan Ini</p>
                    </div>
                    
                    <!-- Donatur Baru -->
                    <div @click="filterByStatus('baru')" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:border-blue-300" :class="filters.status === 'baru' ? 'ring-2 ring-blue-500 border-blue-300' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-person-plus-fill text-blue-500"></i>
                            <span class="text-xs text-gray-500">Baru</span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_baru)"></h4>
                        <p class="text-[10px] text-gray-400" x-text="'Tahun ' + filters.tahun"></p>
                    </div>
                    
                    <!-- Donatur Hilang -->
                    <div @click="filterByStatus('hilang')" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:border-red-300" :class="filters.status === 'hilang' ? 'ring-2 ring-red-500 border-red-300' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-person-dash-fill text-red-500"></i>
                            <span class="text-xs text-gray-500">Hilang</span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_hilang)"></h4>
                        <p class="text-[10px] text-gray-400">vs Tahun Lalu</p>
                    </div>
                    
                    <!-- Tidak Aktif -->
                    <div @click="filterByStatus('tidak_aktif')" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:border-gray-400" :class="filters.status === 'tidak_aktif' ? 'ring-2 ring-gray-500 border-gray-400' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-clock-history text-gray-500"></i>
                            <span class="text-xs text-gray-500">&gt;30hr</span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800" x-text="formatNumber(stats.tidak_aktif_30hari)"></h4>
                        <p class="text-[10px] text-gray-400">Tidak Aktif</p>
                    </div>
                </div>
                
                <!-- Mobile: 2-2-1 Grid -->
                <div class="sm:hidden grid grid-cols-2 gap-2">
                    <!-- Row 1 -->
                    <div @click="filterByStatus('all')" class="stat-card bg-white rounded-xl shadow-sm border p-3 cursor-pointer" :class="filters.status === 'all' ? 'ring-2 ring-primary-500 border-primary-300' : 'border-gray-100'">
                        <div class="flex items-center gap-1.5 mb-1">
                            <i class="bi bi-people-fill text-primary-500 text-sm"></i>
                            <span class="text-[10px] text-gray-500">Total</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800" x-text="formatNumber(stats.total_donatur)"></h4>
                    </div>
                    <div @click="filterByStatus('aktif')" class="stat-card bg-white rounded-xl shadow-sm border p-3 cursor-pointer" :class="filters.status === 'aktif' ? 'ring-2 ring-green-500 border-green-300' : 'border-gray-100'">
                        <div class="flex items-center gap-1.5 mb-1">
                            <i class="bi bi-check-circle-fill text-green-500 text-sm"></i>
                            <span class="text-[10px] text-gray-500">Aktif</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800" x-text="formatNumber(stats.donatur_aktif_bulan_ini)"></h4>
                    </div>
                    
                    <!-- Row 2 -->
                    <div @click="filterByStatus('baru')" class="stat-card bg-white rounded-xl shadow-sm border p-3 cursor-pointer" :class="filters.status === 'baru' ? 'ring-2 ring-blue-500 border-blue-300' : 'border-gray-100'">
                        <div class="flex items-center gap-1.5 mb-1">
                            <i class="bi bi-person-plus-fill text-blue-500 text-sm"></i>
                            <span class="text-[10px] text-gray-500">Baru</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800" x-text="formatNumber(stats.donatur_baru)"></h4>
                    </div>
                    <div @click="filterByStatus('hilang')" class="stat-card bg-white rounded-xl shadow-sm border p-3 cursor-pointer" :class="filters.status === 'hilang' ? 'ring-2 ring-red-500 border-red-300' : 'border-gray-100'">
                        <div class="flex items-center gap-1.5 mb-1">
                            <i class="bi bi-person-dash-fill text-red-500 text-sm"></i>
                            <span class="text-[10px] text-gray-500">Hilang</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800" x-text="formatNumber(stats.donatur_hilang)"></h4>
                    </div>
                    
                    <!-- Row 3: Full width -->
                    <div @click="filterByStatus('tidak_aktif')" class="col-span-2 stat-card bg-white rounded-xl shadow-sm border p-3 cursor-pointer" :class="filters.status === 'tidak_aktif' ? 'ring-2 ring-gray-500 border-gray-400' : 'border-gray-100'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <i class="bi bi-clock-history text-gray-500 text-sm"></i>
                                <span class="text-[10px] text-gray-500">Tidak Aktif &gt;30 hari</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800" x-text="formatNumber(stats.tidak_aktif_30hari)"></h4>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 3: Trend Chart (Full Width dengan YoY) -->
            <section class="mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 relative">
                    <div x-show="chartLoading" class="chart-loading">
                        <div class="spinner"></div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-gray-800">
                                <i class="bi bi-graph-up text-primary-500 mr-2"></i>Trend Perolehan Bulanan
                            </h3>
                            <p class="text-xs md:text-sm text-gray-500">Perbandingan <span x-text="filters.tahun === 'all' ? new Date().getFullYear() : filters.tahun" class="font-medium text-primary-600"></span> vs <span x-text="filters.tahun === 'all' ? new Date().getFullYear() - 1 : parseInt(filters.tahun) - 1" class="font-medium text-gray-500"></span></p>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-primary-500 rounded"></span>
                                <span class="text-gray-600" x-text="filters.tahun === 'all' ? new Date().getFullYear() : filters.tahun"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 bg-gray-300 rounded"></span>
                                <span class="text-gray-500" x-text="filters.tahun === 'all' ? new Date().getFullYear() - 1 : parseInt(filters.tahun) - 1"></span>
                            </div>
                        </div>
                    </div>
                    <div class="h-64 md:h-80">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </section>
            
            <!-- Section 3b: Trend Perolehan Hari Jumat -->
            <section class="mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 relative">
                    <div x-show="chartLoading" class="chart-loading">
                        <div class="spinner"></div>
                    </div>
                    
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-gray-800">
                                <i class="bi bi-calendar-event text-amber-500 mr-2"></i>Trend Perolehan Hari Jumat
                            </h3>
                            <p class="text-xs md:text-sm text-gray-500">
                                Rata-rata perolehan per Jumat • 
                                <span x-text="charts.trend_jumat?.tahun_ini_label" class="font-medium text-amber-600"></span> vs 
                                <span x-text="charts.trend_jumat?.tahun_lalu_label" class="font-medium text-gray-500"></span>
                            </p>
                        </div>
                        
                        <!-- Summary Stats -->
                        <div class="flex items-center gap-3">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-center">
                                <p class="text-[10px] text-amber-600 font-medium" x-text="charts.trend_jumat?.tahun_ini_label"></p>
                                <p class="text-sm font-bold text-amber-700" x-text="'Rp ' + formatCompact(charts.trend_jumat?.summary?.rata_rata_tahun_ini || 0) + '/Jumat'"></p>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-center">
                                <p class="text-[10px] text-gray-500 font-medium" x-text="charts.trend_jumat?.tahun_lalu_label"></p>
                                <p class="text-sm font-bold text-gray-600" x-text="'Rp ' + formatCompact(charts.trend_jumat?.summary?.rata_rata_tahun_lalu || 0) + '/Jumat'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="flex items-center gap-4 text-xs mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 bg-amber-500 rounded"></span>
                            <span class="text-gray-600" x-text="charts.trend_jumat?.tahun_ini_label"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 bg-gray-300 rounded"></span>
                            <span class="text-gray-500" x-text="charts.trend_jumat?.tahun_lalu_label"></span>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <div class="h-56 md:h-72">
                        <canvas id="trendJumatChart"></canvas>
                    </div>
                    
                    <!-- Note -->
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-info-circle-fill text-amber-500 mt-0.5"></i>
                            <div class="text-xs text-amber-800">
                                <strong>Catatan:</strong> Chart menampilkan <strong>rata-rata perolehan per hari Jumat</strong> dalam setiap bulan. 
                                Ini memberikan perbandingan yang fair karena jumlah Jumat tiap bulan berbeda (4-5 Jumat).
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 3c: Performa per Hari dalam Seminggu (YoY) -->
            <section class="mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 relative">
                    <div x-show="chartLoading" class="chart-loading">
                        <div class="spinner"></div>
                    </div>
                    
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-gray-800">
                                <i class="bi bi-calendar-week text-purple-500 mr-2"></i>Performa Perolehan Harian
                            </h3>
                            <p class="text-xs md:text-sm text-gray-500">
                                Rata-rata perolehan berdasarkan hari: 
                                <span class="font-medium text-purple-600" x-text="charts.performa_harian?.tahun_ini_label"></span> vs 
                                <span class="font-medium text-gray-400" x-text="charts.performa_harian?.tahun_lalu_label"></span>
                            </p>
                        </div>
                        
                        <!-- Legend -->
                        <div class="flex items-center gap-3 text-xs">
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                                <span class="text-gray-600" x-text="charts.performa_harian?.tahun_ini_label"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                <span class="text-gray-400" x-text="charts.performa_harian?.tahun_lalu_label"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <div class="h-64 md:h-80">
                        <canvas id="performaHarianChart"></canvas>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <!-- Best Day Tahun Ini -->
                            <div class="bg-purple-50 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="bi bi-trophy-fill text-purple-500 text-sm"></i>
                                    <span class="text-[10px] text-purple-600 font-medium" x-text="'Terbaik ' + charts.performa_harian?.tahun_ini_label"></span>
                                </div>
                                <p class="text-sm font-bold text-purple-700" x-text="charts.performa_harian?.summary?.best_day_tahun_ini || '-'"></p>
                                <p class="text-[10px] text-purple-500">
                                    <span x-text="'Rp ' + formatCompact(charts.performa_harian?.summary?.best_day_avg_tahun_ini || 0)"></span>/hari
                                </p>
                            </div>
                            
                            <!-- Best Day Tahun Lalu -->
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="bi bi-trophy text-gray-400 text-sm"></i>
                                    <span class="text-[10px] text-gray-500 font-medium" x-text="'Terbaik ' + charts.performa_harian?.tahun_lalu_label"></span>
                                </div>
                                <p class="text-sm font-bold text-gray-600" x-text="charts.performa_harian?.summary?.best_day_tahun_lalu || '-'"></p>
                                <p class="text-[10px] text-gray-400">
                                    <span x-text="'Rp ' + formatCompact(charts.performa_harian?.summary?.best_day_avg_tahun_lalu || 0)"></span>/hari
                                </p>
                            </div>
                            
                            <!-- Rata-rata Tahun Ini -->
                            <div class="bg-purple-50 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="bi bi-bar-chart-fill text-purple-500 text-sm"></i>
                                    <span class="text-[10px] text-purple-600 font-medium" x-text="'Rata² ' + charts.performa_harian?.tahun_ini_label"></span>
                                </div>
                                <p class="text-sm font-bold text-purple-700" x-text="'Rp ' + formatCompact(charts.performa_harian?.summary?.rata_rata_tahun_ini || 0)"></p>
                                <p class="text-[10px] text-purple-500">per hari</p>
                            </div>
                            
                            <!-- Rata-rata Tahun Lalu -->
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="bi bi-bar-chart text-gray-400 text-sm"></i>
                                    <span class="text-[10px] text-gray-500 font-medium" x-text="'Rata² ' + charts.performa_harian?.tahun_lalu_label"></span>
                                </div>
                                <p class="text-sm font-bold text-gray-600" x-text="'Rp ' + formatCompact(charts.performa_harian?.summary?.rata_rata_tahun_lalu || 0)"></p>
                                <p class="text-[10px] text-gray-400">per hari</p>
                            </div>
                        </div>
                        
                        <!-- Note -->
                        <div class="mt-3 text-[10px] text-gray-400 flex items-start gap-1">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Rata-rata dihitung dari total perolehan dibagi jumlah hari tersebut dalam setahun. Contoh: Total perolehan Jumat / 52 hari Jumat.</span>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 4: Ranking Tim & CS -->
            <section class="mb-6">
                <!-- Desktop: 2 columns -->
                <div class="hidden md:grid md:grid-cols-2 gap-4">
                    <!-- Ranking Tim -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">
                                    <i class="bi bi-diagram-3 text-primary-500 mr-2"></i>Ranking Tim
                                </h3>
                                <p class="text-xs text-gray-500">Perolehan per tim</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="timChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Ranking CS -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">
                                    <i class="bi bi-person-badge text-primary-500 mr-2"></i>Ranking CS
                                </h3>
                                <p class="text-xs text-gray-500">Top 10 CS by perolehan</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="csChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile: Tab Panel -->
                <div class="md:hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ activeRankingTab: 'tim' }">
                    <!-- Tab Buttons -->
                    <div class="flex border-b border-gray-100">
                        <button @click="activeRankingTab = 'tim'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeRankingTab === 'tim' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-diagram-3 mr-1"></i>Tim
                        </button>
                        <button @click="activeRankingTab = 'cs'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeRankingTab === 'cs' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-person-badge mr-1"></i>CS
                        </button>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-4 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div x-show="activeRankingTab === 'tim'" class="h-64">
                            <canvas id="timChartMobile"></canvas>
                        </div>
                        <div x-show="activeRankingTab === 'cs'" class="h-64">
                            <canvas id="csChartMobile"></canvas>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 5: Top Donatur & Retention -->
            <section class="mb-6">
                <!-- Desktop: 2 columns -->
                <div class="hidden md:grid md:grid-cols-2 gap-4">
                    <!-- Top 10 Donatur -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">
                                    <i class="bi bi-trophy text-primary-500 mr-2"></i>Top 10 Donatur
                                </h3>
                                <p class="text-xs text-gray-500">Donatur dengan total terbesar</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="topDonaturChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Retention Rate -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-base font-semibold text-gray-800">
                                <i class="bi bi-arrow-repeat text-primary-500 mr-2"></i>Retention Rate
                            </h3>
                            <p class="text-xs text-gray-500">Tingkat retensi donatur</p>
                        </div>
                        <div class="flex flex-col items-center justify-center h-56">
                            <div class="relative w-40 h-40">
                                <canvas id="retentionChart"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-primary-600" x-text="charts.retention_rate + '%'"></span>
                                    <span class="text-xs text-gray-500">Retention</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-6 text-xs">
                                <div class="text-center">
                                    <span class="text-base font-semibold text-gray-800" x-text="formatNumber(charts.donatur_retained)"></span>
                                    <p class="text-gray-500">Retained</p>
                                </div>
                                <div class="text-center">
                                    <span class="text-base font-semibold text-gray-800" x-text="formatNumber(charts.donatur_tahun_lalu)"></span>
                                    <p class="text-gray-500">Thn Lalu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile: Tab Panel -->
                <div class="md:hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ activeAnalysisTab: 'donatur' }">
                    <!-- Tab Buttons -->
                    <div class="flex border-b border-gray-100">
                        <button @click="activeAnalysisTab = 'donatur'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeAnalysisTab === 'donatur' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-trophy mr-1"></i>Top Donatur
                        </button>
                        <button @click="activeAnalysisTab = 'retention'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeAnalysisTab === 'retention' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-arrow-repeat mr-1"></i>Retention
                        </button>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-4 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div x-show="activeAnalysisTab === 'donatur'" class="h-64">
                            <canvas id="topDonaturChartMobile"></canvas>
                        </div>
                        <div x-show="activeAnalysisTab === 'retention'" class="flex flex-col items-center justify-center h-64">
                            <div class="relative w-36 h-36">
                                <canvas id="retentionChartMobile"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-bold text-primary-600" x-text="charts.retention_rate + '%'"></span>
                                    <span class="text-[10px] text-gray-500">Retention</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-6 text-xs">
                                <div class="text-center">
                                    <span class="text-sm font-semibold text-gray-800" x-text="formatNumber(charts.donatur_retained)"></span>
                                    <p class="text-gray-400">Retained</p>
                                </div>
                                <div class="text-center">
                                    <span class="text-sm font-semibold text-gray-800" x-text="formatNumber(charts.donatur_tahun_lalu)"></span>
                                    <p class="text-gray-400">Thn Lalu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section 6: Distribusi Nilai & Repeat Analysis -->
            <section class="mb-6">
                <!-- Desktop: 2 columns -->
                <div class="hidden md:grid md:grid-cols-2 gap-4">
                    <!-- Distribusi Nilai Transaksi -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">
                                    <i class="bi bi-cash-coin text-primary-500 mr-2"></i>Distribusi Nilai Transaksi
                                </h3>
                                <p class="text-xs text-gray-500">Berapa nilai donasi per transaksi?</p>
                            </div>
                            <!-- Rata-rata Badge -->
                            <div class="bg-primary-50 border border-primary-200 rounded-lg px-3 py-1.5">
                                <p class="text-[10px] text-primary-600 font-medium">Rata-rata</p>
                                <p class="text-sm font-bold text-primary-700" x-text="'Rp ' + formatCompact(charts.distribusi_nilai?.rata_rata || 0)"></p>
                            </div>
                        </div>
                        
                        <!-- Horizontal Bar Chart -->
                        <div class="space-y-3">
                            <template x-for="(item, idx) in (charts.distribusi_nilai?.data || [])" :key="idx">
                                <div class="group">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700" x-text="item.label"></span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500" x-text="formatNumber(item.count) + ' trx'"></span>
                                            <span class="text-sm font-bold min-w-[45px] text-right" 
                                                  :class="item.pct >= 25 ? 'text-primary-600' : 'text-gray-700'"
                                                  x-text="item.pct + '%'"></span>
                                        </div>
                                    </div>
                                    <div class="relative h-6 bg-gray-100 rounded-lg overflow-hidden">
                                        <div class="absolute inset-y-0 left-0 rounded-lg transition-all duration-500 bg-gradient-to-r from-primary-400 to-primary-500"
                                             :style="{ width: Math.max(item.pct, 2) + '%' }">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Summary -->
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>Total: <strong class="text-gray-700" x-text="formatNumber(charts.distribusi_nilai?.total_transaksi || 0)"></strong> transaksi</span>
                            <span>Nilai: <strong class="text-gray-700" x-text="'Rp ' + formatCompact(charts.distribusi_nilai?.total_nilai || 0)"></strong></span>
                        </div>
                    </div>
                    
                    <!-- Repeat vs One-time -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">
                                    <i class="bi bi-people text-primary-500 mr-2"></i>Repeat vs One-time
                                </h3>
                                <p class="text-xs text-gray-500">Donatur berulang vs sekali transaksi</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="repeatChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile: Tab Panel -->
                <div class="md:hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ activeSegmentTab: 'distribusi' }">
                    <!-- Tab Buttons -->
                    <div class="flex border-b border-gray-100">
                        <button @click="activeSegmentTab = 'distribusi'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeSegmentTab === 'distribusi' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-cash-coin mr-1"></i>Nilai Trx
                        </button>
                        <button @click="activeSegmentTab = 'repeat'" 
                                class="flex-1 py-3 text-sm font-medium transition-colors"
                                :class="activeSegmentTab === 'repeat' ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/50' : 'text-gray-500'">
                            <i class="bi bi-people mr-1"></i>Repeat
                        </button>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-4 relative">
                        <div x-show="chartLoading" class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                        
                        <!-- Distribusi Nilai Mobile -->
                        <div x-show="activeSegmentTab === 'distribusi'">
                            <!-- Rata-rata Badge Mobile -->
                            <div class="flex justify-center mb-4">
                                <div class="bg-primary-50 border border-primary-200 rounded-lg px-4 py-2 text-center">
                                    <p class="text-[10px] text-primary-600 font-medium">Rata-rata per Transaksi</p>
                                    <p class="text-lg font-bold text-primary-700" x-text="'Rp ' + formatCompact(charts.distribusi_nilai?.rata_rata || 0)"></p>
                                </div>
                            </div>
                            
                            <div class="space-y-2.5">
                                <template x-for="(item, idx) in (charts.distribusi_nilai?.data || [])" :key="idx">
                                    <div class="group">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-medium text-gray-700" x-text="item.label"></span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] text-gray-400" x-text="formatNumber(item.count) + ' trx'"></span>
                                                <span class="text-xs font-bold min-w-[35px] text-right" 
                                                      :class="item.pct >= 25 ? 'text-primary-600' : 'text-gray-700'"
                                                      x-text="item.pct + '%'"></span>
                                            </div>
                                        </div>
                                        <div class="relative h-5 bg-gray-100 rounded-lg overflow-hidden">
                                            <div class="absolute inset-y-0 left-0 rounded-lg transition-all duration-500 bg-gradient-to-r from-primary-400 to-primary-500"
                                                 :style="{ width: Math.max(item.pct, 2) + '%' }">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Summary Mobile -->
                            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                                <span>Total: <strong x-text="formatNumber(charts.distribusi_nilai?.total_transaksi || 0)"></strong> trx</span>
                                <span>Nilai: <strong x-text="'Rp ' + formatCompact(charts.distribusi_nilai?.total_nilai || 0)"></strong></span>
                            </div>
                        </div>
                        
                        <div x-show="activeSegmentTab === 'repeat'" class="h-64">
                            <canvas id="repeatChartMobile"></canvas>
                        </div>
                    </div>
                </div>
            </section>
                </div>
            </section>
            
            <!-- Data Table Section -->
            <section id="data-table-section" class="mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Table Header with Filters -->
                    <div class="px-4 md:px-6 py-4 border-b border-gray-100">
                        <div class="flex flex-col gap-4">
                            <!-- Title Row -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base md:text-lg font-semibold text-gray-800">
                                        <i class="bi bi-table text-primary-500 mr-2"></i>Data Donatur
                                    </h3>
                                    <p class="text-xs md:text-sm text-gray-500">
                                        <span x-text="formatNumber(table.total)">{{ number_format($donaturList->total()) }}</span> donatur ditemukan
                                        <!-- Active Filter Indicator -->
                                        <template x-if="hasActiveTableFilters()">
                                            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 bg-primary-100 text-primary-700 text-xs rounded-full">
                                                <i class="bi bi-funnel-fill text-[10px]"></i>
                                                <span x-text="countActiveFilters()"></span> filter aktif
                                            </span>
                                        </template>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Filters Row - Responsive -->
                            <div class="flex flex-col gap-2 sm:gap-3">
                                <!-- Status & CS Filter Row (sejajar di mobile) -->
                                <div class="flex gap-2">
                                    <!-- Status Filter - Realtime -->
                                    <select x-model="tableFilterInputs.status"
                                            @change="tableFilterInputs.from_date = ''; tableFilterInputs.to_date = ''; drp.startDate = null; drp.endDate = null; drp.step = 1; applyStatusFilter()"
                                            class="flex-1 px-3 py-2 bg-gray-50 border rounded-lg text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                            :class="tableFilterInputs.status !== 'all' ? 'border-primary-300 bg-primary-50' : 'border-gray-200'">
                                        <option value="all">Semua Status</option>
                                        <option value="aktif">🟢 Aktif</option>
                                        <option value="baru">✨ Baru</option>
                                        <option value="hilang">🔴 Hilang</option>
                                        <option value="tidak_aktif">⚪ Tidak Aktif</option>
                                    </select>
                                    
                                    <!-- CS Filter - Realtime -->
                                    <select x-model="tableFilterInputs.table_cs"
                                            @change="applyTableCsFilter()"
                                            class="flex-1 px-3 py-2 bg-gray-50 border rounded-lg text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                            :class="tableFilterInputs.table_cs !== 'all' ? 'border-primary-300 bg-primary-50' : 'border-gray-200'">
                                        <option value="all">Semua CS</option>
                                        @foreach($csList as $c)
                                            <option value="{{ $c }}">{{ Str::limit($c, 15) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Date Range Picker -->
                                <div x-show="tableFilterInputs.status === 'all'" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="relative">
                                    {{-- Trigger --}}
                                    <div @click="drp.open = !drp.open" 
                                         class="drp-trigger" :class="{ 'drp-active': drp.open || (tableFilterInputs.from_date && tableFilterInputs.to_date) }">
                                        <i class="bi bi-calendar-range text-primary-500"></i>
                                        <span x-text="drpDisplayText()" class="flex-1 truncate"></span>
                                        <template x-if="tableFilterInputs.from_date || tableFilterInputs.to_date">
                                            <button @click.stop="drpClear()" class="text-gray-400 hover:text-red-500 transition">
                                                <i class="bi bi-x-circle-fill text-sm"></i>
                                            </button>
                                        </template>
                                        <i class="bi text-gray-400 text-xs transition-transform" :class="drp.open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    </div>
                                    {{-- Calendar Popover --}}
                                    <div x-show="drp.open" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         @click.outside="drp.open = false"
                                         class="drp-popover">
                                        {{-- Navigation --}}
                                        <div class="drp-nav">
                                            <button @click="drpPrevMonth()" type="button"><i class="bi bi-chevron-left"></i></button>
                                            <span style="font-size:14px;font-weight:700;color:#1F2937" x-text="drpMonthYear()"></span>
                                            <button @click="drpNextMonth()" type="button"><i class="bi bi-chevron-right"></i></button>
                                        </div>
                                        {{-- Weekday Headers --}}
                                        <div class="drp-weekdays">
                                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                                        </div>
                                        {{-- Days Grid --}}
                                        <div class="drp-grid">
                                            <template x-for="cell in drpGetDays()" :key="cell.key">
                                                <div @click="drpSelectDate(cell)"
                                                     @mouseenter="drpHover(cell)"
                                                     :class="drpCellClass(cell)"
                                                     class="drp-cell"
                                                     x-text="cell.d || ''"></div>
                                            </template>
                                        </div>
                                        {{-- Quick Presets --}}
                                        <div class="drp-presets">
                                            <button type="button" @click="drpPreset('7d')" class="drp-preset-btn">7 Hari</button>
                                            <button type="button" @click="drpPreset('30d')" class="drp-preset-btn">30 Hari</button>
                                            <button type="button" @click="drpPreset('bulan')" class="drp-preset-btn">Bulan Ini</button>
                                            <button type="button" @click="drpPreset('3bulan')" class="drp-preset-btn">3 Bulan</button>
                                            <button type="button" @click="drpPreset('tahun')" class="drp-preset-btn">Tahun Ini</button>
                                        </div>
                                        {{-- Selection hint --}}
                                        <p style="font-size:11px;color:#9CA3AF;text-align:center;margin-top:8px" x-show="drp.step === 1">
                                            <i class="bi bi-hand-index"></i> Klik tanggal akhir
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Search Row -->
                                <div class="flex gap-2">
                                    <div class="relative flex-grow">
                                        <input type="text" x-model="tableFilterInputs.search"
                                               @keydown.enter="applySearchFilter()"
                                               placeholder="Cari nama/HP..."
                                               class="w-full pl-9 pr-3 py-2 bg-gray-50 border rounded-lg text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                                               :class="tableFilterInputs.search ? 'border-primary-300 bg-primary-50' : 'border-gray-200'">
                                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    </div>
                                    
                                    <!-- Search Button - Only for search text -->
                                    <button @click="applySearchFilter()" 
                                            x-show="tableFilterInputs.search !== filters.search"
                                            x-transition
                                            class="flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-lg transition-all shadow-sm hover:shadow animate-pulse ring-2 ring-primary-300">
                                        <i class="bi bi-search"></i>
                                        <span class="hidden sm:inline">Cari</span>
                                    </button>
                                    
                                    <!-- Reset Button - Only show when filters active -->
                                    <button @click="resetTableFilters()" 
                                            x-show="hasActiveTableFilters()"
                                            x-transition
                                            class="flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-lg transition-all border border-red-200" 
                                            title="Reset Semua Filter">
                                        <i class="bi bi-x-circle"></i>
                                        <span class="hidden sm:inline">Reset</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Active Filter Tags -->
                            <div x-show="hasActiveTableFilters()" x-transition class="flex flex-wrap gap-2">
                                <!-- Status Tag -->
                                <template x-if="filters.status !== 'all'">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-700 text-xs rounded-full">
                                        <span x-text="getStatusLabel(filters.status)"></span>
                                        <button @click="filters.status = 'all'; tableFilterInputs.status = 'all'; applyTableFilters()" class="hover:text-primary-900">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </span>
                                </template>
                                <!-- Date Range Tag -->
                                <template x-if="filters.from_date || filters.to_date">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-700 text-xs rounded-full">
                                        <i class="bi bi-calendar3 text-[10px]"></i>
                                        <span x-text="drpDisplayText()"></span>
                                        <button @click="drpClear()" class="hover:text-primary-900">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </span>
                                </template>
                                <!-- Search Tag -->
                                <template x-if="filters.search">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-700 text-xs rounded-full">
                                        <i class="bi bi-search text-[10px]"></i>
                                        "<span x-text="filters.search" class="max-w-[100px] truncate"></span>"
                                        <button @click="filters.search = ''; tableFilterInputs.search = ''; applyTableFilters()" class="hover:text-primary-900">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </span>
                                </template>
                                <!-- CS Filter Tag -->
                                <template x-if="tableFilterInputs.table_cs !== 'all'">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                        <i class="bi bi-person-badge text-[10px]"></i>
                                        <span x-text="tableFilterInputs.table_cs" class="max-w-[100px] truncate"></span>
                                        <button @click="tableFilterInputs.table_cs = 'all'; applyTableCsFilter()" class="hover:text-blue-900">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto relative">
                        <!-- Table Loading Overlay -->
                        <div x-show="tableLoading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10">
                            <div class="spinner"></div>
                        </div>
                        
                        <table class="w-full">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Donatur</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No HP</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama CS</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Donasi</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Trx</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Pertama</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Terakhir</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="donatur in table.data" :key="donatur.no_hp">
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 text-sm text-gray-500" x-text="donatur.no"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center text-white font-semibold text-xs" x-text="donatur.initial"></div>
                                                <p class="font-medium text-gray-800 truncate max-w-[180px]" x-text="donatur.nama_donatur"></p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 font-mono" x-text="donatur.no_hp"></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                                <i class="bi bi-person-badge"></i>
                                                <span x-text="donatur.nama_cs"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <span class="font-semibold text-primary-600" x-text="'Rp ' + formatNumber(donatur.total_donasi)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-7 h-7 bg-primary-50 text-primary-700 text-xs font-semibold rounded-md" x-text="donatur.jml_transaksi"></span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 text-center" x-text="donatur.first_donation"></td>
                                        <td class="px-4 py-3 text-xs text-gray-500 text-center" x-text="donatur.last_donation"></td>
                                        <td class="px-4 py-3 text-center">
                                            <a :href="donatur.wa_link" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-150" title="WhatsApp">
                                                <i class="bi bi-whatsapp text-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="table.data.length === 0 && !tableLoading">
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500 text-sm">Tidak ada data donatur</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View -->
                    <div class="md:hidden relative">
                        <!-- Loading Overlay -->
                        <div x-show="tableLoading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10">
                            <div class="spinner"></div>
                        </div>
                        
                        <!-- Cards Container -->
                        <div class="divide-y divide-gray-100">
                            <template x-for="donatur in table.data" :key="donatur.no_hp">
                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                    <!-- Card Header -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm" x-text="donatur.initial"></div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-800 truncate" x-text="donatur.nama_donatur"></p>
                                                <p class="text-xs text-gray-500 font-mono" x-text="donatur.no_hp"></p>
                                                <p class="text-xs text-blue-600 mt-0.5">
                                                    <i class="bi bi-person-badge mr-1"></i>
                                                    <span x-text="donatur.nama_cs"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <a :href="donatur.wa_link" target="_blank" 
                                           class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-colors" 
                                           title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    
                                    <!-- Card Stats -->
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-primary-50 rounded-lg py-2 px-1">
                                            <p class="text-xs text-primary-600 font-semibold" x-text="'Rp ' + formatCompact(donatur.total_donasi)"></p>
                                            <p class="text-[10px] text-gray-500">Total</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg py-2 px-1">
                                            <p class="text-xs text-gray-700 font-semibold" x-text="donatur.jml_transaksi + 'x'"></p>
                                            <p class="text-[10px] text-gray-500">Transaksi</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg py-2 px-1">
                                            <p class="text-xs text-gray-700 font-semibold" x-text="donatur.last_donation"></p>
                                            <p class="text-[10px] text-gray-500">Terakhir</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Empty State -->
                            <div x-show="table.data.length === 0 && !tableLoading" class="p-8 text-center">
                                <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 text-sm">Tidak ada data donatur</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div x-show="table.last_page > 1" class="px-4 md:px-6 py-4 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p class="text-xs sm:text-sm text-gray-500 order-2 sm:order-1">
                                <i class="bi bi-file-earmark-text mr-1"></i>
                                Hal <span x-text="table.current_page"></span>/<span x-text="table.last_page"></span>
                                <span class="hidden sm:inline">• <span x-text="formatNumber(table.total)"></span> data</span>
                            </p>
                            <div class="flex items-center gap-2 order-1 sm:order-2">
                                <!-- Previous Button -->
                                <button @click="goToPage(table.current_page - 1)" 
                                        :disabled="table.current_page <= 1"
                                        :class="table.current_page <= 1 ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-gray-700 bg-white border border-gray-200 hover:bg-gray-50'"
                                        class="px-3 py-2 text-xs sm:text-sm rounded-lg transition-colors">
                                    <i class="bi bi-chevron-left"></i>
                                    <span class="hidden sm:inline ml-1">Prev</span>
                                </button>
                                
                                <!-- Page Numbers - Desktop Only -->
                                <div class="hidden sm:flex items-center gap-1">
                                    <template x-for="page in getPageNumbers()" :key="page">
                                        <button @click="page !== '...' && goToPage(page)"
                                                :class="page === table.current_page ? 'bg-primary-500 text-white' : (page === '...' ? 'cursor-default text-gray-400' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50')"
                                                class="w-9 h-9 text-sm rounded-lg transition-colors"
                                                :disabled="page === '...'"
                                                x-text="page">
                                        </button>
                                    </template>
                                </div>
                                
                                <!-- Mobile Page Input -->
                                <div class="sm:hidden flex items-center gap-2">
                                    <input type="number" 
                                           :value="table.current_page" 
                                           @change="goToPage(parseInt($event.target.value))"
                                           :min="1" 
                                           :max="table.last_page"
                                           class="w-14 px-2 py-2 text-center text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                
                                <!-- Next Button -->
                                <button @click="goToPage(table.current_page + 1)" 
                                        :disabled="table.current_page >= table.last_page"
                                        :class="table.current_page >= table.last_page ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-white bg-primary-500 hover:bg-primary-600'"
                                        class="px-3 py-2 text-xs sm:text-sm rounded-lg transition-colors">
                                    <span class="hidden sm:inline mr-1">Next</span>
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </section>
            
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    <i class="bi bi-heart-fill text-primary-500 mx-1"></i>
                    LAZ AL BAHJAH WILAYAH BARAT &copy; {{ date('Y') }}
                </p>
            </div>
        </footer>
        
    </div>

@push('scripts')
    <script>
        // Initial data from backend
        const initialCharts = @json($charts);
        const initialStats = @json($stats);
        const initialTable = @json($initialTableData);
        
        // Chart instances - Desktop
        let trendChart, trendJumatChart, performaHarianChart, timChart, csChart, topDonaturChart, retentionChart, repeatChart;
        // Chart instances - Mobile
        let timChartMobile, csChartMobile, topDonaturChartMobile, retentionChartMobile, repeatChartMobile;
        
        // Month names
        const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Colors
        const colors = {
            primary: '#10B981',
            primaryLight: '#34D399',
            primaryDark: '#059669',
            gradient: ['#10B981', '#34D399', '#6EE7B7', '#A7F3D0', '#D1FAE5', '#ECFDF5']
        };
        
        // Alpine.js App
        function dashboardApp() {
            return {
                loading: false,
                chartLoading: false,
                tableLoading: false,
                isLoading: false,
                loadingMessage: 'Memuat data...',
                sidebarOpen: false,
                
                // Initial Loading State
                initialLoading: {
                    show: true,
                    progress: 0,
                    status: 'Memulai...',
                    currentTip: 0,
                    tips: [
                        { icon: 'bi-graph-up', text: 'Dashboard ini menampilkan analisis komprehensif perolehan donasi Anda.' },
                        { icon: 'bi-calendar-check', text: 'Gunakan filter tahun untuk membandingkan performa dengan tahun sebelumnya.' },
                        { icon: 'bi-bullseye', text: 'Pantau trend perolehan Jumat - hari terpenting untuk donasi!' },
                        { icon: 'bi-trophy', text: 'Lihat ranking Tim dan CS untuk mengetahui performa terbaik.' },
                        { icon: 'bi-arrow-repeat', text: 'Retention rate menunjukkan seberapa baik donatur kembali berdonasi.' },
                        { icon: 'bi-bar-chart-line', text: 'Distribusi nilai transaksi membantu memahami pola donasi.' },
                        { icon: 'bi-download', text: 'Export data ke Excel atau PDF untuk laporan dan presentasi.' },
                        { icon: 'bi-funnel', text: 'Filter berdasarkan Tim, CS, atau Kategori untuk analisis lebih spesifik.' },
                        { icon: 'bi-people', text: 'Klik stats card untuk langsung filter data di tabel donatur.' },
                        { icon: 'bi-lightning', text: 'Performa harian membantu menentukan hari terbaik untuk kampanye.' }
                    ]
                },
                
                filters: {
                    tahun: '{{ $tahun }}',
                    tim: '{{ $tim ?? "all" }}',
                    cs: '{{ $cs ?? "all" }}',
                    kategori: '{{ $kategori ?? "all" }}',
                    status: '{{ $status }}',
                    from_date: '{{ $fromDate }}',
                    to_date: '{{ $toDate }}',
                    search: '{{ $search }}'
                },
                // Separate inputs for table filters (before clicking "Cari")
                tableFilterInputs: {
                    status: '{{ $status }}',
                    from_date: '{{ $fromDate }}',
                    to_date: '{{ $toDate }}',
                    search: '{{ $search }}',
                    table_cs: '{{ $cs ?? "all" }}'
                },

                // Date Range Picker state
                drp: {
                    open: false,
                    month: new Date().getMonth(),
                    year: new Date().getFullYear(),
                    startDate: null,  // 'YYYY-MM-DD'
                    endDate: null,
                    hoverDate: null,
                    step: 0,  // 0=pick start, 1=pick end
                },
                stats: initialStats,
                charts: initialCharts,
                table: initialTable,
                
                get exportUrl() {
                    const params = new URLSearchParams(this.filters);
                    return '/analisis-donatur/export?' + params.toString();
                },
                
                get exportPdfUrl() {
                    const params = new URLSearchParams({
                        tahun: this.filters.tahun,
                        tim: this.filters.tim,
                        cs: this.filters.cs,
                        kategori: this.filters.kategori
                    });
                    return '/analisis-donatur/export-pdf?' + params.toString();
                },
                
                init() {
                    // Start tip rotation
                    this.startTipRotation();
                    
                    // Initialize date range picker from existing filter values
                    if (this.tableFilterInputs.from_date) {
                        this.drp.startDate = this.tableFilterInputs.from_date;
                        var sp = this.tableFilterInputs.from_date.split('-');
                        this.drp.month = parseInt(sp[1]) - 1;
                        this.drp.year = parseInt(sp[0]);
                    }
                    if (this.tableFilterInputs.to_date) {
                        this.drp.endDate = this.tableFilterInputs.to_date;
                    }
                    
                    // Simulate loading progress and initialize charts
                    this.loadInitialData();
                },
                
                // Rotate tips every 3 seconds
                startTipRotation() {
                    setInterval(() => {
                        if (this.initialLoading.show) {
                            this.initialLoading.currentTip = (this.initialLoading.currentTip + 1) % this.initialLoading.tips.length;
                        }
                    }, 3000);
                },
                
                // Load initial data with progress animation
                async loadInitialData() {
                    const steps = [
                        { status: 'Memuat statistik donatur...', progress: 15 },
                        { status: 'Memproses data trend...', progress: 35 },
                        { status: 'Menghitung ranking tim & CS...', progress: 55 },
                        { status: 'Membuat visualisasi chart...', progress: 75 },
                        { status: 'Mempersiapkan tabel data...', progress: 90 }
                    ];
                    
                    for (const step of steps) {
                        this.initialLoading.status = step.status;
                        this.initialLoading.progress = step.progress;
                        // Small delay for smooth progress animation
                        await new Promise(r => setTimeout(r, 300));
                    }
                    
                    // Initialize charts
                    this.initCharts();
                    
                    // Complete
                    this.initialLoading.progress = 100;
                    this.initialLoading.status = 'Dashboard siap! ✨';
                    
                    // Hide loading after short delay
                    await new Promise(r => setTimeout(r, 400));
                    this.initialLoading.show = false;
                },
                
                // Check if any table filter is active
                hasActiveTableFilters() {
                    return this.filters.status !== 'all' || 
                           this.filters.from_date || 
                           this.filters.to_date || 
                           this.filters.search ||
                           this.tableFilterInputs.table_cs !== 'all';
                },
                
                // Count active filters
                countActiveFilters() {
                    let count = 0;
                    if (this.filters.status !== 'all') count++;
                    if (this.filters.from_date || this.filters.to_date) count++;
                    if (this.filters.search) count++;
                    if (this.tableFilterInputs.table_cs !== 'all') count++;
                    return count;
                },
                
                // Check if inputs differ from applied filters
                hasUnappliedFilters() {
                    return this.tableFilterInputs.status !== this.filters.status ||
                           this.tableFilterInputs.from_date !== this.filters.from_date ||
                           this.tableFilterInputs.to_date !== this.filters.to_date ||
                           this.tableFilterInputs.search !== this.filters.search;
                },
                
                // Get status label for tags
                getStatusLabel(status) {
                    const labels = {
                        'aktif': '🟢 Aktif',
                        'baru': '✨ Baru',
                        'hilang': '🔴 Hilang',
                        'tidak_aktif': '⚪ Tidak Aktif'
                    };
                    return labels[status] || status;
                },
                
                // Apply status filter immediately (realtime)
                async applyStatusFilter() {
                    this.filters.status = this.tableFilterInputs.status;
                    this.filters.from_date = '';
                    this.filters.to_date = '';
                    this.drp.startDate = null;
                    this.drp.endDate = null;
                    this.drp.step = 1;
                    await this.applyTableFilters();
                },
                
                // Apply date range filter immediately (realtime)
                async applyDateRangeFilter() {
                    this.filters.from_date = this.tableFilterInputs.from_date;
                    this.filters.to_date = this.tableFilterInputs.to_date;
                    await this.applyTableFilters();
                },

                // ===== Date Range Picker Methods =====
                _drpMonths: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],

                drpMonthYear() {
                    return this._drpMonths[this.drp.month] + ' ' + this.drp.year;
                },
                drpPrevMonth() {
                    if (this.drp.month === 0) { this.drp.month = 11; this.drp.year--; }
                    else { this.drp.month--; }
                },
                drpNextMonth() {
                    if (this.drp.month === 11) { this.drp.month = 0; this.drp.year++; }
                    else { this.drp.month++; }
                },
                drpGetDays() {
                    var y = this.drp.year, m = this.drp.month;
                    var firstDay = new Date(y, m, 1).getDay(); // 0=Sun
                    var offset = firstDay === 0 ? 6 : firstDay - 1; // shift to Mon=0
                    var daysInMonth = new Date(y, m + 1, 0).getDate();
                    var cells = [];
                    // Empty cells before day 1
                    for (var i = 0; i < offset; i++) {
                        cells.push({ key: 'e' + i, d: null, date: null, empty: true });
                    }
                    // Day cells
                    var today = new Date(); today.setHours(0,0,0,0);
                    for (var d = 1; d <= daysInMonth; d++) {
                        var dt = new Date(y, m, d);
                        var iso = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                        cells.push({
                            key: iso,
                            d: d,
                            date: iso,
                            empty: false,
                            isToday: dt.getTime() === today.getTime(),
                        });
                    }
                    return cells;
                },
                drpSelectDate(cell) {
                    if (!cell.date || cell.empty) return;
                    if (this.drp.step === 0) {
                        // Pick start date
                        this.drp.startDate = cell.date;
                        this.drp.endDate = null;
                        this.drp.hoverDate = null;
                        this.drp.step = 1;
                    } else {
                        // Pick end date
                        var s = this.drp.startDate, e = cell.date;
                        if (e < s) { var tmp = s; s = e; e = tmp; }
                        this.drp.startDate = s;
                        this.drp.endDate = e;
                        this.drp.step = 0;
                        this.drp.open = false;
                        // Apply to filters
                        this.tableFilterInputs.from_date = s;
                        this.tableFilterInputs.to_date = e;
                        this.applyDateRangeFilter();
                    }
                },
                drpHover(cell) {
                    if (this.drp.step === 1 && cell.date) {
                        this.drp.hoverDate = cell.date;
                    }
                },
                drpCellClass(cell) {
                    if (cell.empty) return 'drp-empty';
                    var cls = [];
                    if (cell.isToday) cls.push('drp-today');
                    var s = this.drp.startDate, e = this.drp.endDate, h = this.drp.hoverDate, dt = cell.date;
                    if (s && e) {
                        // Both selected — show range
                        if (dt === s && dt === e) cls.push('drp-start drp-end');
                        else if (dt === s) cls.push('drp-start');
                        else if (dt === e) cls.push('drp-end');
                        else if (dt > s && dt < e) cls.push('drp-in-range');
                    } else if (s && !e && this.drp.step === 1) {
                        // Selecting — show hover preview
                        var rangeStart = s, rangeEnd = h || s;
                        if (rangeEnd < rangeStart) { var t = rangeStart; rangeStart = rangeEnd; rangeEnd = t; }
                        if (dt === rangeStart && dt === rangeEnd) cls.push('drp-start drp-end');
                        else if (dt === rangeStart) cls.push('drp-start');
                        else if (dt === rangeEnd) cls.push('drp-hover-end');
                        else if (dt > rangeStart && dt < rangeEnd) cls.push('drp-in-range');
                    }
                    return cls.join(' ');
                },
                drpDisplayText() {
                    var f = this.tableFilterInputs.from_date, t = this.tableFilterInputs.to_date;
                    if (f && t) {
                        return this._drpFmtShort(f) + ' — ' + this._drpFmtShort(t);
                    }
                    if (f) return this._drpFmtShort(f) + ' — ...';
                    return 'Pilih rentang tanggal';
                },
                _drpFmtShort(iso) {
                    var parts = iso.split('-');
                    var mn = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    return parseInt(parts[2]) + ' ' + mn[parseInt(parts[1]) - 1] + ' ' + parts[0];
                },
                drpClear() {
                    this.drp.startDate = null;
                    this.drp.endDate = null;
                    this.drp.hoverDate = null;
                    this.drp.step = 0;
                    this.tableFilterInputs.from_date = '';
                    this.tableFilterInputs.to_date = '';
                    this.applyDateRangeFilter();
                },
                drpPreset(key) {
                    var now = new Date();
                    var end = now.toISOString().slice(0, 10);
                    var start;
                    if (key === '7d') {
                        start = new Date(now.getTime() - 6 * 86400000).toISOString().slice(0, 10);
                    } else if (key === '30d') {
                        start = new Date(now.getTime() - 29 * 86400000).toISOString().slice(0, 10);
                    } else if (key === 'bulan') {
                        start = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-01';
                    } else if (key === '3bulan') {
                        var d3 = new Date(now.getFullYear(), now.getMonth() - 2, 1);
                        start = d3.toISOString().slice(0, 10);
                    } else if (key === 'tahun') {
                        start = now.getFullYear() + '-01-01';
                    }
                    this.drp.startDate = start;
                    this.drp.endDate = end;
                    this.drp.step = 0;
                    this.drp.open = false;
                    this.tableFilterInputs.from_date = start;
                    this.tableFilterInputs.to_date = end;
                    // Navigate calendar to start month
                    var sp = start.split('-');
                    this.drp.month = parseInt(sp[1]) - 1;
                    this.drp.year = parseInt(sp[0]);
                    this.applyDateRangeFilter();
                },
                // ===== End Date Range Picker =====
                
                // Apply search filter (manual - button/enter)
                async applySearchFilter() {
                    this.filters.search = this.tableFilterInputs.search;
                    await this.applyTableFilters();
                },
                
                // Apply table CS filter immediately (realtime)
                async applyTableCsFilter() {
                    this.filters.cs = this.tableFilterInputs.table_cs;
                    await this.applyTableFilters();
                },
                
                // Apply filters from inputs (when clicking "Cari" button) - kept for compatibility
                async applyTableFiltersFromInputs() {
                    this.filters.status = this.tableFilterInputs.status;
                    this.filters.from_date = this.tableFilterInputs.from_date;
                    this.filters.to_date = this.tableFilterInputs.to_date;
                    this.filters.search = this.tableFilterInputs.search;
                    await this.applyTableFilters();
                },
                
                async applyFilters() {
                    this.isLoading = true;
                    this.loadingMessage = 'Memuat data...';
                    this.chartLoading = true;
                    this.tableLoading = true;
                    
                    try {
                        // Fetch new chart data (tahun, tim, cs, kategori filters)
                        const chartParams = new URLSearchParams({ 
                            tahun: this.filters.tahun,
                            tim: this.filters.tim,
                            cs: this.filters.cs,
                            kategori: this.filters.kategori
                        });
                        const tableParams = new URLSearchParams(this.filters);
                        
                        const [chartResponse, tableResponse] = await Promise.all([
                            fetch('/analisis-donatur/chart-data?' + chartParams.toString()),
                            fetch('/analisis-donatur/donatur-list?' + tableParams.toString() + '&page=1')
                        ]);
                        
                        const chartData = await chartResponse.json();
                        const tableData = await tableResponse.json();
                        
                        // Update stats and charts
                        this.stats = chartData.stats;
                        this.charts = chartData.charts;
                        this.updateCharts();
                        
                        // Update table
                        this.table = tableData;
                        
                        // Update URL without reload (for bookmarking)
                        window.history.replaceState({}, '', '/analisis-donatur?' + tableParams.toString());
                        
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    }
                    
                    this.chartLoading = false;
                    this.tableLoading = false;
                    this.isLoading = false;
                },
                
                // Filter header (tahun, tim, cs, kategori) - untuk stats & charts
                async applyHeaderFilters() {
                    this.isLoading = true;
                    this.loadingMessage = 'Memuat data dashboard...';
                    this.chartLoading = true;
                    this.tableLoading = true;
                    
                    // Reset table-only filters when header filters change
                    this.filters.status = 'all';
                    this.filters.from_date = '';
                    this.filters.to_date = '';
                    this.filters.search = '';
                    // Also reset inputs
                    this.tableFilterInputs.status = 'all';
                    this.tableFilterInputs.from_date = '';
                    this.tableFilterInputs.to_date = '';
                    this.tableFilterInputs.search = '';
                    
                    try {
                        const chartParams = new URLSearchParams({ 
                            tahun: this.filters.tahun,
                            tim: this.filters.tim,
                            cs: this.filters.cs,
                            kategori: this.filters.kategori
                        });
                        const tableParams = new URLSearchParams(this.filters);
                        
                        const [chartResponse, tableResponse] = await Promise.all([
                            fetch('/analisis-donatur/chart-data?' + chartParams.toString()),
                            fetch('/analisis-donatur/donatur-list?' + tableParams.toString() + '&page=1')
                        ]);
                        
                        const chartData = await chartResponse.json();
                        const tableData = await tableResponse.json();
                        
                        this.stats = chartData.stats;
                        this.charts = chartData.charts;
                        this.updateCharts();
                        this.table = tableData;
                        
                        // Update URL with header filters
                        const urlParams = new URLSearchParams();
                        urlParams.set('tahun', this.filters.tahun);
                        if (this.filters.tim !== 'all') urlParams.set('tim', this.filters.tim);
                        if (this.filters.cs !== 'all') urlParams.set('cs', this.filters.cs);
                        if (this.filters.kategori !== 'all') urlParams.set('kategori', this.filters.kategori);
                        window.history.replaceState({}, '', '/analisis-donatur?' + urlParams.toString());
                        
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    }
                    
                    this.chartLoading = false;
                    this.tableLoading = false;
                    this.isLoading = false;
                },
                
                // Alias untuk backward compatibility
                async applyYearFilter() {
                    this.applyHeaderFilters();
                },
                
                // Filter tabel saja (status, date, search)
                async applyTableFilters() {
                    this.isLoading = true;
                    this.loadingMessage = 'Memfilter data...';
                    this.tableLoading = true;
                    
                    try {
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch('/analisis-donatur/donatur-list?' + params.toString() + '&page=1');
                        const data = await response.json();
                        
                        this.table = data;
                        
                    } catch (error) {
                        console.error('Error fetching table:', error);
                    }
                    
                    this.tableLoading = false;
                    this.isLoading = false;
                },
                
                // Klik stats card untuk filter tabel
                filterByStatus(status) {
                    this.filters.status = status;
                    this.applyTableFilters();
                    
                    // Scroll to table
                    setTimeout(() => {
                        document.getElementById('data-table-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                },
                
                async goToPage(page) {
                    if (page < 1 || page > this.table.last_page) return;
                    
                    this.isLoading = true;
                    this.loadingMessage = 'Memuat halaman ' + page + '...';
                    this.tableLoading = true;
                    
                    try {
                        const params = new URLSearchParams(this.filters);
                        params.set('page', page);
                        
                        const response = await fetch('/analisis-donatur/donatur-list?' + params.toString());
                        const data = await response.json();
                        
                        this.table = data;
                        
                        // Scroll to table
                        document.getElementById('data-table-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                        
                    } catch (error) {
                        console.error('Error fetching page:', error);
                    }
                    
                    this.tableLoading = false;
                    this.isLoading = false;
                },
                
                getPageNumbers() {
                    const current = this.table.current_page;
                    const last = this.table.last_page;
                    const delta = 2;
                    const range = [];
                    const rangeWithDots = [];
                    let l;
                    
                    for (let i = 1; i <= last; i++) {
                        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
                            range.push(i);
                        }
                    }
                    
                    for (let i of range) {
                        if (l) {
                            if (i - l === 2) {
                                rangeWithDots.push(l + 1);
                            } else if (i - l !== 1) {
                                rangeWithDots.push('...');
                            }
                        }
                        rangeWithDots.push(i);
                        l = i;
                    }
                    
                    return rangeWithDots;
                },
                
                // Reset semua filter
                resetFilters() {
                    this.filters = {
                        tahun: '2025',
                        tim: 'all',
                        cs: 'all',
                        kategori: 'all',
                        status: 'all',
                        from_date: '',
                        to_date: '',
                        search: ''
                    };
                    this.tableFilterInputs = {
                        status: 'all',
                        from_date: '',
                        to_date: '',
                        search: ''
                    };
                    this.applyHeaderFilters();
                },
                
                // Reset hanya filter tabel
                resetTableFilters() {
                    this.filters.status = 'all';
                    this.filters.from_date = '';
                    this.filters.to_date = '';
                    this.filters.search = '';
                    this.filters.cs = 'all';
                    // Also reset inputs
                    this.tableFilterInputs.status = 'all';
                    this.tableFilterInputs.from_date = '';
                    this.tableFilterInputs.to_date = '';
                    this.tableFilterInputs.search = '';
                    this.tableFilterInputs.table_cs = 'all';
                    this.applyTableFilters();
                },
                
                initCharts() {
                    // 1. Trend Chart dengan YoY Comparison
                    const trendCtx = document.getElementById('trendChart').getContext('2d');
                    const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 320);
                    trendGradient.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
                    trendGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                    
                    const trendData = this.charts.trend_bulanan || [];
                    const trendDataLalu = this.charts.trend_tahun_lalu || [];
                    
                    trendChart = new Chart(trendCtx, {
                        type: 'bar',
                        data: {
                            labels: bulanNames,
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Tahun Lalu',
                                    data: bulanNames.map((_, i) => {
                                        const found = trendDataLalu.find(d => d.bulan === i + 1);
                                        return found ? found.total : 0;
                                    }),
                                    backgroundColor: 'rgba(209, 213, 219, 0.5)',
                                    borderColor: 'rgba(156, 163, 175, 0.5)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barPercentage: 0.6,
                                    order: 2
                                },
                                {
                                    type: 'line',
                                    label: 'Tahun Ini',
                                    data: bulanNames.map((_, i) => {
                                        const found = trendData.find(d => d.bulan === i + 1);
                                        return found ? found.total : 0;
                                    }),
                                    borderColor: colors.primary,
                                    backgroundColor: trendGradient,
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: colors.primary,
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    order: 1
                                }
                            ]
                        },
                        options: this.getTrendChartOptions()
                    });
                    
                    // 1b. Trend Jumat Chart
                    const trendJumatCtx = document.getElementById('trendJumatChart');
                    if (trendJumatCtx) {
                        const jumatGradient = trendJumatCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
                        jumatGradient.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
                        jumatGradient.addColorStop(1, 'rgba(245, 158, 11, 0)');
                        
                        const trendJumatTahunIni = this.charts.trend_jumat?.tahun_ini || [];
                        const trendJumatTahunLalu = this.charts.trend_jumat?.tahun_lalu || [];
                        
                        trendJumatChart = new Chart(trendJumatCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: bulanNames,
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: this.charts.trend_jumat?.tahun_lalu_label || 'Tahun Lalu',
                                        data: trendJumatTahunLalu.map(d => d.rata_rata),
                                        backgroundColor: 'rgba(209, 213, 219, 0.5)',
                                        borderColor: 'rgba(156, 163, 175, 0.5)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        barPercentage: 0.6,
                                        order: 2
                                    },
                                    {
                                        type: 'line',
                                        label: this.charts.trend_jumat?.tahun_ini_label || 'Tahun Ini',
                                        data: trendJumatTahunIni.map(d => d.rata_rata),
                                        borderColor: '#F59E0B',
                                        backgroundColor: jumatGradient,
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.4,
                                        pointBackgroundColor: '#F59E0B',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        order: 1
                                    }
                                ]
                            },
                            options: this.getTrendJumatChartOptions()
                        });
                    }
                    
                    // 1c. Performa Harian Chart (per Hari dalam Seminggu YoY)
                    const performaHarianCtx = document.getElementById('performaHarianChart');
                    if (performaHarianCtx) {
                        const harianGradient = performaHarianCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
                        harianGradient.addColorStop(0, 'rgba(139, 92, 246, 0.25)');
                        harianGradient.addColorStop(1, 'rgba(139, 92, 246, 0)');
                        
                        const performaTahunIni = this.charts.performa_harian?.tahun_ini || [];
                        const performaTahunLalu = this.charts.performa_harian?.tahun_lalu || [];
                        const hariLabels = performaTahunIni.map(d => d.hari);
                        
                        performaHarianChart = new Chart(performaHarianCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: hariLabels,
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: this.charts.performa_harian?.tahun_lalu_label || 'Tahun Lalu',
                                        data: performaTahunLalu.map(d => d.rata_rata),
                                        backgroundColor: 'rgba(209, 213, 219, 0.5)',
                                        borderColor: 'rgba(156, 163, 175, 0.5)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        barPercentage: 0.6,
                                        order: 2
                                    },
                                    {
                                        type: 'line',
                                        label: this.charts.performa_harian?.tahun_ini_label || 'Tahun Ini',
                                        data: performaTahunIni.map(d => d.rata_rata),
                                        borderColor: '#8B5CF6',
                                        backgroundColor: harianGradient,
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.4,
                                        pointBackgroundColor: '#8B5CF6',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        order: 1
                                    }
                                ]
                            },
                            options: this.getPerformaHarianChartOptions()
                        });
                    }
                    
                    // 2. Tim Chart - Desktop (Doughnut)
                    const timCtx = document.getElementById('timChart').getContext('2d');
                    timChart = new Chart(timCtx, {
                        type: 'doughnut',
                        data: {
                            labels: this.charts.distribusi_tim.map(d => d.tim),
                            datasets: [{
                                data: this.charts.distribusi_tim.map(d => d.total),
                                backgroundColor: colors.gradient,
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: this.getDoughnutChartOptions()
                    });
                    
                    // 2b. Tim Chart - Mobile
                    const timMobileCtx = document.getElementById('timChartMobile');
                    if (timMobileCtx) {
                        timChartMobile = new Chart(timMobileCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: this.charts.distribusi_tim.map(d => d.tim),
                                datasets: [{
                                    data: this.charts.distribusi_tim.map(d => d.total),
                                    backgroundColor: colors.gradient,
                                    borderWidth: 0,
                                    hoverOffset: 8
                                }]
                            },
                            options: this.getDoughnutChartOptions()
                        });
                    }
                    
                    // 3. CS Chart - Desktop (Horizontal Bar)
                    const csCtx = document.getElementById('csChart');
                    if (csCtx) {
                        const csData = this.charts.ranking_cs || [];
                        csChart = new Chart(csCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: csData.map(d => {
                                    const nama = d.nama_cs || 'Unknown';
                                    return nama.length > 12 ? nama.substring(0, 12) + '...' : nama;
                                }),
                                datasets: [{
                                    label: 'Perolehan',
                                    data: csData.map(d => d.total),
                                    backgroundColor: colors.primary,
                                    borderRadius: 4,
                                    borderSkipped: false,
                                    barThickness: 14
                                }]
                            },
                            options: this.getHorizontalBarOptions()
                        });
                    }
                    
                    // 3b. CS Chart - Mobile
                    const csMobileCtx = document.getElementById('csChartMobile');
                    if (csMobileCtx) {
                        const csData = this.charts.ranking_cs || [];
                        csChartMobile = new Chart(csMobileCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: csData.slice(0, 10).map(d => {
                                    const nama = d.nama_cs || 'Unknown';
                                    return nama.length > 10 ? nama.substring(0, 10) + '...' : nama;
                                }),
                                datasets: [{
                                    label: 'Perolehan',
                                    data: csData.slice(0, 10).map(d => d.total),
                                    backgroundColor: colors.primary,
                                    borderRadius: 4,
                                    borderSkipped: false,
                                    barThickness: 12
                                }]
                            },
                            options: this.getHorizontalBarOptions()
                        });
                    }
                    
                    // 4. Top Donatur Chart - Desktop
                    const topCtx = document.getElementById('topDonaturChart').getContext('2d');
                    topDonaturChart = new Chart(topCtx, {
                        type: 'bar',
                        data: {
                            labels: this.charts.top_donatur.map(d => {
                                const nama = d.nama || 'Unknown';
                                return nama.length > 15 ? nama.substring(0, 15) + '...' : nama;
                            }),
                            datasets: [{
                                label: 'Total Donasi',
                                data: this.charts.top_donatur.map(d => d.total),
                                backgroundColor: colors.primary,
                                borderRadius: 6,
                                borderSkipped: false
                            }]
                        },
                        options: this.getBarChartOptions()
                    });
                    
                    // 4b. Top Donatur Chart - Mobile
                    const topMobileCtx = document.getElementById('topDonaturChartMobile');
                    if (topMobileCtx) {
                        topDonaturChartMobile = new Chart(topMobileCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: this.charts.top_donatur.slice(0, 7).map(d => {
                                    const nama = d.nama || 'Unknown';
                                    return nama.length > 10 ? nama.substring(0, 10) + '...' : nama;
                                }),
                                datasets: [{
                                    label: 'Total Donasi',
                                    data: this.charts.top_donatur.slice(0, 7).map(d => d.total),
                                    backgroundColor: colors.primary,
                                    borderRadius: 4,
                                    borderSkipped: false,
                                    barThickness: 12
                                }]
                            },
                            options: this.getBarChartOptions()
                        });
                    }
                    
                    // 5. Retention Chart - Desktop
                    const retentionCtx = document.getElementById('retentionChart').getContext('2d');
                    retentionChart = new Chart(retentionCtx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [this.charts.retention_rate, 100 - this.charts.retention_rate],
                                backgroundColor: [colors.primary, '#F3F4F6'],
                                borderWidth: 0
                            }]
                        },
                        options: this.getRetentionChartOptions()
                    });
                    
                    // 5b. Retention Chart - Mobile
                    const retentionMobileCtx = document.getElementById('retentionChartMobile');
                    if (retentionMobileCtx) {
                        retentionChartMobile = new Chart(retentionMobileCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                datasets: [{
                                    data: [this.charts.retention_rate, 100 - this.charts.retention_rate],
                                    backgroundColor: [colors.primary, '#F3F4F6'],
                                    borderWidth: 0
                                }]
                            },
                            options: this.getRetentionChartOptions()
                        });
                    }
                    
                    // Note: Segmentasi chart sudah diganti dengan horizontal bar (tidak perlu canvas)
                    
                    // 7. Repeat vs One-time Chart - Desktop
                    const repeatData = this.charts.repeat_vs_onetime || [];
                    const repeatColors = ['#9CA3AF', '#10B981'];
                    
                    const repeatCtx = document.getElementById('repeatChart');
                    if (repeatCtx) {
                        repeatChart = new Chart(repeatCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: repeatData.map(d => d.label),
                                datasets: [{
                                    data: repeatData.map(d => d.count),
                                    backgroundColor: repeatColors,
                                    borderWidth: 0,
                                    hoverOffset: 8
                                }]
                            },
                            options: this.getRepeatChartOptions()
                        });
                    }
                    
                    // 7b. Repeat Chart - Mobile
                    const repeatMobileCtx = document.getElementById('repeatChartMobile');
                    if (repeatMobileCtx) {
                        repeatChartMobile = new Chart(repeatMobileCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: repeatData.map(d => d.label),
                                datasets: [{
                                    data: repeatData.map(d => d.count),
                                    backgroundColor: repeatColors,
                                    borderWidth: 0,
                                    hoverOffset: 6
                                }]
                            },
                            options: this.getRepeatChartOptions()
                        });
                    }
                },
                
                updateCharts() {
                    const trendData = this.charts.trend_bulanan || [];
                    const trendDataLalu = this.charts.trend_tahun_lalu || [];
                    
                    // Update Trend Chart (YoY)
                    trendChart.data.datasets[0].data = bulanNames.map((_, i) => {
                        const found = trendDataLalu.find(d => d.bulan === i + 1);
                        return found ? found.total : 0;
                    });
                    trendChart.data.datasets[1].data = bulanNames.map((_, i) => {
                        const found = trendData.find(d => d.bulan === i + 1);
                        return found ? found.total : 0;
                    });
                    trendChart.update('none');
                    
                    // Update Trend Jumat Chart
                    if (trendJumatChart) {
                        const trendJumatTahunIni = this.charts.trend_jumat?.tahun_ini || [];
                        const trendJumatTahunLalu = this.charts.trend_jumat?.tahun_lalu || [];
                        
                        trendJumatChart.data.datasets[0].data = trendJumatTahunLalu.map(d => d.rata_rata);
                        trendJumatChart.data.datasets[0].label = this.charts.trend_jumat?.tahun_lalu_label || 'Tahun Lalu';
                        trendJumatChart.data.datasets[1].data = trendJumatTahunIni.map(d => d.rata_rata);
                        trendJumatChart.data.datasets[1].label = this.charts.trend_jumat?.tahun_ini_label || 'Tahun Ini';
                        trendJumatChart.update('none');
                    }
                    
                    // Update Performa Harian Chart
                    if (performaHarianChart) {
                        const performaTahunIni = this.charts.performa_harian?.tahun_ini || [];
                        const performaTahunLalu = this.charts.performa_harian?.tahun_lalu || [];
                        
                        performaHarianChart.data.labels = performaTahunIni.map(d => d.hari);
                        performaHarianChart.data.datasets[0].data = performaTahunLalu.map(d => d.rata_rata);
                        performaHarianChart.data.datasets[0].label = this.charts.performa_harian?.tahun_lalu_label || 'Tahun Lalu';
                        performaHarianChart.data.datasets[1].data = performaTahunIni.map(d => d.rata_rata);
                        performaHarianChart.data.datasets[1].label = this.charts.performa_harian?.tahun_ini_label || 'Tahun Ini';
                        performaHarianChart.update('none');
                    }
                    
                    // Update Tim Charts (Desktop + Mobile)
                    const timLabels = this.charts.distribusi_tim.map(d => d.tim);
                    const timData = this.charts.distribusi_tim.map(d => d.total);
                    
                    timChart.data.labels = timLabels;
                    timChart.data.datasets[0].data = timData;
                    timChart.update('none');
                    
                    if (timChartMobile) {
                        timChartMobile.data.labels = timLabels;
                        timChartMobile.data.datasets[0].data = timData;
                        timChartMobile.update('none');
                    }
                    
                    // Update CS Charts (Desktop + Mobile)
                    const csData = this.charts.ranking_cs || [];
                    
                    if (csChart) {
                        csChart.data.labels = csData.map(d => {
                            const nama = d.nama_cs || 'Unknown';
                            return nama.length > 12 ? nama.substring(0, 12) + '...' : nama;
                        });
                        csChart.data.datasets[0].data = csData.map(d => d.total);
                        csChart.update('none');
                    }
                    
                    if (csChartMobile) {
                        csChartMobile.data.labels = csData.slice(0, 10).map(d => {
                            const nama = d.nama_cs || 'Unknown';
                            return nama.length > 10 ? nama.substring(0, 10) + '...' : nama;
                        });
                        csChartMobile.data.datasets[0].data = csData.slice(0, 10).map(d => d.total);
                        csChartMobile.update('none');
                    }
                    
                    // Update Top Donatur Charts (Desktop + Mobile)
                    topDonaturChart.data.labels = this.charts.top_donatur.map(d => {
                        const nama = d.nama || 'Unknown';
                        return nama.length > 15 ? nama.substring(0, 15) + '...' : nama;
                    });
                    topDonaturChart.data.datasets[0].data = this.charts.top_donatur.map(d => d.total);
                    topDonaturChart.update('none');
                    
                    if (topDonaturChartMobile) {
                        topDonaturChartMobile.data.labels = this.charts.top_donatur.slice(0, 7).map(d => {
                            const nama = d.nama || 'Unknown';
                            return nama.length > 10 ? nama.substring(0, 10) + '...' : nama;
                        });
                        topDonaturChartMobile.data.datasets[0].data = this.charts.top_donatur.slice(0, 7).map(d => d.total);
                        topDonaturChartMobile.update('none');
                    }
                    
                    // Update Retention Charts (Desktop + Mobile)
                    retentionChart.data.datasets[0].data = [this.charts.retention_rate, 100 - this.charts.retention_rate];
                    retentionChart.update('none');
                    
                    if (retentionChartMobile) {
                        retentionChartMobile.data.datasets[0].data = [this.charts.retention_rate, 100 - this.charts.retention_rate];
                        retentionChartMobile.update('none');
                    }
                    
                    // Update Segmentasi Charts (Desktop + Mobile)
                    const segmentasiData = this.charts.segmentasi_nominal || [];
                    const segmentasiLabels = segmentasiData.map(d => d.label);
                    const segmentasiCounts = segmentasiData.map(d => d.count);
                    
                    if (segmentasiChart) {
                        segmentasiChart.data.labels = segmentasiLabels;
                        segmentasiChart.data.datasets[0].data = segmentasiCounts;
                        segmentasiChart.update('none');
                    }
                    
                    if (segmentasiChartMobile) {
                        segmentasiChartMobile.data.labels = segmentasiLabels;
                        segmentasiChartMobile.data.datasets[0].data = segmentasiCounts;
                        segmentasiChartMobile.update('none');
                    }
                    
                    // Update Repeat Charts (Desktop + Mobile)
                    const repeatData = this.charts.repeat_vs_onetime || [];
                    const repeatLabels = repeatData.map(d => d.label);
                    const repeatCounts = repeatData.map(d => d.count);
                    
                    if (repeatChart) {
                        repeatChart.data.labels = repeatLabels;
                        repeatChart.data.datasets[0].data = repeatCounts;
                        repeatChart.update('none');
                    }
                    
                    if (repeatChartMobile) {
                        repeatChartMobile.data.labels = repeatLabels;
                        repeatChartMobile.data.datasets[0].data = repeatCounts;
                        repeatChartMobile.update('none');
                    }
                },
                
                getTrendChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: { 
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => ctx.dataset.label + ': Rp ' + this.formatNumber(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6B7280', font: { size: 11 } } },
                            y: {
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    color: '#6B7280',
                                    callback: (v) => 'Rp ' + this.formatCompact(v)
                                }
                            }
                        }
                    };
                },
                
                getTrendJumatChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0].dataIndex;
                                        const jumatInfo = this.charts.trend_jumat?.tahun_ini?.[idx];
                                        return items[0].label + (jumatInfo ? ` (${jumatInfo.jumlah_jumat} Jumat)` : '');
                                    },
                                    label: (ctx) => {
                                        const label = ctx.dataset.label || '';
                                        return label + ': Rp ' + this.formatNumber(ctx.raw) + '/Jumat';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6B7280', font: { size: 11 } } },
                            y: {
                                grid: { color: '#FEF3C7' },
                                ticks: {
                                    color: '#6B7280',
                                    callback: (v) => 'Rp ' + this.formatCompact(v)
                                }
                            }
                        }
                    };
                },
                
                getPerformaHarianChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0].dataIndex;
                                        const harianInfo = this.charts.performa_harian?.tahun_ini?.[idx];
                                        return (harianInfo?.hari_full || items[0].label) + (harianInfo ? ` (${harianInfo.jumlah_hari} hari/tahun)` : '');
                                    },
                                    label: (ctx) => {
                                        const label = ctx.dataset.label || '';
                                        return label + ': Rp ' + this.formatNumber(ctx.raw) + '/hari';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { 
                                grid: { display: false }, 
                                ticks: { 
                                    color: '#6B7280', 
                                    font: { size: 11, weight: '500' }
                                } 
                            },
                            y: {
                                grid: { color: '#EDE9FE' },
                                ticks: {
                                    color: '#6B7280',
                                    callback: (v) => 'Rp ' + this.formatCompact(v)
                                }
                            }
                        }
                    };
                },
                
                getLineChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => 'Rp ' + this.formatNumber(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6B7280' } },
                            y: {
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    color: '#6B7280',
                                    callback: (v) => 'Rp ' + this.formatCompact(v)
                                }
                            }
                        }
                    };
                },
                
                getHorizontalBarOptions() {
                    return {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => 'Rp ' + this.formatNumber(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    color: '#6B7280',
                                    font: { size: 10 },
                                    callback: (v) => this.formatCompact(v)
                                }
                            },
                            y: { 
                                grid: { display: false }, 
                                ticks: { color: '#6B7280', font: { size: 10 } } 
                            }
                        }
                    };
                },
                
                getRetentionChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '75%',
                        rotation: -90,
                        circumference: 180,
                        plugins: { 
                            legend: { display: false }, 
                            tooltip: { enabled: false } 
                        }
                    };
                },
                
                getRepeatChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { usePointStyle: true, padding: 12, font: { size: 11 } }
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                        return ctx.label + ': ' + this.formatNumber(ctx.raw) + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    };
                },
                
                getDoughnutChartOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { usePointStyle: true, padding: 15, font: { size: 11 } }
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => ctx.label + ': Rp ' + this.formatNumber(ctx.raw)
                                }
                            }
                        }
                    };
                },
                
                getBarChartOptions() {
                    return {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => 'Rp ' + this.formatNumber(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    color: '#6B7280',
                                    callback: (v) => 'Rp ' + this.formatCompact(v)
                                }
                            },
                            y: { grid: { display: false }, ticks: { color: '#6B7280', font: { size: 10 } } }
                        }
                    };
                },
                
                // Format angka ke format Indonesia (1.000.000)
                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                },
                
                // Format angka singkat Indonesia (1,5 M, 500 Jt, 250 Rb)
                formatCompact(num) {
                    if (num >= 1000000000) {
                        const val = num / 1000000000;
                        return (val % 1 === 0 ? val.toFixed(0) : val.toFixed(1).replace('.', ',')) + ' M';
                    } else if (num >= 1000000) {
                        const val = num / 1000000;
                        return (val % 1 === 0 ? val.toFixed(0) : val.toFixed(1).replace('.', ',')) + ' Jt';
                    } else if (num >= 1000) {
                        const val = num / 1000;
                        return (val % 1 === 0 ? val.toFixed(0) : val.toFixed(1).replace('.', ',')) + ' Rb';
                    }
                    return num.toString();
                },
                
                // Format currency lengkap
                formatCurrency(num) {
                    return 'Rp ' + this.formatNumber(num);
                }
            }
        }
    </script>
@endpush

</x-layouts.app>
