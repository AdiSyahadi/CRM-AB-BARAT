<x-layouts.app active="monitor-cs" title="Monitor CS - CRM Dashboard" xData="monitorCsApp()">

@push('styles')
<style>
    /* CS Card */
    .cs-card { transition: all 0.2s ease; }
    .cs-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

    /* Status colors */
    .status-sudah { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); border-color: #10B981; }
    .status-idle { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-color: #F59E0B; }
    .status-belum { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); border-color: #EF4444; }

    /* Timeline */
    .timeline-item { animation: slideIn 0.3s ease-out; }
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
                    <i class="bi bi-people-fill text-white text-4xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Monitor CS</h2>
            <p class="text-gray-500 mb-6">Menyiapkan data untuk Anda...</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500 ease-out"
                     :style="'width: ' + initialLoading.progress + '%'"></div>
            </div>
            
            <!-- Loading Status -->
            <p class="text-sm text-primary-600 font-medium mb-6" x-text="initialLoading.status">Memuat data...</p>
            
            <!-- Dots animation -->
            <div class="flex justify-center gap-1.5 mt-6">
                <template x-for="i in 3" :key="i">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce"
                         :style="'animation-delay: ' + (i * 0.15) + 's'"></div>
                </template>
            </div>
        </div>
    </div>
    
    <!-- CS Detail Modal -->
    <div x-show="showCsDetailModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showCsDetailModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] overflow-hidden" @click.stop>
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-primary-50 to-white flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-800" x-text="csDetail.nama_cs || 'Detail CS'"></h3>
                    <p class="text-sm text-gray-500" x-text="'Tim ' + (csDetail.tim || '-')"></p>
                </div>
                <button @click="showCsDetailModal = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <!-- Summary Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-3 bg-primary-50 rounded-xl">
                        <p class="text-2xl font-bold text-primary-600" x-text="csDetail.total_transaksi || 0"></p>
                        <p class="text-xs text-gray-500">Transaksi</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                        <p class="text-2xl font-bold text-blue-600" x-text="'Rp ' + formatCompact(csDetail.total_perolehan || 0)"></p>
                        <p class="text-xs text-gray-500">Perolehan</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-xl">
                        <p class="text-2xl font-bold text-purple-600" x-text="'Rp ' + formatCompact(csDetail.avg_per_transaksi || 0)"></p>
                        <p class="text-xs text-gray-500">Avg/Trx</p>
                    </div>
                </div>
                
                <!-- Per Jam Breakdown -->
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-clock text-primary-500"></i>
                        Breakdown Per Jam
                    </h4>
                    <div class="space-y-2">
                        <template x-for="jam in csDetail.per_jam || []" :key="jam.jam">
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-600" x-text="jam.jam"></span>
                                <div class="flex items-center gap-4">
                                    <span class="text-xs text-gray-400" x-text="jam.transaksi + ' trx'"></span>
                                    <span class="text-sm font-semibold text-primary-600" x-text="'Rp ' + formatNumber(jam.perolehan)"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!csDetail.per_jam?.length" class="text-center py-4 text-gray-400 text-sm">
                            Belum ada data
                        </div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-list-ul text-primary-500"></i>
                        Transaksi Hari Ini
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <template x-for="trx in csDetail.laporans || []" :key="trx.id">
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg text-sm">
                                <div>
                                    <p class="font-medium text-gray-700" x-text="trx.nama_donatur || 'Anonymous'"></p>
                                    <p class="text-xs text-gray-400" x-text="trx.perolehan_jam"></p>
                                </div>
                                <span class="font-semibold text-primary-600" x-text="'Rp ' + formatNumber(trx.jml_perolehan)"></span>
                            </div>
                        </template>
                        <div x-show="!csDetail.laporans?.length" class="text-center py-4 text-gray-400 text-sm">
                            Belum ada transaksi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
            <div class="flex items-center justify-between px-4 md:px-6 py-3">
                <!-- Left: Menu & Title -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg md:text-xl font-bold text-gray-800">Monitor CS</h1>
                            <span class="relative flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">
                                <span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span>
                                LIVE
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Tracking aktivitas CS realtime</p>
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
                            class="p-2 bg-primary-50 hover:bg-primary-100 text-primary-600 rounded-xl transition disabled:opacity-50">
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
            
            <!-- ===================================== -->
            <!-- SECTION 1: SUMMARY CARDS             -->
            <!-- ===================================== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total CS -->
                <div class="stat-card bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-people-fill text-blue-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-1">Total CS Terdaftar</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800" x-text="summary.total_cs"></p>
                </div>
                
                <!-- Sudah Laporan -->
                <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-4 md:p-5 text-white shadow-lg shadow-green-500/20">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="bi bi-check-circle-fill text-lg md:text-xl"></i>
                        </div>
                        <span class="px-2 py-1 bg-white/20 rounded-full text-xs font-bold" x-text="summary.percentage_sudah + '%'"></span>
                    </div>
                    <p class="text-white/70 text-xs md:text-sm mb-1">Sudah Laporan</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold" x-text="summary.sudah_laporan"></p>
                </div>
                
                <!-- Belum Laporan -->
                <div class="stat-card bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-4 md:p-5 text-white shadow-lg shadow-red-500/20">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="bi bi-x-circle-fill text-lg md:text-xl"></i>
                        </div>
                        <span class="px-2 py-1 bg-white/20 rounded-full text-xs font-bold" x-text="summary.percentage_belum + '%'"></span>
                    </div>
                    <p class="text-white/70 text-xs md:text-sm mb-1">Belum Laporan</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold" x-text="summary.belum_laporan"></p>
                </div>
                
                <!-- Unregistered CS (jika ada) -->
                <div class="stat-card bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-exclamation-triangle-fill text-amber-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-1">CS Tidak Terdaftar</p>
                    <p class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800" x-text="summary.unregistered_cs || 0"></p>
                    <p class="text-xs text-gray-400 mt-1">Laporan tanpa CS match</p>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 2: PER TIM SUMMARY           -->
            <!-- ===================================== -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-primary-500"></i>
                            Progress Per Tim
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Status laporan per tim</p>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <template x-for="(tim, index) in summary.per_tim || []" :key="index">
                            <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2 py-1 bg-primary-100 text-primary-700 rounded-lg text-xs font-semibold" x-text="tim.tim"></span>
                                    <span class="text-xs font-bold" 
                                          :class="tim.percentage >= 80 ? 'text-green-600' : tim.percentage >= 50 ? 'text-amber-600' : 'text-red-600'"
                                          x-text="tim.percentage + '%'"></span>
                                </div>
                                <div class="flex items-center gap-2 text-sm mb-2">
                                    <span class="text-green-600 font-medium" x-text="tim.sudah"></span>
                                    <span class="text-gray-400">/</span>
                                    <span class="text-gray-600" x-text="tim.total"></span>
                                </div>
                                <!-- Progress bar -->
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         :class="tim.percentage >= 80 ? 'bg-green-500' : tim.percentage >= 50 ? 'bg-amber-500' : 'bg-red-500'"
                                         :style="'width: ' + tim.percentage + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- ===================================== -->
            <!-- SECTION 3: FILTERS & CS LIST         -->
            <!-- ===================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- CS List (2/3 width) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Filters -->
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Status Filter -->
                            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                                <button @click="filters.status = 'all'; applyFilters()" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                        :class="filters.status === 'all' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    Semua
                                </button>
                                <button @click="filters.status = 'belum'; applyFilters()" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                        :class="filters.status === 'belum' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    ðŸ”´ Belum
                                </button>
                                <button @click="filters.status = 'idle'; applyFilters()" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                        :class="filters.status === 'idle' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    ðŸŸ¡ Idle
                                </button>
                                <button @click="filters.status = 'sudah'; applyFilters()" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                        :class="filters.status === 'sudah' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    ðŸŸ¢ Aktif
                                </button>
                            </div>
                            
                            <!-- Tim Filter -->
                            <select x-model="filters.tim" @change="applyFilters()"
                                    class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="all">Semua Tim</option>
                                @foreach($timList as $tim)
                                    <option value="{{ $tim }}">{{ $tim }}</option>
                                @endforeach
                            </select>
                            
                            <!-- Sort -->
                            <select x-model="filters.sort" @change="applyFilters()"
                                    class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="status">Sort: Status</option>
                                <option value="perolehan">Sort: Perolehan</option>
                                <option value="nama">Sort: Nama</option>
                                <option value="tim">Sort: Tim</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- CS List -->
                    <div class="max-h-[500px] overflow-y-auto">
                        <template x-for="(cs, index) in csList" :key="cs.id">
                            <div @click="openCsDetail(cs.nama_cs)" 
                                 class="cs-card flex items-center gap-4 px-5 py-4 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition">
                                
                                <!-- Status Indicator -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                                     :class="{
                                         'bg-green-100 text-green-600': cs.status === 'sudah',
                                         'bg-amber-100 text-amber-600': cs.status === 'idle',
                                         'bg-red-100 text-red-600': cs.status === 'belum'
                                     }">
                                    <i :class="{
                                        'bi bi-check-lg': cs.status === 'sudah',
                                        'bi bi-clock': cs.status === 'idle',
                                        'bi bi-x-lg': cs.status === 'belum'
                                    }"></i>
                                </div>
                                
                                <!-- CS Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-medium text-gray-800 truncate" x-text="cs.nama_cs"></p>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-medium" x-text="cs.tim"></span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        <template x-if="cs.status !== 'belum'">
                                            <span>
                                                <i class="bi bi-clock mr-1"></i>
                                                <span x-text="cs.last_activity"></span>
                                                <span class="mx-1">â€¢</span>
                                                <span x-text="cs.total_transaksi + ' trx'"></span>
                                            </span>
                                        </template>
                                        <template x-if="cs.status === 'belum'">
                                            <span class="text-red-500 font-medium">Belum ada aktivitas hari ini</span>
                                        </template>
                                    </p>
                                </div>
                                
                                <!-- Stats -->
                                <div class="flex-shrink-0 text-right">
                                    <p class="font-semibold"
                                       :class="cs.total_perolehan > 0 ? 'text-primary-600' : 'text-gray-400'"
                                       x-text="cs.total_perolehan > 0 ? 'Rp ' + formatCompact(cs.total_perolehan) : '-'"></p>
                                    <p class="text-xs"
                                       :class="{
                                           'text-green-500': cs.status === 'sudah',
                                           'text-amber-500': cs.status === 'idle',
                                           'text-red-500': cs.status === 'belum'
                                       }"
                                       x-text="cs.status_label"></p>
                                </div>
                                
                                <!-- Arrow -->
                                <i class="bi bi-chevron-right text-gray-300"></i>
                            </div>
                        </template>
                        
                        <!-- Empty State -->
                        <div x-show="csList.length === 0" class="py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="bi bi-people text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-sm text-gray-400">Tidak ada CS ditemukan</p>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Timeline (1/3 width) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-white">
                        <div>
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <span class="relative flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full live-dot"></span>
                                </span>
                                <span class="ml-2">Aktivitas Terbaru</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Laporan masuk realtime</p>
                        </div>
                    </div>
                    <div class="max-h-[500px] overflow-y-auto">
                        <template x-for="(activity, index) in timeline" :key="activity.id">
                            <div class="timeline-item px-5 py-3 border-b border-gray-50 hover:bg-gray-50 transition"
                                 :style="'animation-delay: ' + (index * 0.05) + 's'">
                                <div class="flex items-start gap-3">
                                    <!-- Time -->
                                    <div class="flex-shrink-0 text-center">
                                        <span class="text-xs font-bold text-primary-600" x-text="activity.time_formatted"></span>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800" x-text="activity.nama_cs"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="activity.nama_donatur || 'Anonymous'"></p>
                                        <p class="text-xs text-primary-600 font-semibold mt-1" x-text="'Rp ' + formatNumber(activity.jml_perolehan)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Empty State -->
                        <div x-show="timeline.length === 0" class="py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="bi bi-clock-history text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-sm text-gray-400">Belum ada aktivitas</p>
                        </div>
                    </div>
                </div>
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
        function monitorCsApp() {
            return {
                // State
                sidebarOpen: false,
                isLoading: false,
                autoRefresh: true,
                autoRefreshInterval: null,
                selectedDate: '{{ $selectedDate }}',
                lastUpdated: null,
                showCsDetailModal: false,
                
                // Initial Loading
                initialLoading: {
                    show: true,
                    progress: 0,
                    status: 'Memulai...',
                },
                
                // Filters
                filters: {
                    status: 'all',
                    tim: 'all',
                    sort: 'status',
                },
                
                // Data
                summary: @json($initialSummary),
                csList: @json($initialCsList),
                timeline: @json($initialTimeline),
                csDetail: {},
                
                // Initialize
                async init() {
                    await this.simulateLoading();
                    this.startAutoRefresh();
                },
                
                async simulateLoading() {
                    const steps = [
                        { status: 'Memuat data CS...', progress: 30 },
                        { status: 'Mengecek status laporan...', progress: 60 },
                        { status: 'Menyiapkan timeline...', progress: 90 },
                    ];
                    
                    for (const step of steps) {
                        this.initialLoading.status = step.status;
                        this.initialLoading.progress = step.progress;
                        await new Promise(r => setTimeout(r, 250));
                    }
                    
                    this.initialLoading.progress = 100;
                    this.initialLoading.status = 'Siap! âœ¨';
                    this.updateLastUpdated();
                    
                    await new Promise(r => setTimeout(r, 300));
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
                
                // Load all data
                async loadAllData() {
                    this.isLoading = true;
                    
                    try {
                        const params = new URLSearchParams({ 
                            tanggal: this.selectedDate,
                            tim: this.filters.tim,
                            status: this.filters.status,
                            sort: this.filters.sort,
                        });
                        
                        const [summaryRes, csListRes, timelineRes] = await Promise.all([
                            fetch('/api/monitor-cs/cs-status-summary?tanggal=' + this.selectedDate),
                            fetch('/api/monitor-cs/cs-list-status?' + params),
                            fetch('/api/monitor-cs/activity-timeline?tanggal=' + this.selectedDate),
                        ]);
                        
                        this.summary = await summaryRes.json();
                        this.csList = await csListRes.json();
                        this.timeline = await timelineRes.json();
                        
                        this.updateLastUpdated();
                    } catch (error) {
                        console.error('Error loading data:', error);
                    }
                    
                    this.isLoading = false;
                },
                
                // Apply filters
                async applyFilters() {
                    this.isLoading = true;
                    
                    try {
                        const params = new URLSearchParams({ 
                            tanggal: this.selectedDate,
                            tim: this.filters.tim,
                            status: this.filters.status,
                            sort: this.filters.sort,
                        });
                        
                        const res = await fetch('/api/monitor-cs/cs-list-status?' + params);
                        this.csList = await res.json();
                    } catch (error) {
                        console.error('Error applying filters:', error);
                    }
                    
                    this.isLoading = false;
                },
                
                // Open CS detail modal
                async openCsDetail(namaCs) {
                    try {
                        const params = new URLSearchParams({ 
                            tanggal: this.selectedDate,
                            nama_cs: namaCs,
                        });
                        
                        const res = await fetch('/api/monitor-cs/cs-detail?' + params);
                        this.csDetail = await res.json();
                        this.showCsDetailModal = true;
                    } catch (error) {
                        console.error('Error loading CS detail:', error);
                    }
                },
                
                updateLastUpdated() {
                    const now = new Date();
                    this.lastUpdated = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
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
