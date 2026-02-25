{{-- 
    Sidebar Component
    Usage: <x-sidebar :active="'dashboard'" />
    
    Active options:
    - 'dashboard' = Analisis Donatur / Dashboard
    - 'donatur' = Donatur
    - 'daftar-cs' = Daftar CS
    - 'performa-bulanan' = Performa Bulanan
    - 'performa-cs' = Performa CS (H2H)
    - 'monitor-cs' = Monitor CS
    - 'laporan-perolehan' = Realtime Perolehan
    - 'donasi-web' = Donasi Website
    - 'kwitansi-v1' = Kwitansi v1
    - 'laporan-ramadhan' = Laporan Ramadhan
--}}

@props(['active' => 'dashboard'])

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[60] lg:hidden">
</div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed top-0 left-0 z-[70] w-72 h-screen bg-white/95 backdrop-blur-xl border-r border-primary-100
              transform transition-transform duration-300 ease-in-out flex flex-col shadow-xl lg:shadow-none">
    
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 bg-gradient-to-r from-primary-50 to-white">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <i class="bi bi-heart-pulse-fill text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-800">Abbarat</h1>
                <p class="text-[10px] text-gray-500">Management System</p>
            </div>
        </div>
        <!-- Close button mobile -->
        <button @click="sidebarOpen = false" class="lg:hidden p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 px-3">
        <!-- Dashboard / Analisis Donatur -->
        <a href="{{ route('analisis-donatur') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'dashboard' ? 'bg-primary-100 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-grid-1x2-fill text-lg"></i>
            <span class="font-medium">Dashboard</span>
            @if($active === 'dashboard')
            <span class="ml-auto w-2 h-2 bg-primary-500 rounded-full"></span>
            @endif
        </a>
        
        <!-- Divider -->
        <div class="my-4 mx-2 border-t border-gray-200"></div>
        <p class="px-4 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Data Master</p>
        
        <!-- Donatur -->
        <a href="{{ route('donatur.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'donatur' ? 'bg-primary-100 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-people-fill text-lg"></i>
            <span class="font-medium">Donatur</span>
            @if($active === 'donatur')
            <span class="ml-auto w-2 h-2 bg-primary-500 rounded-full"></span>
            @endif
        </a>
        
        <!-- Customer Service -->
        <div x-data="{ open: {{ in_array($active, ['daftar-cs', 'performa-bulanan', 'performa-cs']) ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="menu-item w-full flex items-center justify-between px-4 py-3 rounded-xl text-gray-600 hover:text-primary-700 hover:bg-primary-50 transition mb-1">
                <div class="flex items-center gap-3">
                    <i class="bi bi-headset text-lg"></i>
                    <span class="font-medium">Customer Service</span>
                </div>
                <i class="bi bi-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l-2 border-primary-100 pl-4">
                <a href="{{ route('daftar-cs.index') }}" wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'daftar-cs' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-list-ul text-xs"></i>
                    <span>Daftar CS</span>
                    @if($active === 'daftar-cs')
                    <span class="ml-auto w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('performa-bulanan.index') }}" wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'performa-bulanan' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-bar-chart-line text-xs"></i>
                    <span>Performa Bulanan</span>
                    @if($active === 'performa-bulanan')
                    <span class="ml-auto w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('performa-cs.index') }}" wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'performa-cs' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-trophy text-xs"></i>
                    <span>Performa CS (H2H)</span>
                    @if($active === 'performa-cs')
                    <span class="ml-auto w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
                    @endif
                </a>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="my-4 mx-2 border-t border-gray-200"></div>
        <p class="px-4 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Monitoring</p>
        
        <!-- Monitor CS -->
        <a href="{{ route('monitor-cs.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'monitor-cs' ? 'bg-primary-100 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-eye-fill text-lg"></i>
            <span class="font-medium">Monitor CS</span>
            @if($active === 'monitor-cs')
            <span class="ml-auto w-2 h-2 bg-primary-500 rounded-full"></span>
            @endif
        </a>
        
        <!-- Divider -->
        <div class="my-4 mx-2 border-t border-gray-200"></div>
        <p class="px-4 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Laporan</p>
        
        <!-- Laporan Perolehan CRM - NEW -->
        <a href="{{ route('laporan-perolehan.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'laporan-perolehan' ? 'bg-primary-100 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-graph-up-arrow text-lg"></i>
            <span class="font-medium">Realtime Perolehan</span>
            @if($active === 'laporan-perolehan')
            <span class="ml-auto w-2 h-2 bg-primary-500 rounded-full"></span>
            @else
            <span class="ml-auto px-1.5 py-0.5 text-[9px] font-bold bg-red-500 text-white rounded-full animate-pulse">LIVE</span>
            @endif
        </a>

        <!-- Donasi Website Analytics -->
        <a href="{{ route('donasi-web.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'donasi-web' ? 'bg-primary-100 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-globe2 text-lg"></i>
            <span class="font-medium">Donasi Website</span>
            @if($active === 'donasi-web')
            <span class="ml-auto w-2 h-2 bg-primary-500 rounded-full"></span>
            @else
            <span class="ml-auto px-1.5 py-0.5 text-[9px] font-bold bg-blue-500 text-white rounded-full">NEW</span>
            @endif
        </a>
        
        <!-- Laporan Lainnya -->
        <div x-data="{ open: {{ in_array($active, ['input-laporan', 'laporan-ramadhan']) ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="menu-item w-full flex items-center justify-between px-4 py-3 rounded-xl {{ in_array($active, ['input-laporan', 'laporan-ramadhan']) ? 'text-primary-700 bg-primary-50 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }} transition mb-1">
                <div class="flex items-center gap-3">
                    <i class="bi bi-file-earmark-text-fill text-lg"></i>
                    <span class="font-medium">Laporan Lainnya</span>
                </div>
                <i class="bi bi-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l-2 border-primary-100 pl-4">
                <a href="{{ route('input-laporan.index') }}" wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'input-laporan' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-cash-coin text-xs"></i>
                    <span>Input Laporan</span>
                    @if($active === 'input-laporan')
                    <span class="ml-auto w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('coming-soon', ['menu' => 'Laporan Bulanan']) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary-700 hover:bg-primary-50 transition">
                    <i class="bi bi-calendar3 text-xs"></i>
                    <span>Laporan Bulanan</span>
                </a>
                <a href="{{ route('laporan-ramadhan.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'laporan-ramadhan' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-moon-stars text-xs"></i>
                    @if($active === 'laporan-ramadhan') <div class="w-1.5 h-1.5 rounded-full bg-primary-500 mr-1"></div> @endif
                    <span>Laporan Ramadhan</span>
                </a>
            </div>
        </div>
        
        <!-- Kwitansi -->
        <div x-data="{ open: {{ in_array($active, ['kwitansi-v1', 'kwitansi-v2']) ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="menu-item w-full flex items-center justify-between px-4 py-3 rounded-xl {{ in_array($active, ['kwitansi-v1', 'kwitansi-v2']) ? 'text-primary-700 bg-primary-50 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }} transition mb-1">
                <div class="flex items-center gap-3">
                    <i class="bi bi-receipt text-lg"></i>
                    <span class="font-medium">Kwitansi</span>
                </div>
                <i class="bi bi-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l-2 border-primary-100 pl-4">
                <a href="{{ route('kwitansi-v1.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'kwitansi-v1' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-receipt text-xs"></i>
                    @if($active === 'kwitansi-v1') <div class="w-1.5 h-1.5 rounded-full bg-primary-500 mr-1"></div> @endif
                    <span>Kwitansi v1</span>
                </a>
                <a href="{{ route('coming-soon', ['menu' => 'Kwitansi v2']) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $active === 'kwitansi-v2' ? 'text-primary-700 bg-primary-50 font-medium' : 'text-gray-500 hover:text-primary-700 hover:bg-primary-50' }} transition">
                    <i class="bi bi-receipt-cutoff text-xs"></i>
                    <span>Kwitansi v2</span>
                </a>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="my-4 mx-2 border-t border-gray-200"></div>
        <p class="px-4 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">HRD</p>
        
        <!-- Data Pegawai -->
        <a href="{{ route('data-pegawai.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'data-pegawai' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-person-badge-fill text-lg"></i>
            <span class="font-medium">Data Pegawai</span>
        </a>
        
        <!-- Absensi -->
        <a href="{{ route('absensi.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'absensi' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-calendar-check-fill text-lg"></i>
            <span class="font-medium">Absensi</span>
        </a>
        
        <!-- Divider -->
        <div class="my-4 mx-2 border-t border-gray-200"></div>
        <p class="px-4 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Lainnya</p>
        
        <!-- Partnership -->
        <a href="{{ route('partnership.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'partnership' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-building text-lg"></i>
            <span class="font-medium">Partnership</span>
        </a>
        
        <!-- Penyebaran Toko -->
        <a href="{{ route('penyebaran-toko.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'penyebaran-toko' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-geo-alt-fill text-lg"></i>
            <span class="font-medium">Penyebaran Toko</span>
        </a>
        
        <!-- User Management -->
        <a href="{{ route('manajemen-user.index') }}" wire:navigate
           class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition mb-1 {{ $active === 'manajemen-user' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-primary-700 hover:bg-primary-50' }}">
            <i class="bi bi-person-gear text-lg"></i>
            <span class="font-medium">Manajemen User</span>
        </a>
    </nav>
    
    <!-- User Section -->
    <div class="border-t border-primary-100 p-4 bg-gradient-to-r from-primary-50/50 to-white">
        <div x-data="{ userMenuSidebar: false }" class="relative">
            <button @click="userMenuSidebar = !userMenuSidebar" 
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white hover:bg-gray-50 border border-gray-200 hover:border-primary-200 transition cursor-pointer shadow-sm">
                <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center shadow-md shadow-primary-500/20">
                    <span class="text-white font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-gray-800 font-medium text-sm truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-gray-400 text-[10px] truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <i class="bi bi-chevron-up text-gray-400 text-xs transition-transform" :class="userMenuSidebar ? '' : 'rotate-180'"></i>
            </button>
            
            <!-- User Dropdown -->
            <div x-show="userMenuSidebar" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-75"
                 @click.away="userMenuSidebar = false"
                 class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <a href="/admin" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-700 transition">
                    <i class="bi bi-grid"></i>
                    <span>Admin Panel</span>
                </a>
                <a href="{{ route('coming-soon', ['menu' => 'Pengaturan']) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-700 transition">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan</span>
                </a>
                <div class="border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="bi bi-box-arrow-left"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
