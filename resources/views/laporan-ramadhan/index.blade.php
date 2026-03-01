<x-layouts.app active="laporan-ramadhan" title="Laporan Ramadhan - Abbarat" xData="laporanRamadhanApp()" :chartjs="true">

@push('styles')
<style>
    .field-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; transition: all 0.15s; background: white;
    }
    .field-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
    .field-input.error { border-color: #EF4444; }
    .field-error { font-size: 11px; color: #EF4444; margin-top: 2px; }
    .btn-primary {
        background: linear-gradient(135deg, #059669, #10B981); color: white;
        padding: 8px 20px; border-radius: 10px; font-weight: 600; font-size: 13px;
        transition: all 0.2s; border: none; cursor: pointer;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(5,150,105,0.3); }
    .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .btn-secondary {
        background: white; color: #374151; border: 1px solid #D1D5DB;
        padding: 8px 20px; border-radius: 10px; font-weight: 600; font-size: 13px;
        transition: all 0.2s; cursor: pointer;
    }
    .btn-secondary:hover { background: #F9FAFB; }
    .table-row { transition: background 0.15s; }
    .table-row:hover { background: #F0FDF4; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.1); }
    .spinner { border: 3px solid #D1FAE5; border-top: 3px solid #10B981; border-radius: 50%; width: 24px; height: 24px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .tab-btn { padding: 10px 20px; font-size: 13px; font-weight: 600; border-radius: 12px; border: none; cursor: pointer; transition: all 0.2s; }
    .tab-btn.active { background: linear-gradient(135deg, #059669, #10B981); color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
    .tab-btn:not(.active) { background: white; color: #6B7280; border: 1px solid #E5E7EB; }
    .tab-btn:not(.active):hover { color: #059669; border-color: #059669; background: #F0FDF4; }
    .growth-positive { color: #059669; }
    .growth-negative { color: #EF4444; }
    .progress-bar { height: 8px; border-radius: 4px; background: #E5E7EB; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 4px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
@endpush

@push('before-sidebar')
    {{-- Loading Overlay --}}
    <div x-show="loading" x-cloak class="fixed inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="flex flex-col items-center gap-3 text-primary-600">
            <svg class="animate-spin h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-medium text-sm">Memuat data...</span>
        </div>
    </div>

    {{-- ======= PERIOD FORM MODAL ======= --}}
    <div x-show="showPeriodModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Form Periode Ramadhan"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click.self="closePeriodModal()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center z-[80] p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-lg my-8 shadow-2xl relative" @click.stop>
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi text-white text-lg" :class="periodForm.id ? 'bi-pencil-square' : 'bi-plus-lg'"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="periodForm.id ? 'Edit Periode Ramadhan' : 'Tambah Periode Ramadhan'"></h3>
                        <p class="text-xs text-gray-500">Isi tanggal Ramadhan berdasarkan kalender Hijriah</p>
                    </div>
                </div>
                <button @click="closePeriodModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            {{-- Body --}}
            <form @submit.prevent="submitPeriod()" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Tahun Hijriah <span class="text-red-500">*</span></label>
                        <input type="number" x-model="periodForm.hijri_year" placeholder="1447" class="field-input" :class="{ 'error': periodErrors.hijri_year }">
                        <p x-show="periodErrors.hijri_year" class="field-error" x-text="periodErrors.hijri_year?.[0]"></p>
                    </div>
                    <div>
                        <label class="field-label">Label</label>
                        <input type="text" x-model="periodForm.label" placeholder="Ramadhan 1447H / 2026M" class="field-input" :class="{ 'error': periodErrors.label }">
                        <p x-show="periodErrors.label" class="field-error" x-text="periodErrors.label?.[0]"></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" x-model="periodForm.start_date" class="field-input" :class="{ 'error': periodErrors.start_date }">
                        <p x-show="periodErrors.start_date" class="field-error" x-text="periodErrors.start_date?.[0]"></p>
                    </div>
                    <div>
                        <label class="field-label">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" x-model="periodForm.end_date" class="field-input" :class="{ 'error': periodErrors.end_date }">
                        <p x-show="periodErrors.end_date" class="field-error" x-text="periodErrors.end_date?.[0]"></p>
                    </div>
                </div>
                <div>
                    <label class="field-label">Target Perolehan (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                        <input type="text" x-model="periodForm.target_display"
                               @input="formatTarget()"
                               placeholder="1.800.000.000"
                               class="field-input pl-10" :class="{ 'error': periodErrors.target }">
                    </div>
                    <p x-show="periodErrors.target" class="field-error" x-text="periodErrors.target?.[0]"></p>
                </div>
            </form>
            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3 bg-gray-50 rounded-b-2xl">
                <button @click="closePeriodModal()" class="btn-secondary">Batal</button>
                <button @click="submitPeriod()" :disabled="savingPeriod" class="btn-primary flex items-center gap-2">
                    <svg x-show="savingPeriod" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="savingPeriod ? 'Menyimpan...' : (periodForm.id ? 'Update' : 'Simpan')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ======= DELETE CONFIRMATION MODAL ======= --}}
    <div x-show="showDeleteModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Laporan"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="showDeleteModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[90] p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center" @click.stop>
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-exclamation-triangle text-3xl text-red-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-500 text-sm mb-6" x-text="deleteMessage"></p>
            <div class="flex gap-3 justify-center">
                <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
                <button @click="executeDeletePeriod()" class="bg-red-500 text-white px-5 py-2 rounded-xl hover:bg-red-600 transition font-semibold text-sm">
                    Hapus
                </button>
            </div>
        </div>
    </div>
@endpush

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
        <div class="flex items-center justify-between px-4 md:px-6 py-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl">
                <i class="bi bi-list text-xl"></i>
            </button>
            <div class="flex items-center gap-3">
                <div class="hidden lg:flex w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl items-center justify-center">
                    <i class="bi bi-moon-stars text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-gray-800">Laporan Ramadhan</h1>
                    <p class="text-xs text-gray-500 hidden md:block">Insight perolehan donasi tiap bulan Ramadhan</p>
                </div>
            </div>
            <button @click="openCreatePeriodModal()" class="flex items-center gap-2 px-3 md:px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition shadow-lg shadow-primary-500/30">
                <i class="bi bi-calendar-plus"></i>
                <span class="hidden md:inline">Kelola Periode</span>
            </button>
        </div>
    </header>

    <!-- Content -->
    <div class="p-4 md:p-6 space-y-6">

        <!-- Periode Ramadhan Cards -->
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Periode Ramadhan</h2>
                <div class="flex items-center gap-2">
                    <button @click="openCreatePeriodModal()" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <i class="bi bi-plus-circle"></i> Tambah Periode
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                <template x-for="p in periodStats" :key="p.period_id">
                    <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm relative group">
                        {{-- Edit/Delete on hover --}}
                        <div class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button @click="openEditPeriodModal(p.period_id)" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-500 text-sm"><i class="bi bi-pencil"></i></button>
                            <button @click="confirmDeletePeriod(p.period_id, p.label)" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 text-red-500 text-sm"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center">
                                <i class="bi bi-moon-stars text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800" x-text="p.label"></p>
                                <p class="text-xs text-gray-400" x-text="p.start_date + ' s/d ' + p.end_date"></p>
                            </div>
                        </div>
                        <p class="text-xl font-bold text-gray-800" x-text="p.total_perolehan_formatted"></p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500">Target: <span x-text="p.target_formatted"></span></span>
                            <span class="text-xs font-bold" :class="p.percentage >= 100 ? 'text-emerald-600' : 'text-orange-500'" x-text="p.percentage + '%'"></span>
                        </div>
                        <div class="progress-bar mt-2">
                            <div class="progress-fill" :class="p.percentage >= 100 ? 'bg-emerald-500' : 'bg-orange-400'" :style="'width: ' + Math.min(p.percentage, 100) + '%'"></div>
                        </div>
                        <div class="flex gap-4 mt-3 text-xs text-gray-500">
                            <span><i class="bi bi-receipt mr-1"></i><span x-text="p.total_transaksi"></span> transaksi</span>
                            <span><i class="bi bi-people mr-1"></i><span x-text="p.total_cs"></span> CS</span>
                        </div>
                    </div>
                </template>
                <template x-if="periodStats.length === 0 && !loading">
                    <div class="col-span-full bg-gray-50 rounded-2xl p-8 text-center">
                        <i class="bi bi-calendar-x text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm">Belum ada periode Ramadhan. Klik "Kelola Periode" untuk menambah.</p>
                    </div>
                </template>
            </div>
        </section>

        <!-- Line Chart: Perbandingan Harian Antar Tahun -->
        <section x-show="trendSeries.length > 0" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-graph-up text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Trend Perolehan Harian</h3>
                        <p class="text-[11px] text-gray-400">Perbandingan perolehan per hari Ramadhan antar tahun</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template x-for="(s, i) in trendSeries" :key="'legend-'+s.masehi_year">
                        <span class="flex items-center gap-1.5 text-xs text-gray-600">
                            <span class="w-3 h-3 rounded-full" :style="'background:' + chartColors[i % chartColors.length]"></span>
                            <span x-text="s.label"></span>
                        </span>
                    </template>
                </div>
            </div>
            <div class="p-4">
                <div style="position: relative; height: 320px;">
                    <canvas id="ramadhanTrendChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex items-center gap-2 flex-1">
                    <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Hari Ramadhan:</label>
                    <select x-model="filters.hari_ramadhan" @change="onFilterChange()" class="field-input max-w-[180px]">
                        <option value="">Semua Hari</option>
                        <template x-for="d in 30" :key="d">
                            <option :value="d" x-text="'Hari ke-' + d"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Kumulatif:</label>
                    <select x-model="filters.kumulatif" @change="onFilterChange()" class="field-input max-w-[200px]">
                        <option value="">Non-kumulatif</option>
                        <template x-for="d in 30" :key="d">
                            <option :value="d" x-text="'Hari 1 s/d ' + d"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Tim:</label>
                    <select x-model="filters.tim" @change="onFilterChange()" class="field-input max-w-[160px]">
                        <option value="">Semua Tim</option>
                        <template x-for="t in filterOptions.tim_list" :key="t">
                            <option :value="t" x-text="t"></option>
                        </template>
                    </select>
                </div>
                <button @click="resetFilters()" class="btn-secondary text-sm px-3 py-2 flex items-center gap-1">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </section>

        <!-- Tab Navigation -->
        <section>
            <div class="flex flex-wrap gap-2">
                <button @click="activeTab = 'cs'" class="tab-btn" :class="activeTab === 'cs' ? 'active' : ''">
                    <i class="bi bi-people mr-1"></i> Per CS
                </button>
                <button @click="activeTab = 'tim'" class="tab-btn" :class="activeTab === 'tim' ? 'active' : ''">
                    <i class="bi bi-diagram-3 mr-1"></i> Per Tim
                </button>
                <button @click="activeTab = 'trend'" class="tab-btn" :class="activeTab === 'trend' ? 'active' : ''">
                    <i class="bi bi-graph-up mr-1"></i> Trend Harian
                </button>
            </div>
        </section>

        <!-- TAB: Perbandingan per CS -->
        <section x-show="activeTab === 'cs'" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Perbandingan Perolehan CS Antar Tahun</h3>
                <div class="relative max-w-[200px]">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model.debounce.500ms="filters.search_cs" @input="loadCSData()" placeholder="Cari nama CS..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50 min-w-[160px]">Nama CS</th>
                            <template x-for="col in csColumns" :key="col.year">
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase min-w-[150px]">
                                    <div x-text="'Ramadhan ' + col.year"></div>
                                    <div class="text-[10px] font-normal text-gray-400" x-text="'Target: ' + col.target_formatted"></div>
                                </th>
                            </template>
                            <th x-show="csColumns.length >= 2" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase min-w-[100px]">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr x-show="loadingCS">
                            <td :colspan="csColumns.length + 2" class="py-12 text-center">
                                <div class="spinner mx-auto mb-2"></div>
                                <p class="text-sm text-gray-500">Memuat data...</p>
                            </td>
                        </tr>
                        <tr x-show="!loadingCS && csData.length === 0">
                            <td :colspan="csColumns.length + 2" class="py-12 text-center text-gray-500 text-sm">Tidak ada data</td>
                        </tr>
                        <template x-for="row in csData" :key="row.nama_cs">
                            <tr class="table-row">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 sticky left-0 bg-white" x-text="row.nama_cs"></td>
                                <template x-for="col in csColumns" :key="col.year">
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-gray-800" x-text="row['total_' + col.year + '_formatted']"></span>
                                        <div class="text-[10px] text-gray-400" x-text="(row['persen_' + col.year] || 0) + '%'"></div>
                                    </td>
                                </template>
                                <td x-show="csColumns.length >= 2" class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold flex items-center justify-end gap-1"
                                          :class="(row.growth || 0) >= 0 ? 'growth-positive' : 'growth-negative'">
                                        <i class="bi" :class="(row.growth || 0) >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                                        <span x-text="Math.abs(row.growth || 0) + '%'"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    {{-- Totals Row --}}
                    <tfoot x-show="csData.length > 0" class="bg-primary-50 border-t-2 border-primary-200">
                        <tr>
                            <td class="px-4 py-3 text-sm font-bold text-primary-700 sticky left-0 bg-primary-50">TOTAL</td>
                            <template x-for="col in csColumns" :key="'total_' + col.year">
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-primary-700" x-text="calcColumnTotal(col.year)"></span>
                                </td>
                            </template>
                            <td x-show="csColumns.length >= 2" class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- TAB: Perbandingan per Tim -->
        <section x-show="activeTab === 'tim'" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Perbandingan Perolehan Tim Antar Tahun</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase min-w-[160px]">Tim</th>
                            <template x-for="col in timColumns" :key="col.year">
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase min-w-[150px]" x-text="'Ramadhan ' + col.year"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr x-show="loadingTim">
                            <td :colspan="timColumns.length + 1" class="py-12 text-center">
                                <div class="spinner mx-auto mb-2"></div>
                                <p class="text-sm text-gray-500">Memuat data...</p>
                            </td>
                        </tr>
                        <tr x-show="!loadingTim && timData.length === 0">
                            <td :colspan="timColumns.length + 1" class="py-12 text-center text-gray-500 text-sm">Tidak ada data</td>
                        </tr>
                        <template x-for="row in timData" :key="row.tim">
                            <tr class="table-row">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800" x-text="row.tim"></td>
                                <template x-for="col in timColumns" :key="col.year">
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-gray-800" x-text="row['total_' + col.year + '_formatted']"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB: Trend Harian (Chart) -->
        <section x-show="activeTab === 'trend'" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Trend Harian per Hari Ramadhan</h3>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500">CS:</label>
                    <select x-model="filters.trend_cs" @change="loadTrendData()" class="field-input text-xs max-w-[160px] py-1.5">
                        <option value="">Semua CS</option>
                        <template x-for="cs in filterOptions.cs_list" :key="cs">
                            <option :value="cs" x-text="cs"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="p-4">
                <div x-show="loadingTrend" class="flex items-center justify-center py-12">
                    <div class="spinner"></div>
                </div>
                <div x-show="!loadingTrend">
                    {{-- Simple Table-based Chart (no external chart lib needed) --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-2 py-2 text-left text-gray-500 font-semibold min-w-[60px]">Hari</th>
                                    <template x-for="s in trendSeries" :key="s.masehi_year">
                                        <th class="px-2 py-2 text-right text-gray-500 font-semibold min-w-[140px]" x-text="s.label"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="day in 30" :key="day">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="px-2 py-2 font-medium text-gray-600" x-text="'Hari ' + day"></td>
                                        <template x-for="s in trendSeries" :key="s.masehi_year + '-' + day">
                                            <td class="px-2 py-2 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <div class="flex-1 max-w-[100px]">
                                                        <div class="progress-bar h-2">
                                                            <div class="progress-fill bg-emerald-400" :style="'width: ' + calcBarWidth(s, day) + '%'"></div>
                                                        </div>
                                                    </div>
                                                    <span class="font-medium text-gray-700 whitespace-nowrap" x-text="getTrendValue(s, day)"></span>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                            {{-- Accumulative Total --}}
                            <tfoot class="bg-primary-50 border-t-2 border-primary-200">
                                <tr>
                                    <td class="px-2 py-2 font-bold text-primary-700">TOTAL</td>
                                    <template x-for="s in trendSeries" :key="'total-' + s.masehi_year">
                                        <td class="px-2 py-2 text-right font-bold text-primary-700" x-text="calcTrendTotal(s)"></td>
                                    </template>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </div>

@push('scripts')
<script>
function laporanRamadhanApp() {
    return {
        // State
        loading: true,
        activeTab: 'cs',
        showPeriodModal: false,
        showDeleteModal: false,
        deleteMessage: '',
        deletePeriodId: null,
        savingPeriod: false,

        // Data
        periods: [],
        periodStats: [],
        ramadhanChart: null,
        chartColors: ['#059669', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
        csData: [],
        csColumns: [],
        timData: [],
        timColumns: [],
        trendSeries: [],
        filterOptions: { tim_list: [], cs_list: [] },

        // Loading per tab
        loadingCS: false,
        loadingTim: false,
        loadingTrend: false,

        // Filters
        filters: {
            hari_ramadhan: '',
            kumulatif: '',
            tim: '',
            search_cs: '',
            sort: 'nama_cs',
            order: 'asc',
            trend_cs: '',
        },

        // Period Form
        periodForm: { id: null, hijri_year: '', label: '', start_date: '', end_date: '', target: '', target_display: '' },
        periodErrors: {},

        // Init
        async init() {
            await Promise.all([
                this.loadPeriods(),
                this.loadOptions(),
            ]);
            await this.loadAllData();
            this.loading = false;
        },

        // ===== Period Management =====
        async loadPeriods() {
            try {
                const res = await fetch('/api/laporan-ramadhan/periods');
                this.periods = await res.json();
            } catch (e) { console.error('Error loading periods:', e); }
        },

        async loadOptions() {
            try {
                const res = await fetch('/api/laporan-ramadhan/options');
                this.filterOptions = await res.json();
            } catch (e) { console.error('Error loading options:', e); }
        },

        async loadAllData() {
            await Promise.all([
                this.loadStats(),
                this.loadCSData(),
                this.loadTimData(),
                this.loadTrendData(),
            ]);
        },

        // ===== Stats =====
        async loadStats() {
            try {
                const params = this.buildFilterParams();
                const res = await fetch('/api/laporan-ramadhan/stats?' + params.toString());
                const data = await res.json();
                this.periodStats = data.periods || [];
            } catch (e) { console.error('Error loading stats:', e); }
        },

        // ===== CS Data =====
        async loadCSData() {
            this.loadingCS = true;
            try {
                const params = this.buildFilterParams();
                params.append('search', this.filters.search_cs);
                params.append('sort', this.filters.sort);
                params.append('order', this.filters.order);
                const res = await fetch('/api/laporan-ramadhan/perbandingan-cs?' + params.toString());
                const data = await res.json();
                this.csData = data.data || [];
                this.csColumns = (data.periods || []).map(p => ({
                    year: p.masehi_year,
                    label: p.label,
                    target_formatted: p.target_formatted,
                }));
            } catch (e) { console.error('Error loading CS data:', e); }
            this.loadingCS = false;
        },

        // ===== Tim Data =====
        async loadTimData() {
            this.loadingTim = true;
            try {
                const params = this.buildFilterParams();
                const res = await fetch('/api/laporan-ramadhan/perbandingan-tim?' + params.toString());
                const data = await res.json();
                this.timData = data.data || [];
                this.timColumns = (data.periods || []).map(p => ({
                    year: p.masehi_year,
                    label: p.label,
                }));
            } catch (e) { console.error('Error loading Tim data:', e); }
            this.loadingTim = false;
        },

        // ===== Trend Data =====
        async loadTrendData() {
            this.loadingTrend = true;
            try {
                const params = this.buildFilterParams();
                if (this.filters.trend_cs) params.append('nama_cs', this.filters.trend_cs);
                const res = await fetch('/api/laporan-ramadhan/trend-harian?' + params.toString());
                const data = await res.json();
                this.trendSeries = data.series || [];
                this.$nextTick(() => this.renderTrendChart());
            } catch (e) { console.error('Error loading trend:', e); }
            this.loadingTrend = false;
        },

        // ===== Filter Helpers =====
        buildFilterParams() {
            const params = new URLSearchParams();
            if (this.filters.hari_ramadhan) params.append('hari_ramadhan', this.filters.hari_ramadhan);
            if (this.filters.kumulatif) params.append('kumulatif', this.filters.kumulatif);
            if (this.filters.tim) params.append('tim', this.filters.tim);
            return params;
        },

        onFilterChange() {
            // hari_ramadhan and kumulatif are mutually exclusive
            if (this.filters.hari_ramadhan && this.filters.kumulatif) {
                this.filters.kumulatif = '';
            }
            this.loadAllData();
        },

        resetFilters() {
            this.filters.hari_ramadhan = '';
            this.filters.kumulatif = '';
            this.filters.tim = '';
            this.filters.search_cs = '';
            this.filters.trend_cs = '';
            this.loadAllData();
        },

        // ===== Period CRUD =====
        openCreatePeriodModal() {
            this.periodForm = { id: null, hijri_year: '', label: '', start_date: '', end_date: '', target: '', target_display: '' };
            this.periodErrors = {};
            this.showPeriodModal = true;
        },

        openEditPeriodModal(periodId) {
            const p = this.periods.find(x => x.id === periodId);
            if (!p) return;
            this.periodForm = {
                id: p.id,
                hijri_year: p.hijri_year,
                label: p.label,
                start_date: p.start_date,
                end_date: p.end_date,
                target: p.target,
                target_display: this.numberFormat(p.target),
            };
            this.periodErrors = {};
            this.showPeriodModal = true;
        },

        closePeriodModal() {
            this.showPeriodModal = false;
        },

        formatTarget() {
            let raw = this.periodForm.target_display.replace(/\D/g, '');
            this.periodForm.target = raw;
            this.periodForm.target_display = this.numberFormat(raw);
        },

        async submitPeriod() {
            this.periodErrors = {};
            this.savingPeriod = true;

            const payload = {
                hijri_year: parseInt(this.periodForm.hijri_year),
                label: this.periodForm.label || `Ramadhan ${this.periodForm.hijri_year}H`,
                start_date: this.periodForm.start_date,
                end_date: this.periodForm.end_date,
                target: parseFloat(this.periodForm.target) || 0,
            };

            try {
                const url = this.periodForm.id
                    ? `/api/laporan-ramadhan/periods/${this.periodForm.id}`
                    : '/api/laporan-ramadhan/periods';
                const method = this.periodForm.id ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok) {
                    if (data.errors) this.periodErrors = data.errors;
                    else this.showToast(data.message || 'Terjadi kesalahan', 'error');
                    this.savingPeriod = false;
                    return;
                }

                this.showToast(data.message || 'Berhasil!', 'success');
                this.closePeriodModal();
                await this.loadPeriods();
                await this.loadAllData();
            } catch (e) {
                console.error('Submit period error:', e);
                this.showToast('Terjadi kesalahan jaringan', 'error');
            }
            this.savingPeriod = false;
        },

        confirmDeletePeriod(id, label) {
            this.deletePeriodId = id;
            this.deleteMessage = `Hapus periode "${label}"? Data perolehan di tabel laporans TIDAK akan terhapus.`;
            this.showDeleteModal = true;
        },

        async executeDeletePeriod() {
            this.showDeleteModal = false;
            this.loading = true;
            try {
                await fetch(`/api/laporan-ramadhan/periods/${this.deletePeriodId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.showToast('Periode berhasil dihapus', 'success');
                await this.loadPeriods();
                await this.loadAllData();
            } catch (e) {
                this.showToast('Gagal menghapus periode', 'error');
            }
            this.loading = false;
        },

        // ===== Chart =====
        renderTrendChart() {
            const ctx = document.getElementById('ramadhanTrendChart');
            if (!ctx) return;

            if (this.ramadhanChart) {
                this.ramadhanChart.destroy();
                this.ramadhanChart = null;
            }

            if (!this.trendSeries || this.trendSeries.length === 0) return;

            const maxDays = Math.max(...this.trendSeries.map(s => (s.data || []).length));
            const labels = Array.from({ length: maxDays }, (_, i) => 'Hari ' + (i + 1));

            const datasets = this.trendSeries.map((s, i) => {
                const color = this.chartColors[i % this.chartColors.length];
                return {
                    label: s.label,
                    data: (s.data || []).map(d => d.total),
                    borderColor: color,
                    backgroundColor: color + '15',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: color,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true,
                };
            });

            this.ramadhanChart = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#F9FAFB',
                            bodyColor: '#F9FAFB',
                            padding: 12,
                            cornerRadius: 10,
                            bodySpacing: 6,
                            callbacks: {
                                label: (ctx) => ctx.dataset.label + ': Rp ' + this.numberFormat(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F3F4F6', drawBorder: false },
                            ticks: {
                                font: { size: 11 },
                                callback: (v) => {
                                    if (v >= 1000000000) return (v / 1000000000).toFixed(1) + 'M';
                                    if (v >= 1000000) return (v / 1000000).toFixed(0) + 'JT';
                                    if (v >= 1000) return (v / 1000).toFixed(0) + 'RB';
                                    return v;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        },

        // ===== Helpers =====
        numberFormat(num) {
            if (!num) return '';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        calcColumnTotal(year) {
            let total = 0;
            this.csData.forEach(row => { total += (row['total_' + year] || 0); });
            return 'Rp ' + this.numberFormat(total);
        },

        getTrendValue(series, day) {
            const d = series.data?.find(x => x.day === day);
            return d ? d.total_formatted : 'Rp 0';
        },

        getTrendRawValue(series, day) {
            const d = series.data?.find(x => x.day === day);
            return d ? d.total : 0;
        },

        calcBarWidth(series, day) {
            if (!series.data || series.data.length === 0) return 0;
            const maxVal = Math.max(...series.data.map(d => d.total));
            if (maxVal === 0) return 0;
            const val = this.getTrendRawValue(series, day);
            return Math.round((val / maxVal) * 100);
        },

        calcTrendTotal(series) {
            if (!series.data) return 'Rp 0';
            const total = series.data.reduce((sum, d) => sum + d.total, 0);
            return 'Rp ' + this.numberFormat(total);
        },

        // ===== Toast =====
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = { success: 'bg-green-500 text-white', error: 'bg-red-500 text-white', info: 'bg-gray-800 text-white' };
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl shadow-lg z-[200] transition-all transform ${colors[type] || colors.info}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(10px)'; setTimeout(() => toast.remove(), 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
