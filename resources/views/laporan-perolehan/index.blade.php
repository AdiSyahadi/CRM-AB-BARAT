<x-layouts.app active="laporan-perolehan" title="Realtime Perolehan - CRM Dashboard" xData="laporanPerolehanApp()" :chartjs="true">

@push('styles')
<style>
    /* Leaderboard */
    .leaderboard-row { transition: all 0.2s ease; }
    .leaderboard-row:hover { background: linear-gradient(90deg, rgba(16, 185, 129, 0.08) 0%, transparent 100%); }
    
    /* Live Feed */
    .feed-item { animation: slideIn 0.3s ease-out; }
</style>
@endpush
    
@push('before-sidebar')
    <!-- Initial Loading Modal -->
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
                    <i class="bi bi-graph-up-arrow text-white text-4xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Realtime Perolehan</h2>
            <p class="text-gray-500 mb-6">Menyiapkan data untuk Anda...</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500 ease-out"
                     :style="'width: ' + initialLoading.progress + '%'"></div>
            </div>
            
            <!-- Loading Status -->
            <p class="text-sm text-primary-600 font-medium mb-6" x-text="initialLoading.status">Memuat data...</p>
            
            <!-- Tips -->
            <div class="bg-gradient-to-r from-primary-50 to-green-50 rounded-2xl p-4 border border-primary-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="text-primary-600 text-lg" :class="'bi ' + initialLoading.tips[initialLoading.currentTip].icon"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-semibold text-primary-700 mb-1">Tips</p>
                        <p class="text-sm text-gray-600 leading-relaxed" x-text="initialLoading.tips[initialLoading.currentTip].text"></p>
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
    
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
            <div class="flex items-center justify-between px-4 md:px-6 py-3">
                <!-- Left: Menu & Title -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl" aria-label="Toggle menu">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg md:text-xl font-bold text-gray-800">Realtime Perolehan</h1>
                            <span class="relative flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">
                                <span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span>
                                LIVE
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Monitoring donasi realtime</p>
                    </div>
                </div>
                
                <!-- Right: Date Picker & Refresh -->
                <div class="flex items-center gap-2">
                    <!-- Date Picker -->
                    <div class="relative">
                        <input type="date" 
                               x-model="selectedDate" 
                               @change="loadAllData()"
                               class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    
                    <!-- Refresh Button -->
                    <button @click="loadAllData()" 
                            :disabled="isLoading"
                            class="p-2 bg-primary-50 hover:bg-primary-100 text-primary-600 rounded-xl transition disabled:opacity-50"
                            aria-label="Refresh data">
                        <i class="bi bi-arrow-clockwise" :class="isLoading ? 'animate-spin' : ''"></i>
                    </button>
                    
                    <!-- Auto Refresh Toggle -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl">
                        <span class="text-xs text-gray-500">Auto</span>
                        <button @click="toggleAutoRefresh()" 
                                class="relative w-10 h-5 rounded-full transition-colors"
                                :class="autoRefresh ? 'bg-primary-500' : 'bg-gray-300'">
                            <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                  :class="autoRefresh ? 'translate-x-5' : ''"></span>
                        </button>
                        <span class="text-xs" :class="autoRefresh ? 'text-primary-600 font-medium' : 'text-gray-400'" x-text="autoRefresh ? 'ON' : 'OFF'"></span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content Area -->
        <div class="p-4 md:p-6 space-y-6">
            
            <!-- ======================= -->
            <!-- SECTION 1: HERO STATS   -->
            <!-- ======================= -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Perolehan -->
                <div class="stat-card bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl p-4 md:p-5 text-white shadow-lg shadow-primary-500/20">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="bi bi-cash-stack text-lg md:text-xl"></i>
                        </div>
                        <div x-show="stats.growth_perolehan != 0" 
                             class="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                             :class="stats.growth_perolehan >= 0 ? 'bg-green-400/30 text-green-100' : 'bg-red-400/30 text-red-100'">
                            <i :class="stats.growth_perolehan >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                            <span x-text="Math.abs(stats.growth_perolehan || 0).toFixed(1) + '%'"></span>
                        </div>
                    </div>
                    <p class="text-white/70 text-xs md:text-sm mb-1">Total Perolehan</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold tracking-tight">
                        <span class="text-base md:text-lg">Rp</span>
                        <span x-text="formatCompact(stats.total_perolehan)"></span>
                    </p>
                    <p class="text-white/60 text-xs mt-2 hidden md:block">
                        <i class="bi bi-calendar3 mr-1"></i>
                        <span x-text="selectedDate"></span>
                    </p>
                </div>
                
                <!-- Total Transaksi -->
                <div class="stat-card bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-receipt text-blue-600 text-lg md:text-xl"></i>
                        </div>
                        <div x-show="stats.growth_transaksi != 0" 
                             class="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                             :class="stats.growth_transaksi >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                            <i :class="stats.growth_transaksi >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                            <span x-text="Math.abs(stats.growth_transaksi || 0).toFixed(1) + '%'"></span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-1">Total Transaksi</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800" x-text="formatNumber(stats.total_transaksi)"></p>
                    <p class="text-gray-400 text-xs mt-2 hidden md:block">
                        <i class="bi bi-calculator mr-1"></i>
                        Avg: Rp <span x-text="formatCompact(stats.avg_per_transaksi)"></span>/trx
                    </p>
                </div>
                
                <!-- Donatur Unik -->
                <div class="stat-card bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-people-fill text-purple-600 text-lg md:text-xl"></i>
                        </div>
                        <div x-show="stats.growth_donatur != 0" 
                             class="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                             :class="stats.growth_donatur >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                            <i :class="stats.growth_donatur >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                            <span x-text="Math.abs(stats.growth_donatur || 0).toFixed(1) + '%'"></span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-1">Donatur Unik</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_unik)"></p>
                    <p class="text-gray-400 text-xs mt-2 hidden md:block">
                        <i class="bi bi-person-plus mr-1"></i>
                        <span x-text="stats.donatur_baru || 0"></span> donatur baru
                    </p>
                </div>
                
                <!-- CS Aktif -->
                <div class="stat-card bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-headset text-amber-600 text-lg md:text-xl"></i>
                        </div>
                        <span class="px-2 py-1 bg-primary-100 text-primary-600 rounded-full text-xs font-medium">
                            <i class="bi bi-activity mr-1"></i>Aktif
                        </span>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-1">CS Aktif</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800" x-text="stats.active_cs"></p>
                    <p class="text-gray-400 text-xs mt-2 hidden md:block">
                        <i class="bi bi-trophy mr-1"></i>
                        Top: <span x-text="stats.top_cs || '-'"></span>
                    </p>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 2: CHART & LEADERBOARD ROW   -->
            <!-- ===================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Chart Perolehan Per Jam -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-bar-chart-fill text-primary-500"></i>
                                Perolehan Per Jam
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Distribusi perolehan berdasarkan jam</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-medium"
                                  x-show="hourly.peak_hour">
                                <i class="bi bi-lightning-fill mr-1"></i>
                                Peak: <span x-text="hourly.peak_hour"></span>
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <canvas id="hourlyChart" height="200"></canvas>
                    </div>
                </div>
                
                <!-- CS Leaderboard -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-50 to-white">
                        <div>
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-trophy-fill text-amber-500"></i>
                                Top CS Hari Ini
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Berdasarkan total perolehan</p>
                        </div>
                        <span class="text-xs text-gray-400" x-text="'Top ' + leaderboard.length"></span>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <template x-for="(cs, index) in leaderboard.slice(0, 10)" :key="index">
                            <div class="leaderboard-row flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0">
                                <!-- Rank -->
                                <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full text-sm font-bold"
                                     :class="{
                                         'bg-amber-400 text-white shadow-md shadow-amber-400/30': index === 0,
                                         'bg-gray-300 text-gray-700': index === 1,
                                         'bg-amber-600 text-white': index === 2,
                                         'bg-gray-100 text-gray-500': index > 2
                                     }">
                                    <span x-show="index < 3"><i class="bi bi-trophy-fill text-xs"></i></span>
                                    <span x-show="index >= 3" x-text="index + 1"></span>
                                </div>
                                
                                <!-- CS Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 text-sm truncate" x-text="cs.nama_cs"></p>
                                    <p class="text-xs text-gray-400" x-text="cs.tim + ' • ' + cs.total_transaksi + ' trx'"></p>
                                </div>
                                
                                <!-- Amount -->
                                <div class="text-right">
                                    <p class="font-semibold text-sm" 
                                       :class="index === 0 ? 'text-primary-600' : 'text-gray-700'"
                                       x-text="'Rp ' + formatCompact(cs.total_perolehan)"></p>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Empty State -->
                        <div x-show="leaderboard.length === 0" class="py-8 text-center">
                            <i class="bi bi-emoji-neutral text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm text-gray-400">Belum ada data</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 3: SOURCE & TEAM BREAKDOWN   -->
            <!-- ===================================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Sumber Donasi (Hasil Dari) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <i class="bi bi-diagram-3-fill text-blue-500"></i>
                            Sumber Donasi
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <template x-for="(item, index) in sourceBreakdown.hasil_dari || []" :key="index">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full"
                                         :style="'background-color: ' + getSourceColor(index)"></div>
                                    <span class="text-sm text-gray-600" x-text="item.hasil_dari || '-'"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-800" x-text="formatCompact(item.total)"></span>
                            </div>
                        </template>
                        <div x-show="!sourceBreakdown.hasil_dari?.length" class="text-center py-4">
                            <p class="text-xs text-gray-400">Memuat data...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Program (Zakat) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <i class="bi bi-heart-fill text-green-500"></i>
                            Program Zakat
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <template x-for="(item, index) in sourceBreakdown.zakat || []" :key="index">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full"
                                         :style="'background-color: ' + getProgramColor(index)"></div>
                                    <span class="text-sm text-gray-600 truncate max-w-[100px]" x-text="item.zakat || '-'"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-800" x-text="formatCompact(item.total)"></span>
                            </div>
                        </template>
                        <div x-show="!sourceBreakdown.zakat?.length" class="text-center py-4">
                            <p class="text-xs text-gray-400">Memuat data...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Platform -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <i class="bi bi-phone-fill text-purple-500"></i>
                            Platform
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <template x-for="(item, index) in sourceBreakdown.platform || []" :key="index">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full"
                                         :style="'background-color: ' + getPlatformColor(index)"></div>
                                    <span class="text-sm text-gray-600" x-text="item.nama_platform || '-'"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-800" x-text="formatCompact(item.total)"></span>
                            </div>
                        </template>
                        <div x-show="!sourceBreakdown.platform?.length" class="text-center py-4">
                            <p class="text-xs text-gray-400">Memuat data...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Produk -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <i class="bi bi-box-fill text-amber-500"></i>
                            Produk Terlaris
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <template x-for="(item, index) in sourceBreakdown.produk || []" :key="index">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full"
                                         :style="'background-color: ' + getProductColor(index)"></div>
                                    <span class="text-sm text-gray-600 truncate max-w-[100px]" x-text="item.nama_produk || '-'"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-800" x-text="formatCompact(item.total)"></span>
                            </div>
                        </template>
                        <div x-show="!sourceBreakdown.produk?.length" class="text-center py-4">
                            <p class="text-xs text-gray-400">Memuat data...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 4: TEAM BREAKDOWN            -->
            <!-- ===================================== -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-people-fill text-primary-500"></i>
                            Performa Per Tim
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Perbandingan perolehan antar tim</p>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <template x-for="(team, index) in teamBreakdown" :key="index">
                            <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-4 border border-gray-100 hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2 py-1 bg-primary-100 text-primary-700 rounded-lg text-xs font-semibold" x-text="team.tim"></span>
                                    <span class="text-xs text-gray-400" x-text="team.active_cs + ' CS'"></span>
                                </div>
                                <p class="text-lg font-bold text-gray-800 mb-1" x-text="'Rp ' + formatCompact(team.total_perolehan)"></p>
                                <p class="text-xs text-gray-500">
                                    <span x-text="team.total_transaksi"></span> transaksi
                                </p>
                                <!-- Progress bar relative to top team -->
                                <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 rounded-full transition-all"
                                         :style="'width: ' + (teamBreakdown[0] ? (team.total_perolehan / teamBreakdown[0].total_perolehan * 100) : 0) + '%'"></div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Empty State -->
                        <div x-show="teamBreakdown.length === 0" class="col-span-full text-center py-8">
                            <i class="bi bi-people text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm text-gray-400">Memuat data tim...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 5: TREND & LIVE FEED ROW     -->
            <!-- ===================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Trend Comparison -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-graph-up text-indigo-500"></i>
                            Trend Comparison
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Perbandingan dengan kemarin</p>
                    </div>
                    <div class="p-5 space-y-4">
                        <!-- Today vs Yesterday -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Hari ini</span>
                                <span class="font-semibold text-primary-600" x-text="'Rp ' + formatCompact(trendComparison.today?.total || 0)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Kemarin (full day)</span>
                                <span class="font-medium text-gray-500" x-text="'Rp ' + formatCompact(trendComparison.yesterday?.total || 0)"></span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-700">Growth</span>
                                <span class="font-semibold"
                                      :class="(trendComparison.yesterday?.growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                                    <i :class="(trendComparison.yesterday?.growth || 0) >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                                    <span x-text="Math.abs(trendComparison.yesterday?.growth || 0) + '%'"></span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- MTD Comparison -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Month to Date</p>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600" x-text="trendComparison.mtd?.label || 'MTD Bulan Ini'"></span>
                                    <span class="font-semibold text-gray-800" x-text="'Rp ' + formatCompact(trendComparison.mtd?.total || 0)"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600" x-text="trendComparison.last_mtd?.label || 'MTD Bulan Lalu'"></span>
                                    <span class="font-medium text-gray-500" x-text="'Rp ' + formatCompact(trendComparison.last_mtd?.total || 0)"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    <span class="text-sm font-medium text-gray-700">Growth MTD</span>
                                    <span class="font-semibold"
                                          :class="(trendComparison.last_mtd?.growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                                        <i :class="(trendComparison.last_mtd?.growth || 0) >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                                        <span x-text="Math.abs(trendComparison.last_mtd?.growth || 0) + '%'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Live Feed -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-red-50 to-white">
                        <div>
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <span class="relative flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span>
                                </span>
                                <span class="ml-2">Live Feed</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Transaksi terbaru masuk</p>
                        </div>
                        <span class="text-xs text-gray-400" x-text="liveFeed.length + ' terbaru'"></span>
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                        <template x-for="(item, index) in liveFeed" :key="index">
                            <div class="feed-item flex items-start gap-3 px-5 py-3 hover:bg-gray-50 transition"
                                 :style="'animation-delay: ' + (index * 0.05) + 's'">
                                <!-- Time Badge -->
                                <div class="flex-shrink-0 w-20 text-center">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-medium" x-text="item.perolehan_jam || '-'"></span>
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-sm text-gray-800 truncate" x-text="item.nama_donatur || 'Anonymous'"></span>
                                        <span class="px-1.5 py-0.5 bg-primary-100 text-primary-700 rounded text-[10px] font-medium" x-text="item.source_label || item.hasil_dari || '-'"></span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        <i class="bi bi-headset mr-1"></i>
                                        <span x-text="item.nama_cs"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="item.tim"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="item.zakat || item.program_utama || '-'"></span>
                                    </p>
                                </div>
                                
                                <!-- Amount -->
                                <div class="flex-shrink-0 text-right">
                                    <p class="font-semibold text-primary-600" x-text="'Rp ' + formatNumber(item.jml_perolehan)"></p>
                                    <p class="text-xs text-gray-400" x-text="item.time_ago || '-'"></p>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Empty State -->
                        <div x-show="liveFeed.length === 0" class="py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="bi bi-inbox text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-sm text-gray-400 mb-1">Belum ada transaksi</p>
                            <p class="text-xs text-gray-300">Transaksi baru akan muncul secara realtime</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 6: EXPORT BUTTON             -->
            <!-- ===================================== -->
            <div class="flex justify-end">
                <button @click="exportToExcel()" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-xl font-medium text-sm transition shadow-sm shadow-primary-500/20">
                    <i class="bi bi-download"></i>
                    Export Excel
                </button>
            </div>
            
        </div>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-4 px-6">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <p>
                    <i class="bi bi-heart-fill text-primary-500 mr-1"></i>
                    LAZ AL BAHJAH WILAYAH BARAT &copy; {{ date('Y') }}
                </p>
                <p x-show="lastUpdated">
                    <i class="bi bi-clock mr-1"></i>
                    Update: <span x-text="lastUpdated"></span>
                </p>
            </div>
        </footer>
        
