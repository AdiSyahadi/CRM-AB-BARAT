<x-layouts.app active="performa-cs" title="Performa CS - CRM Dashboard" xData="performaCsApp()" :chartjs="true">

@push('before-sidebar')
        <!-- Loading Modal - Light Mode -->
        <div x-show="loading" x-cloak class="loading-overlay">
            <div class="loading-modal">
                <div class="loading-spinner"></div>
                <p class="text-gray-700 font-medium">Memuat data...</p>
            </div>
        </div>

        <!-- CS Detail Modal -->
        <div x-show="showCsDetailModal" x-cloak 
             class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
             @click.self="showCsDetailModal = false">
            <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl relative" @click.stop>
                <!-- Loading Overlay -->
                <div x-show="loadingCsDetail" x-transition.opacity class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-20 rounded-2xl">
                    <div class="flex flex-col items-center gap-3 text-primary-600">
                        <svg class="animate-spin h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="font-medium">Memuat detail CS...</span>
                    </div>
                </div>
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800" x-text="csDetail?.cs?.name || 'Detail CS'"></h3>
                        <p class="text-sm text-gray-500" x-text="csDetail?.cs?.team || ''"></p>
                    </div>
                    <button @click="showCsDetailModal = false" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition" aria-label="Tutup">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-4 overflow-y-auto flex-1 bg-gray-50">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                            <p class="text-2xl font-bold text-primary-600" x-text="csDetail?.summary?.total_perolehan_formatted || 'Rp 0'"></p>
                            <p class="text-xs text-gray-500">Total Perolehan</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                            <p class="text-2xl font-bold text-blue-600" x-text="csDetail?.summary?.total_donatur || 0"></p>
                            <p class="text-xs text-gray-500">Total Donatur</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                            <p class="text-2xl font-bold text-purple-600" x-text="csDetail?.summary?.total_laporan || 0"></p>
                            <p class="text-xs text-gray-500">Total Laporan</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                            <p class="text-2xl font-bold text-amber-600" x-text="csDetail?.summary?.active_days || 0"></p>
                            <p class="text-xs text-gray-500">Hari Aktif</p>
                        </div>
                    </div>
                    
                    <!-- Trend Chart - Full Width -->
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                        <h4 class="font-semibold mb-3 text-gray-800"><i class="bi bi-graph-up text-primary-500 mr-2"></i>Trend Perolehan</h4>
                        <div style="position:relative;height:200px;">
                            <canvas id="csTrendChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="mt-4 bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                        <h4 class="font-semibold mb-3 text-gray-800"><i class="bi bi-clock-history text-purple-500 mr-2"></i>Aktivitas Terbaru</h4>
                        <div class="space-y-2 max-h-[200px] overflow-y-auto">
                            <template x-for="activity in (csDetail?.recent_activity || [])" :key="activity.created_at">
                                <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">
                                    <div>
                                        <span class="text-gray-700" x-text="activity.nama_donatur || 'Anonymous'"></span>
                                        <span class="text-gray-400 text-xs ml-2" x-text="activity.program || '-'"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-primary-600 font-medium" x-text="formatRupiah(activity.jml_perolehan)"></span>
                                        <span class="text-gray-400 text-xs block" x-text="formatDate(activity.tanggal)"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!csDetail?.recent_activity?.length">
                                <p class="text-gray-400 text-sm">Tidak ada aktivitas</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endpush

    <!-- Main Content -->
    <div x-init="init()" class="overflow-x-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 shadow-sm sticky top-0 z-40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 hover:text-primary-600 p-2 rounded-lg hover:bg-gray-100 transition" aria-label="Toggle menu">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                        <div>
                            <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Dashboard Performa CS</h2>
                            <p class="text-sm text-gray-500">Analisis performa Customer Service secara komprehensif</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Global Filters -->
                        <select x-model="filters.periode" @change="refreshAll()" 
                                class="bg-white border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                            <option value="hari_ini">Hari Ini</option>
                            <option value="minggu_ini">Minggu Ini</option>
                            <option value="bulan_ini">Bulan Ini</option>
                        </select>
                        <select x-model="filters.tim" @change="refreshAll()"
                                class="bg-white border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700 hidden sm:block">
                            <option value="all">Semua Tim</option>
                            @foreach($teams as $team)
                                <option value="{{ $team }}">{{ $team }}</option>
                            @endforeach
                        </select>
                        <button @click="refreshAll()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-xl transition hidden sm:flex items-center shadow-lg shadow-primary-500/30">
                            <i class="bi bi-arrow-clockwise mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-4 lg:p-6 space-y-6">
                
                <!-- Section 1: Overview Cards -->
                <section class="relative">
                    <!-- Loading Overlay -->
                    <div x-show="loadingOverview" x-transition.opacity class="absolute inset-0 bg-white/70 backdrop-blur-sm rounded-xl flex items-center justify-center z-10">
                        <div class="flex items-center gap-3 text-primary-600">
                            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="font-medium">Memuat data...</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-800">
                        <i class="bi bi-speedometer2 text-primary-500 mr-2"></i>Overview Performa
                        <span class="ml-2 text-sm font-normal text-gray-500" x-text="'(' + overview.periode_label + ')'"></span>
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Total Perolehan -->
                        <div class="stat-card bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-500 text-sm">Total Perolehan</span>
                                <span :class="overview.perolehan_growth >= 0 ? 'text-primary-600' : 'text-red-500'" class="text-xs font-medium">
                                    <i :class="overview.perolehan_growth >= 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    <span x-text="Math.abs(overview.perolehan_growth) + '%'"></span>
                                </span>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-primary-600" x-text="overview.total_perolehan_formatted || 'Rp 0'"></p>
                        </div>
                        
                        <!-- Total Donatur -->
                        <div class="stat-card bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-500 text-sm">Total Donatur</span>
                                <span :class="overview.donatur_growth >= 0 ? 'text-primary-600' : 'text-red-500'" class="text-xs font-medium">
                                    <i :class="overview.donatur_growth >= 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    <span x-text="Math.abs(overview.donatur_growth) + '%'"></span>
                                </span>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-blue-600" x-text="overview.total_donatur || 0"></p>
                        </div>
                        
                        <!-- Donatur Baru -->
                        <div class="stat-card bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-500 text-sm">Donatur Baru</span>
                                <i class="bi bi-person-plus-fill text-purple-500"></i>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-purple-600" x-text="overview.donatur_baru || 0"></p>
                        </div>
                        
                        <!-- Avg Laporan/Hari -->
                        <div class="stat-card bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-500 text-sm">Avg/Hari</span>
                                <i class="bi bi-graph-up text-amber-500"></i>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-amber-600" x-text="overview.avg_laporan_per_day || 0"></p>
                        </div>
                        
                        <!-- Active CS -->
                        <div class="stat-card bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-500 text-sm">Active CS</span>
                                <i class="bi bi-people-fill text-cyan-500"></i>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-cyan-600">
                                <span x-text="overview.active_cs || 0"></span>
                                <span class="text-lg text-gray-400">/<span x-text="overview.total_cs || 0"></span></span>
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Section 2 & 3: H2H + Leaderboard -->
                <div class="grid lg:grid-cols-3 gap-6">
                    
                    <!-- Section 2: Head-to-Head Comparison -->
                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">
                        <!-- Loading Overlay -->
                        <div x-show="loadingH2h" x-transition.opacity class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10">
                            <div class="flex items-center gap-3 text-primary-600">
                                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium">Memuat data...</span>
                            </div>
                        </div>
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-white">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h3 class="font-semibold flex items-center text-gray-800">
                                    <i class="bi bi-arrow-left-right text-primary-500 mr-2"></i>Head-to-Head Comparison
                                </h3>
                                <!-- Mode Tabs -->
                                <div class="flex bg-gray-100 rounded-lg p-1">
                                    <button @click="changeH2hMode('harian')" 
                                            :class="h2h.mode === 'harian' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                                            class="px-3 py-1 text-sm rounded-md transition font-medium">Harian</button>
                                    <button @click="changeH2hMode('mingguan')" 
                                            :class="h2h.mode === 'mingguan' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                                            class="px-3 py-1 text-sm rounded-md transition font-medium">Mingguan</button>
                                    <button @click="changeH2hMode('bulanan')" 
                                            :class="h2h.mode === 'bulanan' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                                            class="px-3 py-1 text-sm rounded-md transition font-medium">Bulanan</button>
                                </div>
                            </div>
                            
                            <!-- Filters Row -->
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <!-- Harian Filter -->
                                <div x-show="h2h.mode === 'harian'" class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Tahun Ini:</label>
                                        <input type="date" x-model="h2h.periode1" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                    <span class="text-primary-500 font-bold">vs</span>
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Tahun Lalu:</label>
                                        <input type="date" x-model="h2h.periode2" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                </div>
                                
                                <!-- Mingguan Filter -->
                                <div x-show="h2h.mode === 'mingguan'" class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Minggu 1:</label>
                                        <input type="week" x-model="h2h.periode1" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                    <span class="text-primary-500 font-bold">vs</span>
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Minggu 2:</label>
                                        <input type="week" x-model="h2h.periode2" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                </div>
                                
                                <!-- Bulanan Filter -->
                                <div x-show="h2h.mode === 'bulanan'" class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Bulan Ini:</label>
                                        <input type="month" x-model="h2h.periode1" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                    <span class="text-primary-500 font-bold">vs</span>
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500">Bulan Lalu:</label>
                                        <input type="month" x-model="h2h.periode2" @change="fetchH2hData()"
                                               class="bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 ml-auto">
                                    <select x-model="h2h.viewBy" @change="fetchH2hData()"
                                            class="bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700">
                                        <option value="cs">Per CS</option>
                                        <option value="tim">Per Tim</option>
                                    </select>
                                    
                                    <button @click="exportH2h()" class="bg-primary-50 text-primary-600 hover:bg-primary-100 px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1">
                                        <i class="bi bi-download"></i>Export
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Summary -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-gray-50 border-b border-gray-200">
                            <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1 font-medium" x-text="h2h.labels?.periode1 || 'Periode 1'"></p>
                                <p class="text-lg font-bold text-primary-600" x-text="h2h.summary?.total_periode1_formatted || 'Rp 0'"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1 font-medium" x-text="h2h.labels?.periode2 || 'Periode 2'"></p>
                                <p class="text-lg font-bold text-gray-600" x-text="h2h.summary?.total_periode2_formatted || 'Rp 0'"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1 font-medium">Selisih</p>
                                <p class="text-lg font-bold" :class="h2h.summary?.total_diff >= 0 ? 'text-primary-600' : 'text-red-500'"
                                   x-text="h2h.summary?.total_diff_formatted || 'Rp 0'"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1 font-medium">Avg Growth</p>
                                <p class="text-lg font-bold" :class="h2h.summary?.avg_growth >= 0 ? 'text-primary-600' : 'text-red-500'"
                                   x-text="(h2h.summary?.avg_growth >= 0 ? '+' : '') + (h2h.summary?.avg_growth || 0) + '%'"></p>
                            </div>
                        </div>
                        
                        <!-- Chart -->
                        <div class="p-4 border-b border-gray-200 bg-white" style="position: relative; height: 280px;">
                            <canvas id="h2hChart"></canvas>
                        </div>
                        
                        <!-- Table -->
                        <div class="max-h-[300px] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="text-left p-3 text-gray-700 font-semibold border-b border-gray-200">Nama</th>
                                        <th class="text-right p-3 text-gray-700 font-semibold border-b border-gray-200 whitespace-nowrap" x-text="h2h.labels?.periode1 || 'Periode 1'"></th>
                                        <th class="text-right p-3 text-gray-700 font-semibold border-b border-gray-200 whitespace-nowrap" x-text="h2h.labels?.periode2 || 'Periode 2'"></th>
                                        <th class="text-right p-3 text-gray-700 font-semibold border-b border-gray-200">Selisih</th>
                                        <th class="text-right p-3 text-gray-700 font-semibold border-b border-gray-200">Growth</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, index) in h2h.comparison" :key="item.name">
                                        <tr class="hover:bg-primary-50/70 cursor-pointer transition-colors" @click="openCsDetail(item.name)">
                                            <td class="p-3 text-gray-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold" x-text="index + 1"></span>
                                                    <div>
                                                        <span x-text="item.name" class="font-medium"></span>
                                                        <span x-show="item.team" class="text-xs text-gray-400 block" x-text="item.team"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-3 text-right">
                                                <span class="text-primary-600 font-semibold" x-text="item.periode1_formatted"></span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <span class="text-gray-600" x-text="item.periode2_formatted"></span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <span class="font-semibold" :class="item.diff >= 0 ? 'text-primary-600' : 'text-red-500'" x-text="item.diff_formatted"></span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                                      :class="{
                                                          'bg-primary-100 text-primary-700': item.status === 'up',
                                                          'bg-red-100 text-red-700': item.status === 'down',
                                                          'bg-gray-100 text-gray-600': item.status === 'stable'
                                                      }">
                                                    <i :class="{
                                                        'bi bi-arrow-up-short': item.status === 'up',
                                                        'bi bi-arrow-down-short': item.status === 'down',
                                                        'bi bi-dash': item.status === 'stable'
                                                    }"></i>
                                                    <span x-text="item.growth + '%'"></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!h2h.comparison?.length">
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-gray-400">
                                                <i class="bi bi-inbox text-3xl mb-2 block"></i>
                                                <span>Tidak ada data untuk periode ini</span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Section 3: Leaderboard -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">
                        <!-- Loading Overlay -->
                        <div x-show="loadingLeaderboard" x-transition.opacity class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10">
                            <div class="flex flex-col items-center gap-2 text-primary-600">
                                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium">Memuat...</span>
                            </div>
                        </div>
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-white">
                            <h3 class="font-semibold flex items-center text-gray-800">
                                <i class="bi bi-trophy-fill text-amber-500 mr-2"></i>Leaderboard
                            </h3>
                            <!-- Leaderboard Tabs -->
                            <div class="flex flex-wrap gap-1 mt-3">
                                <button @click="leaderboard.type = 'top_earners'; fetchLeaderboard()"
                                        :class="leaderboard.type === 'top_earners' ? 'bg-primary-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs rounded-md transition font-medium">💰 Top Earners</button>
                                <button @click="leaderboard.type = 'most_improved'; fetchLeaderboard()"
                                        :class="leaderboard.type === 'most_improved' ? 'bg-primary-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs rounded-md transition font-medium">🚀 Most Improved</button>
                                <button @click="leaderboard.type = 'most_productive'; fetchLeaderboard()"
                                        :class="leaderboard.type === 'most_productive' ? 'bg-primary-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs rounded-md transition font-medium">📊 Productive</button>
                                <button @click="leaderboard.type = 'consistency'; fetchLeaderboard()"
                                        :class="leaderboard.type === 'consistency' ? 'bg-primary-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs rounded-md transition font-medium">⭐ Consistency</button>
                            </div>
                        </div>
                        
                        <div class="p-4 space-y-2 max-h-[500px] overflow-y-auto">
                            <template x-for="(item, index) in leaderboard.data" :key="item.name">
                                <div class="flex items-center bg-gray-50 rounded-lg p-3 hover:bg-primary-50 cursor-pointer transition border border-gray-100"
                                     @click="openCsDetail(item.name)">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                         :class="{
                                             'bg-amber-500 text-black': item.rank === 1,
                                             'bg-gray-400 text-black': item.rank === 2,
                                             'bg-amber-700 text-white': item.rank === 3,
                                             'bg-gray-200 text-gray-600': item.rank > 3
                                         }">
                                        <span class="font-bold text-sm" x-text="item.rank"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate text-gray-800" x-text="item.name"></p>
                                        <p class="text-xs text-gray-400" x-text="item.team || '-'"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-primary-600" x-text="item.value_formatted"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!leaderboard.data?.length">
                                <p class="text-center text-gray-400 py-4">Tidak ada data</p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Section 4 & 5: CS List + Insights -->
                <div class="grid lg:grid-cols-3 gap-6">
                    
                    <!-- Section 4: CS List -->
                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">
                        <!-- Loading Overlay -->
                        <div x-show="loadingCsList" x-transition.opacity class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10">
                            <div class="flex items-center gap-3 text-primary-600">
                                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium">Memuat data...</span>
                            </div>
                        </div>
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold flex items-center text-gray-800">
                                    <i class="bi bi-people-fill text-blue-500 mr-2"></i>Daftar CS
                                </h3>
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <input type="text" x-model="csList.search" @input.debounce.300ms="fetchCsList()"
                                               placeholder="Cari CS..."
                                               class="bg-white border border-gray-300 rounded-lg pl-8 pr-3 py-1.5 text-sm w-40 focus:ring-2 focus:ring-primary-500 text-gray-700">
                                        <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid sm:grid-cols-2 gap-3 p-4 max-h-[400px] overflow-y-auto">
                            <template x-for="cs in csList.data" :key="cs.id">
                                <div class="bg-gray-50 rounded-lg p-3 hover:bg-primary-50 cursor-pointer transition border border-gray-100"
                                     @click="openCsDetail(cs.name, cs.id)">
                                    <div class="flex items-center mb-2">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center mr-3 shadow-lg shadow-primary-500/30">
                                            <span class="font-bold text-white" x-text="cs.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate text-gray-800" x-text="cs.name"></p>
                                            <p class="text-xs text-gray-400" x-text="cs.team || '-'"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                        <div>
                                            <p class="text-primary-600 font-bold" x-text="cs.total_perolehan_formatted"></p>
                                            <p class="text-gray-400">Perolehan</p>
                                        </div>
                                        <div>
                                            <p class="text-blue-600 font-bold" x-text="cs.total_donatur"></p>
                                            <p class="text-gray-400">Donatur</p>
                                        </div>
                                        <div>
                                            <p class="text-purple-600 font-bold" x-text="cs.total_laporan"></p>
                                            <p class="text-gray-400">Laporan</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!csList.data?.length">
                                <div class="col-span-2 text-center text-gray-400 py-8">
                                    <i class="bi bi-people text-4xl mb-2"></i>
                                    <p>Tidak ada data CS</p>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Section 5: Insights & Alerts -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">
                        <!-- Loading Overlay -->
                        <div x-show="loadingInsights" x-transition.opacity class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10">
                            <div class="flex flex-col items-center gap-2 text-primary-600">
                                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium">Memuat...</span>
                            </div>
                        </div>
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-white">
                            <h3 class="font-semibold flex items-center text-gray-800">
                                <i class="bi bi-lightbulb-fill text-yellow-500 mr-2"></i>Insights & Alerts
                            </h3>
                        </div>
                        
                        <div class="p-4 space-y-4 max-h-[400px] overflow-y-auto">
                            <!-- Alerts -->
                            <template x-for="alert in insights.alerts" :key="alert.type">
                                <div class="rounded-lg p-3"
                                     :class="{
                                         'bg-red-50 border border-red-200': alert.severity === 'danger',
                                         'bg-yellow-50 border border-yellow-200': alert.severity === 'warning',
                                         'bg-primary-50 border border-primary-200': alert.severity === 'success'
                                     }">
                                    <p class="font-medium text-sm mb-2"
                                       :class="{
                                           'text-red-600': alert.severity === 'danger',
                                           'text-yellow-600': alert.severity === 'warning',
                                           'text-primary-600': alert.severity === 'success'
                                       }"
                                       x-text="alert.title"></p>
                                    <div class="space-y-1">
                                        <template x-for="item in alert.data" :key="item.name">
                                            <div class="text-xs flex items-center justify-between text-gray-600 hover:bg-white/50 rounded px-1 cursor-pointer transition"
                                                 @click="openCsDetail(item.name)">
                                                <span x-text="item.name"></span>
                                                <span x-text="item.change ? item.change + '%' : item.last_report" class="font-medium"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Best Practices -->
                            <div x-show="insights.insights?.length" class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                <p class="font-medium text-sm text-blue-600 mb-2">💡 Best Practices</p>
                                <div class="space-y-2">
                                    <template x-for="insight in insights.insights" :key="insight.type">
                                        <p class="text-xs text-gray-600">
                                            <span x-text="insight.icon" class="mr-1"></span>
                                            <span x-text="insight.text"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                            
                            <template x-if="!insights.alerts?.length && !insights.insights?.length">
                                <div class="text-center text-gray-400 py-4">
                                    <i class="bi bi-check-circle text-2xl mb-2 text-primary-500"></i>
                                    <p class="text-sm">Tidak ada alert saat ini</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
    </div>