@push('scripts')
    <script>
        function laporanPerolehanApp() {
            return {
                // State
                sidebarOpen: false,
                isLoading: false,
                autoRefresh: true,
                autoRefreshInterval: null,
                selectedDate: '{{ $selectedDate }}',
                lastUpdated: null,
                hourlyChart: null,
                
                // Initial Loading
                initialLoading: {
                    show: true,
                    progress: 0,
                    status: 'Memulai...',
                    currentTip: 0,
                    tips: [
                        { icon: 'bi-clock', text: 'Data diupdate otomatis setiap 30 detik untuk monitoring realtime.' },
                        { icon: 'bi-trophy', text: 'Leaderboard CS membantu memotivasi tim dengan kompetisi sehat.' },
                        { icon: 'bi-graph-up', text: 'Chart per jam membantu identifikasi waktu terbaik untuk follow-up.' },
                        { icon: 'bi-lightning', text: 'Live feed menampilkan transaksi terbaru secara realtime.' },
                        { icon: 'bi-calendar-check', text: 'Gunakan date picker untuk melihat data hari sebelumnya.' },
                    ]
                },
                
                // Data
                stats: @json($initialStats),
                hourly: @json($initialHourly),
                leaderboard: @json($initialLeaderboard),
                liveFeed: @json($initialLiveFeed),
                sourceBreakdown: {},
                teamBreakdown: [],
                trendComparison: {},
                
                // Initialize
                async init() {
                    this.startTipRotation();
                    await this.loadAdditionalData();
                    await this.simulateLoading();
                    this.initHourlyChart();
                    this.startAutoRefresh();
                },
                
                startTipRotation() {
                    setInterval(() => {
                        if (this.initialLoading.show) {
                            this.initialLoading.currentTip = (this.initialLoading.currentTip + 1) % this.initialLoading.tips.length;
                        }
                    }, 3000);
                },
                
                async simulateLoading() {
                    const steps = [
                        { status: 'Memuat statistik hari ini...', progress: 25 },
                        { status: 'Memuat data per jam...', progress: 50 },
                        { status: 'Memuat leaderboard CS...', progress: 75 },
                        { status: 'Mempersiapkan live feed...', progress: 90 },
                    ];
                    
                    for (const step of steps) {
                        this.initialLoading.status = step.status;
                        this.initialLoading.progress = step.progress;
                        await new Promise(r => setTimeout(r, 300));
                    }
                    
                    this.initialLoading.progress = 100;
                    this.initialLoading.status = 'Siap! ✨';
                    this.updateLastUpdated();
                    
                    await new Promise(r => setTimeout(r, 400));
                    this.initialLoading.show = false;
                },
                
                // Auto Refresh
                startAutoRefresh() {
                    if (this.autoRefreshInterval) clearInterval(this.autoRefreshInterval);
                    if (this.autoRefresh) {
                        this.autoRefreshInterval = setInterval(() => {
                            this.loadAllData();
                        }, 30000); // 30 seconds
                    }
                },
                
                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                    this.startAutoRefresh();
                },
                
                // Load additional data (source, team, trend)
                async loadAdditionalData() {
                    try {
                        const params = new URLSearchParams({ tanggal: this.selectedDate });
                        
                        const [sourceRes, teamRes, trendRes] = await Promise.all([
                            fetch('/api/laporan-perolehan/source-breakdown?' + params),
                            fetch('/api/laporan-perolehan/team-breakdown?' + params),
                            fetch('/api/laporan-perolehan/trend-comparison?' + params),
                        ]);
                        
                        this.sourceBreakdown = await sourceRes.json();
                        this.teamBreakdown = await teamRes.json();
                        this.trendComparison = await trendRes.json();
                    } catch (error) {
                        console.error('Error loading additional data:', error);
                    }
                },
                
                // Load all data
                async loadAllData() {
                    this.isLoading = true;
                    
                    try {
                        const params = new URLSearchParams({ tanggal: this.selectedDate });
                        
                        const [statsRes, hourlyRes, leaderboardRes, liveFeedRes, sourceRes, teamRes, trendRes] = await Promise.all([
                            fetch('/api/laporan-perolehan/today-stats?' + params),
                            fetch('/api/laporan-perolehan/hourly-breakdown?' + params),
                            fetch('/api/laporan-perolehan/cs-leaderboard?' + params),
                            fetch('/api/laporan-perolehan/live-feed?' + params),
                            fetch('/api/laporan-perolehan/source-breakdown?' + params),
                            fetch('/api/laporan-perolehan/team-breakdown?' + params),
                            fetch('/api/laporan-perolehan/trend-comparison?' + params),
                        ]);
                        
                        this.stats = await statsRes.json();
                        this.hourly = await hourlyRes.json();
                        this.leaderboard = await leaderboardRes.json();
                        this.liveFeed = await liveFeedRes.json();
                        this.sourceBreakdown = await sourceRes.json();
                        this.teamBreakdown = await teamRes.json();
                        this.trendComparison = await trendRes.json();
                        
                        this.updateHourlyChart();
                        this.updateLastUpdated();
                    } catch (error) {
                        console.error('Error loading data:', error);
                    }
                    
                    this.isLoading = false;
                },
                
                updateLastUpdated() {
                    const now = new Date();
                    this.lastUpdated = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },
                
                // Chart
                initHourlyChart() {
                    const ctx = document.getElementById('hourlyChart');
                    if (!ctx) return;
                    
                    const chartData = this.hourly.data || [];
                    const labels = chartData.map(item => item.jam);
                    const values = chartData.map(item => item.total);
                    const peakIndex = values.indexOf(Math.max(...values));
                    
                    this.hourlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Perolehan',
                                data: values,
                                backgroundColor: values.map((_, i) => i === peakIndex ? '#059669' : '#10B981'),
                                borderColor: values.map((_, i) => i === peakIndex ? '#047857' : '#059669'),
                                borderWidth: 1,
                                borderRadius: 6,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1F2937',
                                    titleColor: '#F9FAFB',
                                    bodyColor: '#F9FAFB',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: (context) => 'Rp ' + this.formatNumber(context.raw)
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#F3F4F6' },
                                    ticks: {
                                        callback: (value) => this.formatCompact(value)
                                    }
                                },
                                x: {
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                },
                
                updateHourlyChart() {
                    if (!this.hourlyChart) return;
                    
                    const chartData = this.hourly.data || [];
                    const values = chartData.map(item => item.total);
                    const peakIndex = values.indexOf(Math.max(...values));
                    
                    this.hourlyChart.data.labels = chartData.map(item => item.jam);
                    this.hourlyChart.data.datasets[0].data = values;
                    this.hourlyChart.data.datasets[0].backgroundColor = values.map((_, i) => i === peakIndex ? '#059669' : '#10B981');
                    this.hourlyChart.update('none');
                },
                
                // Export
                async exportToExcel() {
                    const params = new URLSearchParams({ tanggal: this.selectedDate });
                    window.open('/api/laporan-perolehan/export?' + params, '_blank');
                },
                
                // Color Helpers
                getSourceColor(index) {
                    const colors = ['#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#EC4899'];
                    return colors[index % colors.length];
                },
                
                getProgramColor(index) {
                    const colors = ['#10B981', '#059669', '#047857', '#065F46', '#064E3B'];
                    return colors[index % colors.length];
                },
                
                getPlatformColor(index) {
                    const colors = ['#8B5CF6', '#7C3AED', '#6D28D9', '#5B21B6', '#4C1D95'];
                    return colors[index % colors.length];
                },
                
                getProductColor(index) {
                    const colors = ['#F59E0B', '#D97706', '#B45309', '#92400E', '#78350F'];
                    return colors[index % colors.length];
                },
                
                // Helpers
                formatNumber(num) {
                    if (!num) return '0';
                    return new Intl.NumberFormat('id-ID').format(num);
                },
                
                formatCompact(num) {
                    if (!num) return '0';
                    if (num >= 1000000000) return (num / 1000000000).toFixed(1) + 'M';
                    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'Jt';
                    if (num >= 1000) return (num / 1000).toFixed(0) + 'Rb';
                    return num.toString();
                }
            }
        }
    </script>
@endpush
</x-layouts.app>