@push('scripts')
    <script>
        function performaCsApp() {
            return {
                loading: false,
                sidebarOpen: false,
                showCsDetailModal: false,
                lastUpdate: '-',
                
                // Loading states per section
                loadingOverview: false,
                loadingH2h: false,
                loadingLeaderboard: false,
                loadingCsList: false,
                loadingInsights: false,
                loadingCsDetail: false,
                
                filters: {
                    periode: 'bulan_ini',
                    tim: 'all',
                },
                
                overview: {},
                
                h2h: {
                    mode: 'bulanan',
                    periode1: '',
                    periode2: '',
                    viewBy: 'cs',
                    comparison: [],
                    summary: {},
                    labels: {},
                },
                
                leaderboard: {
                    type: 'top_earners',
                    data: [],
                },
                
                csList: {
                    search: '',
                    data: [],
                },
                
                insights: {
                    alerts: [],
                    insights: [],
                },
                
                csDetail: null,
                h2hChart: null,
                csTrendChart: null,
                
                init() {
                    // Set default periode untuk H2H berdasarkan mode
                    this.setDefaultH2hPeriode();
                    this.refreshAll();
                },
                
                setDefaultH2hPeriode() {
                    const now = new Date();
                    const lastYear = new Date(now);
                    lastYear.setFullYear(lastYear.getFullYear() - 1);
                    
                    if (this.h2h.mode === 'bulanan') {
                        // Format: 2025-12
                        this.h2h.periode1 = now.toISOString().slice(0, 7);
                        this.h2h.periode2 = lastYear.toISOString().slice(0, 7);
                    } else if (this.h2h.mode === 'harian') {
                        // Format: 2025-12-04
                        this.h2h.periode1 = now.toISOString().slice(0, 10);
                        this.h2h.periode2 = lastYear.toISOString().slice(0, 10);
                    } else if (this.h2h.mode === 'mingguan') {
                        // Format: 2025-W49
                        const getWeekNumber = (d) => {
                            const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
                            date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
                            const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
                            const weekNo = Math.ceil((((date - yearStart) / 86400000) + 1) / 7);
                            return `${date.getUTCFullYear()}-W${String(weekNo).padStart(2, '0')}`;
                        };
                        this.h2h.periode1 = getWeekNumber(now);
                        this.h2h.periode2 = getWeekNumber(lastYear);
                    }
                },
                
                changeH2hMode(newMode) {
                    this.h2h.mode = newMode;
                    this.setDefaultH2hPeriode();
                    this.fetchH2hData();
                },
                
                async refreshAll() {
                    this.loading = true;
                    try {
                        await Promise.allSettled([
                            this.fetchOverview(),
                            this.fetchH2hData(),
                            this.fetchLeaderboard(),
                            this.fetchCsList(),
                            this.fetchInsights(),
                        ]);
                        this.lastUpdate = new Date().toLocaleTimeString('id-ID');
                    } catch (e) {
                        console.error('refreshAll error:', e);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async fetchOverview() {
                    this.loadingOverview = true;
                    try {
                        const params = new URLSearchParams({
                            periode: this.filters.periode,
                            tim: this.filters.tim,
                        });
                        const res = await fetch(`/api/performa-cs/overview-summary?${params}`);
                        const json = await res.json();
                        if (json.success) this.overview = json.data;
                    } catch (e) { console.error(e); }
                    this.loadingOverview = false;
                },
                
                async fetchH2hData() {
                    this.loadingH2h = true;
                    try {
                        const params = new URLSearchParams({
                            mode: this.h2h.mode,
                            periode1: this.h2h.periode1,
                            periode2: this.h2h.periode2,
                            tim: this.filters.tim,
                            view_by: this.h2h.viewBy,
                        });
                        const res = await fetch(`/api/performa-cs/h2h-comparison?${params}`);
                        const json = await res.json();
                        if (json.success) {
                            this.h2h.comparison = json.data.comparison;
                            this.h2h.summary = json.data.summary;
                            this.h2h.labels = json.data.labels;
                            this.renderH2hChart();
                        }
                    } catch (e) { console.error(e); }
                    this.loadingH2h = false;
                },
                
                async fetchLeaderboard() {
                    this.loadingLeaderboard = true;
                    try {
                        const params = new URLSearchParams({
                            type: this.leaderboard.type,
                            periode: this.filters.periode,
                            tim: this.filters.tim,
                            limit: 10,
                        });
                        const res = await fetch(`/api/performa-cs/leaderboard?${params}`);
                        const json = await res.json();
                        if (json.success) this.leaderboard.data = json.data;
                    } catch (e) { console.error(e); }
                    this.loadingLeaderboard = false;
                },
                
                async fetchCsList() {
                    this.loadingCsList = true;
                    try {
                        const params = new URLSearchParams({
                            tim: this.filters.tim,
                            search: this.csList.search,
                            periode: this.filters.periode,
                        });
                        const res = await fetch(`/api/performa-cs/cs-list?${params}`);
                        const json = await res.json();
                        if (json.success) this.csList.data = json.data;
                    } catch (e) { console.error(e); }
                    this.loadingCsList = false;
                },
                
                async fetchInsights() {
                    this.loadingInsights = true;
                    try {
                        const params = new URLSearchParams({ tim: this.filters.tim });
                        const res = await fetch(`/api/performa-cs/insights-alerts?${params}`);
                        const json = await res.json();
                        if (json.success) {
                            this.insights.alerts = json.data.alerts;
                            this.insights.insights = json.data.insights;
                        }
                    } catch (e) { console.error(e); }
                    this.loadingInsights = false;
                },
                
                async openCsDetail(csName, csId = null) {
                    this.loadingCsDetail = true;
                    this.showCsDetailModal = true;
                    this.csDetail = null;
                    
                    // Destroy existing chart
                    if (this.csTrendChart) {
                        this.csTrendChart.destroy();
                        this.csTrendChart = null;
                    }
                    
                    try {
                        const params = new URLSearchParams({
                            cs_name: csName,
                            periode: this.filters.periode,
                        });
                        if (csId) params.set('cs_id', csId);
                        
                        const res = await fetch(`/api/performa-cs/cs-detail?${params}`);
                        const json = await res.json();
                        if (json.success) {
                            this.csDetail = json.data;
                        }
                    } catch (e) { console.error(e); }
                    
                    this.loadingCsDetail = false;
                    
                    // Render chart after loading overlay is hidden
                    setTimeout(() => this.renderCsTrendChart(), 200);
                },
                
                renderH2hChart() {
                    const ctx = document.getElementById('h2hChart');
                    if (!ctx) return;
                    
                    if (this.h2hChart) this.h2hChart.destroy();
                    
                    // Potong nama yang panjang (max 12 karakter)
                    const truncateName = (name) => {
                        if (name.length > 12) return name.substring(0, 12) + '...';
                        return name;
                    };
                    
                    const rawData = this.h2h.comparison.slice(0, 10);
                    const labels = rawData.map(i => truncateName(i.name));
                    const fullNames = rawData.map(i => i.name); // Untuk tooltip
                    const data1 = rawData.map(i => i.periode1_value);
                    const data2 = rawData.map(i => i.periode2_value);
                    
                    this.h2hChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: this.h2h.labels?.periode1 || 'Periode 1',
                                    data: data1,
                                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                    borderRadius: 4,
                                    barPercentage: 0.8,
                                    categoryPercentage: 0.85,
                                },
                                {
                                    label: this.h2h.labels?.periode2 || 'Periode 2',
                                    data: data2,
                                    backgroundColor: 'rgba(156, 163, 175, 0.6)',
                                    borderRadius: 4,
                                    barPercentage: 0.8,
                                    categoryPercentage: 0.85,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'top',
                                    align: 'end',
                                    labels: { 
                                        color: '#374151', 
                                        font: { weight: 500, size: 11 },
                                        boxWidth: 12,
                                        padding: 15,
                                    } 
                                },
                                tooltip: {
                                    callbacks: {
                                        title: (items) => {
                                            // Tampilkan nama lengkap di tooltip
                                            return fullNames[items[0].dataIndex];
                                        },
                                        label: (item) => {
                                            const value = item.raw;
                                            return `${item.dataset.label}: Rp ${value.toLocaleString('id-ID')}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { 
                                    ticks: { 
                                        color: '#6B7280',
                                        font: { size: 10 },
                                        maxRotation: 45,
                                        minRotation: 45,
                                    }, 
                                    grid: { display: false } 
                                },
                                y: { 
                                    ticks: { 
                                        color: '#6B7280',
                                        font: { size: 10 },
                                        callback: (value) => {
                                            if (value >= 1000000) return (value / 1000000).toFixed(1) + ' jt';
                                            if (value >= 1000) return (value / 1000).toFixed(0) + ' rb';
                                            return value;
                                        }
                                    }, 
                                    grid: { color: '#F3F4F6' },
                                    beginAtZero: true,
                                }
                            }
                        }
                    });
                },
                
                renderCsTrendChart() {
                    const canvas = document.getElementById('csTrendChart');
                    if (!canvas) return;
                    
                    // Destroy existing chart
                    if (this.csTrendChart) {
                        this.csTrendChart.destroy();
                        this.csTrendChart = null;
                    }
                    
                    // Check data
                    const trendData = this.csDetail?.trend;
                    if (!trendData || !Array.isArray(trendData) || trendData.length === 0) {
                        return;
                    }
                    
                    const labels = trendData.map(i => i.week_start?.slice(5, 10) || i.week || '-');
                    const data = trendData.map(i => parseFloat(i.total) || 0);
                    
                    this.csTrendChart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Perolehan',
                                data: data,
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 6,
                                pointBackgroundColor: 'rgb(16, 185, 129)',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            plugins: { 
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => 'Rp ' + (ctx.raw || 0).toLocaleString('id-ID')
                                    }
                                }
                            },
                            scales: {
                                x: { 
                                    ticks: { color: '#6B7280' }, 
                                    grid: { display: false } 
                                },
                                y: { 
                                    ticks: { 
                                        color: '#6B7280',
                                        callback: (v) => {
                                            if (v >= 1000000) return (v/1000000).toFixed(1) + ' jt';
                                            if (v >= 1000) return (v/1000).toFixed(0) + ' rb';
                                            return v;
                                        }
                                    }, 
                                    grid: { color: '#E5E7EB' },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                
                exportH2h() {
                    const params = new URLSearchParams({
                        mode: this.h2h.mode,
                        periode1: this.h2h.periode1,
                        periode2: this.h2h.periode2,
                        tim: this.filters.tim,
                        view_by: this.h2h.viewBy,
                    });
                    window.open(`/api/performa-cs/export?${params}`, '_blank');
                },
                
                formatRupiah(amount) {
                    if (!amount) return 'Rp 0';
                    if (amount >= 1000000) return 'Rp ' + (amount / 1000000).toFixed(1) + ' jt';
                    if (amount >= 1000) return 'Rp ' + (amount / 1000).toFixed(0) + ' rb';
                    return 'Rp ' + amount.toLocaleString('id-ID');
                },
                
                formatDate(date) {
                    if (!date) return '-';
                    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                },
            };
        }
    </script>
@endpush

</x-layouts.app>
