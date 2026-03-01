<x-layouts.app active="daftar-cs" title="Daftar CS - CRM Dashboard" xData="daftarCsApp()" :chartjs="true">

@push('styles')
<style>
    .cs-card { transition: all 0.2s ease; }
    .cs-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-card { transition: all 0.2s ease; }
    .stat-card:hover { transform: translateY(-1px); }
    .team-badge { font-size: 10px; padding: 2px 8px; border-radius: 9999px; font-weight: 600; }
    .growth-up { color: #10B981; }
    .growth-down { color: #EF4444; }
    .sort-btn.active { color: #059669; font-weight: 600; }
    .search-input:focus { box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
</style>
@endpush

@push('before-sidebar')
    <!-- CS Detail Modal -->
    <div x-show="showDetailModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Detail Customer Service"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click.self="showDetailModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[80] p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl relative" @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <!-- Loading -->
            <div x-show="loadingDetail" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-20 rounded-2xl">
                <div class="flex flex-col items-center gap-3 text-primary-600">
                    <svg class="animate-spin h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-medium">Memuat detail CS...</span>
                </div>
            </div>
            <!-- Header -->
            <div class="p-5 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-lg" x-text="(csDetail?.cs?.name || 'U').substring(0,2).toUpperCase()"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="csDetail?.cs?.name || 'Detail CS'"></h3>
                        <span class="text-xs text-gray-500" x-text="csDetail?.cs?.team || ''"></span>
                    </div>
                </div>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition" aria-label="Tutup">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="p-5 overflow-y-auto flex-1 bg-gray-50 space-y-5">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                        <p class="text-xl font-bold text-primary-600" x-text="csDetail?.summary?.total_perolehan_formatted || 'Rp 0'"></p>
                        <p class="text-[10px] text-gray-500 mt-1">Total Perolehan</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                        <p class="text-xl font-bold text-blue-600" x-text="csDetail?.summary?.total_donatur || 0"></p>
                        <p class="text-[10px] text-gray-500 mt-1">Total Donatur</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                        <p class="text-xl font-bold text-amber-600" x-text="csDetail?.summary?.total_laporan || 0"></p>
                        <p class="text-[10px] text-gray-500 mt-1">Total Laporan</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                        <p class="text-xl font-bold text-emerald-600" x-text="csDetail?.summary?.hari_aktif || 0"></p>
                        <p class="text-[10px] text-gray-500 mt-1">Hari Aktif</p>
                    </div>
                </div>

                <!-- Attendance Row -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-calendar-check-fill text-primary-500 mr-1"></i> Absensi</h4>
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div><p class="text-lg font-bold text-gray-700" x-text="csDetail?.absensi?.total_hari || 0"></p><p class="text-[10px] text-gray-400">Total Hari</p></div>
                        <div><p class="text-lg font-bold text-emerald-600" x-text="csDetail?.absensi?.hadir || 0"></p><p class="text-[10px] text-gray-400">Hadir</p></div>
                        <div><p class="text-lg font-bold text-blue-600" x-text="csDetail?.absensi?.absen_masuk || 0"></p><p class="text-[10px] text-gray-400">Absen Masuk</p></div>
                        <div><p class="text-lg font-bold text-violet-600" x-text="csDetail?.absensi?.absen_pulang || 0"></p><p class="text-[10px] text-gray-400">Absen Pulang</p></div>
                    </div>
                </div>

                <!-- Daily Trend Chart -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-graph-up text-primary-500 mr-1"></i> Trend Harian</h4>
                    <div style="position: relative; height: 200px;">
                        <canvas id="detailTrendChart"></canvas>
                    </div>
                </div>

                <!-- Program & Channel -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-bookmark-fill text-amber-500 mr-1"></i> Sumber Hasil</h4>
                        <div class="space-y-2">
                            <template x-for="(p, i) in (csDetail?.programs || [])" :key="i">
                                <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                                    <span class="text-xs text-gray-600 truncate flex-1" x-text="p.label"></span>
                                    <span class="text-xs font-semibold text-gray-800 ml-2" x-text="'Rp ' + Number(p.total).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                            <template x-if="!csDetail?.programs?.length">
                                <p class="text-xs text-gray-400 italic">Tidak ada data</p>
                            </template>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-broadcast text-blue-500 mr-1"></i> Channel</h4>
                        <div class="space-y-2">
                            <template x-for="(c, i) in (csDetail?.channels || [])" :key="i">
                                <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                                    <span class="text-xs text-gray-600 truncate flex-1" x-text="c.label"></span>
                                    <span class="text-xs font-semibold text-gray-800 ml-2" x-text="'Rp ' + Number(c.total).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                            <template x-if="!csDetail?.channels?.length">
                                <p class="text-xs text-gray-400 italic">Tidak ada data</p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-clock-history text-emerald-500 mr-1"></i> Aktivitas Terakhir</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-100">
                                    <th class="text-left py-2 font-medium">Tanggal</th>
                                    <th class="text-left py-2 font-medium">Donatur</th>
                                    <th class="text-right py-2 font-medium">Nominal</th>
                                    <th class="text-left py-2 font-medium">Sumber</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(a, i) in (csDetail?.recent_activity || [])" :key="i">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="py-2 text-gray-600" x-text="a.tanggal"></td>
                                        <td class="py-2 text-gray-700 font-medium" x-text="a.nama_donatur || '-'"></td>
                                        <td class="py-2 text-right font-semibold text-primary-600" x-text="a.jml_perolehan_formatted"></td>
                                        <td class="py-2 text-gray-500" x-text="a.hasil_dari || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <template x-if="!csDetail?.recent_activity?.length">
                            <p class="text-xs text-gray-400 italic py-3 text-center">Tidak ada aktivitas</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit CS Modal -->
    <div x-show="showFormModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Form Customer Service"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="showFormModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[80] p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800" x-text="formMode === 'add' ? 'Tambah CS Baru' : 'Edit CS'"></h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama CS</label>
                    <input type="text" x-model="formData.name" placeholder="Contoh: Ahmad (CS Jakarta)"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tim</label>
                    <select x-model="formData.team"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 outline-none transition bg-white">
                        <option value="">Pilih Tim</option>
                        @foreach($teams as $team)
                        <option value="{{ $team }}">{{ $team }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="formError" class="text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
            </div>
            <div class="p-5 border-t border-gray-100 flex justify-end gap-2">
                <button @click="showFormModal = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button @click="saveCs()" :disabled="formSaving"
                        class="px-5 py-2 text-sm font-semibold bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition disabled:opacity-50">
                    <span x-show="!formSaving" x-text="formMode === 'add' ? 'Simpan' : 'Update'"></span>
                    <span x-show="formSaving"><i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div x-show="showDeleteModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Customer Service"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="showDeleteModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[80] p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center" @click.stop>
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus CS?</h3>
            <p class="text-sm text-gray-500 mb-5">Data <strong x-text="deleteTarget?.name"></strong> akan dihapus permanen.</p>
            <div class="flex justify-center gap-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button @click="confirmDelete()" class="px-5 py-2 text-sm font-semibold bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
@endpush

<!-- Header -->
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="flex items-center justify-between px-4 md:px-6 py-3">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl transition" aria-label="Toggle menu">
                <i class="bi bi-list text-xl"></i>
            </button>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg md:text-xl font-bold text-gray-800">Daftar Customer Service</h1>
                    <span class="px-2 py-0.5 bg-primary-100 text-primary-700 text-[10px] font-bold rounded-full uppercase"><span x-text="overview.total_cs ?? 0"></span> CS</span>
                </div>
                <p class="text-xs text-gray-500">Kelola dan pantau performa seluruh CS</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select x-model="filterPeriode" @change="refreshAll()"
                    class="text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 focus:border-primary-400 outline-none transition">
                <option value="bulan_ini">Bulan Ini</option>
                <option value="bulan_lalu">Bulan Lalu</option>
                <option value="3_bulan">3 Bulan</option>
                <option value="tahun_ini">Tahun Ini</option>
            </select>
            <button @click="refreshAll()" class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition" title="Refresh" aria-label="Refresh data">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button @click="openAddForm()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-500 text-white text-xs font-semibold rounded-xl hover:bg-primary-600 shadow-lg shadow-primary-500/20 transition">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">Tambah CS</span>
            </button>
        </div>
    </div>
</header>

<!-- Content -->
<div class="p-4 md:p-6 space-y-5">

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-people-fill text-primary-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total CS</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="overview.total_cs ?? '-'"></div>
            <div class="text-[10px] text-gray-400 mt-1"><span x-text="overview.active_cs ?? 0"></span> aktif periode ini</div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Perolehan</span>
            </div>
            <div class="text-xl font-bold text-gray-800" x-text="overview.total_perolehan_formatted ?? '-'"></div>
            <div class="flex items-center gap-1 mt-1">
                <template x-if="overview.growth?.perolehan > 0"><span class="text-[10px] font-semibold growth-up"><i class="bi bi-arrow-up-short"></i><span x-text="overview.growth.perolehan + '%'"></span></span></template>
                <template x-if="overview.growth?.perolehan < 0"><span class="text-[10px] font-semibold growth-down"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.growth.perolehan) + '%'"></span></span></template>
                <template x-if="overview.growth?.perolehan === 0"><span class="text-[10px] text-gray-400">Stabil</span></template>
            </div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-file-earmark-text-fill text-blue-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Laporan</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="overview.total_laporan?.toLocaleString('id-ID') ?? '-'"></div>
            <div class="flex items-center gap-1 mt-1">
                <template x-if="overview.growth?.laporan > 0"><span class="text-[10px] font-semibold growth-up"><i class="bi bi-arrow-up-short"></i><span x-text="overview.growth.laporan + '%'"></span></span></template>
                <template x-if="overview.growth?.laporan < 0"><span class="text-[10px] font-semibold growth-down"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.growth.laporan) + '%'"></span></span></template>
            </div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-person-heart text-amber-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Donatur</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="overview.total_donatur?.toLocaleString('id-ID') ?? '-'"></div>
            <div class="flex items-center gap-1 mt-1">
                <template x-if="overview.growth?.donatur > 0"><span class="text-[10px] font-semibold growth-up"><i class="bi bi-arrow-up-short"></i><span x-text="overview.growth.donatur + '%'"></span></span></template>
                <template x-if="overview.growth?.donatur < 0"><span class="text-[10px] font-semibold growth-down"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.growth.donatur) + '%'"></span></span></template>
            </div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-calculator-fill text-violet-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Rata-rata</span>
            </div>
            <div class="text-lg font-bold text-gray-800" x-text="overview.avg_perolehan_formatted ?? '-'"></div>
            <div class="text-[10px] text-gray-400 mt-1">per laporan</div>
        </div>
    </div>

    <!-- Team Breakdown -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" x-show="overview.team_breakdown?.length > 0">
        <h3 class="text-sm font-semibold text-gray-700 mb-3"><i class="bi bi-diagram-3-fill text-primary-500 mr-1"></i> Breakdown Tim</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            <template x-for="(t, i) in (overview.team_breakdown || [])" :key="i">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 hover:border-primary-200 transition cursor-pointer"
                     @click="filterTeam = t.team; loadCsList()">
                    <div class="text-xs font-semibold text-gray-700 truncate mb-1" x-text="t.team"></div>
                    <div class="text-sm font-bold text-primary-600" x-text="t.total_perolehan_formatted"></div>
                    <div class="text-[10px] text-gray-400"><span x-text="t.active_cs"></span> CS &middot; <span x-text="t.total_laporan?.toLocaleString('id-ID')"></span> laporan</div>
                </div>
            </template>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model="searchQuery" @input.debounce.400ms="loadCsList()"
                       placeholder="Cari nama CS..."
                       class="search-input w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:border-primary-400 outline-none transition">
            </div>
            <select x-model="filterTeam" @change="loadCsList()"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:border-primary-400 outline-none transition">
                <option value="all">Semua Tim</option>
                @foreach($teams as $team)
                <option value="{{ $team }}">{{ $team }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-1 bg-gray-50 rounded-xl p-1">
                <button @click="viewMode = 'card'" :class="viewMode === 'card' ? 'bg-white shadow-sm text-primary-600' : 'text-gray-400'"
                        class="px-3 py-1.5 rounded-lg text-sm transition" aria-label="Tampilan kartu">
                    <i class="bi bi-grid-fill"></i>
                </button>
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white shadow-sm text-primary-600' : 'text-gray-400'"
                        class="px-3 py-1.5 rounded-lg text-sm transition" aria-label="Tampilan tabel">
                    <i class="bi bi-list-ul"></i>
                </button>
            </div>
        </div>
        <!-- Sort Bar -->
        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
            <span>Urutkan:</span>
            <button @click="toggleSort('name')" class="sort-btn hover:text-primary-600 transition" :class="sortBy === 'name' && 'active'">
                Nama <i class="bi" :class="sortBy === 'name' ? (sortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : ''"></i>
            </button>
            <button @click="toggleSort('total_perolehan')" class="sort-btn hover:text-primary-600 transition" :class="sortBy === 'total_perolehan' && 'active'">
                Perolehan <i class="bi" :class="sortBy === 'total_perolehan' ? (sortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : ''"></i>
            </button>
            <button @click="toggleSort('total_laporan')" class="sort-btn hover:text-primary-600 transition" :class="sortBy === 'total_laporan' && 'active'">
                Laporan <i class="bi" :class="sortBy === 'total_laporan' ? (sortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : ''"></i>
            </button>
            <button @click="toggleSort('growth')" class="sort-btn hover:text-primary-600 transition" :class="sortBy === 'growth' && 'active'">
                Growth <i class="bi" :class="sortBy === 'growth' ? (sortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : ''"></i>
            </button>
            <span class="ml-auto text-gray-400"><span x-text="csList.length"></span> CS</span>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loadingList" class="flex justify-center py-10">
        <div class="flex items-center gap-3 text-primary-600">
            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Memuat daftar CS...</span>
        </div>
    </div>

    <!-- Card View -->
    <div x-show="!loadingList && viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="(cs, idx) in csList" :key="cs.id">
            <div class="cs-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5 fade-in-up cursor-pointer"
                 :style="'animation-delay: ' + (idx * 30) + 'ms'"
                 @click="openDetail(cs.id)">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm" x-text="cs.name.substring(0,2).toUpperCase()"></span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 leading-tight" x-text="cs.name"></h3>
                            <span class="team-badge mt-0.5 inline-block"
                                  :class="teamColor(cs.team)" x-text="cs.team"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click.stop="openEditForm(cs)" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit" aria-label="Edit">
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </button>
                        <button @click.stop="openDeleteModal(cs)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus" aria-label="Hapus">
                            <i class="bi bi-trash-fill text-xs"></i>
                        </button>
                    </div>
                </div>
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="text-center">
                        <p class="text-base font-bold text-primary-600" x-text="formatShort(cs.total_perolehan)"></p>
                        <p class="text-[9px] text-gray-400 uppercase">Perolehan</p>
                    </div>
                    <div class="text-center">
                        <p class="text-base font-bold text-blue-600" x-text="cs.total_laporan"></p>
                        <p class="text-[9px] text-gray-400 uppercase">Laporan</p>
                    </div>
                    <div class="text-center">
                        <p class="text-base font-bold text-amber-600" x-text="cs.total_donatur"></p>
                        <p class="text-[9px] text-gray-400 uppercase">Donatur</p>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px]">
                    <div class="flex items-center gap-1">
                        <template x-if="cs.growth > 0"><span class="growth-up font-semibold"><i class="bi bi-arrow-up-short"></i><span x-text="cs.growth + '%'"></span></span></template>
                        <template x-if="cs.growth < 0"><span class="growth-down font-semibold"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(cs.growth) + '%'"></span></span></template>
                        <template x-if="cs.growth === 0"><span class="text-gray-400">Stabil</span></template>
                    </div>
                    <span class="text-gray-400">Terakhir: <span x-text="cs.last_active_formatted"></span></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Table View -->
    <div x-show="!loadingList && viewMode === 'table'" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <th class="text-left px-5 py-3 font-medium">#</th>
                        <th class="text-left px-5 py-3 font-medium">Nama</th>
                        <th class="text-left px-5 py-3 font-medium">Tim</th>
                        <th class="text-right px-5 py-3 font-medium">Perolehan</th>
                        <th class="text-right px-5 py-3 font-medium">Laporan</th>
                        <th class="text-right px-5 py-3 font-medium">Donatur</th>
                        <th class="text-right px-5 py-3 font-medium">Growth</th>
                        <th class="text-left px-5 py-3 font-medium">Terakhir Aktif</th>
                        <th class="text-center px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(cs, idx) in csList" :key="cs.id">
                        <tr class="border-t border-gray-50 hover:bg-primary-50/30 transition cursor-pointer" @click="openDetail(cs.id)">
                            <td class="px-5 py-3 text-gray-400 text-xs" x-text="idx + 1"></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-[10px]" x-text="cs.name.substring(0,2).toUpperCase()"></span>
                                    </div>
                                    <span class="font-medium text-gray-800" x-text="cs.name"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3"><span class="team-badge" :class="teamColor(cs.team)" x-text="cs.team"></span></td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-800" x-text="cs.total_perolehan_formatted"></td>
                            <td class="px-5 py-3 text-right text-gray-600" x-text="cs.total_laporan.toLocaleString('id-ID')"></td>
                            <td class="px-5 py-3 text-right text-gray-600" x-text="cs.total_donatur.toLocaleString('id-ID')"></td>
                            <td class="px-5 py-3 text-right">
                                <template x-if="cs.growth > 0"><span class="text-xs font-semibold growth-up"><i class="bi bi-arrow-up-short"></i><span x-text="cs.growth + '%'"></span></span></template>
                                <template x-if="cs.growth < 0"><span class="text-xs font-semibold growth-down"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(cs.growth) + '%'"></span></span></template>
                                <template x-if="cs.growth === 0"><span class="text-xs text-gray-400">-</span></template>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs" x-text="cs.last_active_formatted"></td>
                            <td class="px-5 py-3 text-center" @click.stop>
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditForm(cs)" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit" aria-label="Edit">
                                        <i class="bi bi-pencil-fill text-xs"></i>
                                    </button>
                                    <button @click="openDeleteModal(cs)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus" aria-label="Hapus">
                                        <i class="bi bi-trash-fill text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <template x-if="!loadingList && csList.length === 0">
            <div class="text-center py-10 text-gray-400">
                <i class="bi bi-people text-4xl mb-2"></i>
                <p class="text-sm">Tidak ada CS ditemukan</p>
            </div>
        </template>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition
         class="fixed bottom-6 right-6 z-[90] max-w-sm px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2"
         :class="toast.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'">
        <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>

@push('scripts')
<script>
function daftarCsApp() {
    return {
        sidebarOpen: false,
        loadingList: true,
        loadingDetail: false,
        csList: [],
        overview: {},
        csDetail: null,

        // Filters
        searchQuery: '',
        filterTeam: 'all',
        filterPeriode: 'bulan_ini',
        sortBy: 'total_perolehan',
        sortOrder: 'desc',
        viewMode: 'card',

        // Modals
        showDetailModal: false,
        showFormModal: false,
        showDeleteModal: false,

        // Form
        formMode: 'add', // add | edit
        formData: { id: null, name: '', team: '' },
        formError: '',
        formSaving: false,

        // Delete
        deleteTarget: null,

        // Toast
        toast: { show: false, message: '', type: 'success' },

        // Charts
        detailTrendChart: null,

        init() {
            this.refreshAll();
        },

        async refreshAll() {
            this.loadOverview();
            this.loadCsList();
        },

        async loadOverview() {
            try {
                const params = new URLSearchParams({ periode: this.filterPeriode });
                const res = await fetch(`/api/customer-service/overview-stats?${params}`);
                const json = await res.json();
                if (json.success) this.overview = json.data;
            } catch (e) { console.error('Overview error:', e); }
        },

        async loadCsList() {
            this.loadingList = true;
            try {
                const params = new URLSearchParams({
                    team: this.filterTeam,
                    search: this.searchQuery,
                    sort: this.sortBy,
                    order: this.sortOrder,
                    periode: this.filterPeriode,
                });
                const res = await fetch(`/api/customer-service/cs-list?${params}`);
                const json = await res.json();
                if (json.success) this.csList = json.data;
            } catch (e) { console.error('CS List error:', e); }
            this.loadingList = false;
        },

        async openDetail(csId) {
            this.showDetailModal = true;
            this.loadingDetail = true;
            this.csDetail = null;
            try {
                const params = new URLSearchParams({ id: csId, periode: this.filterPeriode });
                const res = await fetch(`/api/customer-service/cs-detail?${params}`);
                const json = await res.json();
                if (json.success) {
                    this.csDetail = json.data;
                    this.$nextTick(() => this.renderDetailChart());
                }
            } catch (e) { console.error('Detail error:', e); }
            this.loadingDetail = false;
        },

        renderDetailChart() {
            if (this.detailTrendChart) this.detailTrendChart.destroy();
            const ctx = document.getElementById('detailTrendChart');
            if (!ctx || !this.csDetail?.daily_trend?.length) return;

            this.detailTrendChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.csDetail.daily_trend.map(d => {
                        const dt = new Date(d.tanggal);
                        return dt.getDate() + '/' + (dt.getMonth() + 1);
                    }),
                    datasets: [{
                        label: 'Perolehan',
                        data: this.csDetail.daily_trend.map(d => d.total),
                        backgroundColor: 'rgba(5, 150, 105, 0.6)',
                        borderColor: 'rgb(5, 150, 105)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1000) + 'K' } },
                        x: { ticks: { maxRotation: 45, font: { size: 10 } } }
                    }
                }
            });
        },

        toggleSort(field) {
            if (this.sortBy === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortOrder = field === 'name' ? 'asc' : 'desc';
            }
            this.loadCsList();
        },

        // CRUD
        openAddForm() {
            this.formMode = 'add';
            this.formData = { id: null, name: '', team: '' };
            this.formError = '';
            this.showFormModal = true;
        },

        openEditForm(cs) {
            this.formMode = 'edit';
            this.formData = { id: cs.id, name: cs.name, team: cs.team };
            this.formError = '';
            this.showFormModal = true;
        },

        async saveCs() {
            if (!this.formData.name.trim() || !this.formData.team) {
                this.formError = 'Nama dan Tim wajib diisi';
                return;
            }
            this.formSaving = true;
            this.formError = '';
            try {
                const url = this.formMode === 'add'
                    ? '/api/customer-service/store'
                    : `/api/customer-service/${this.formData.id}/update`;
                const method = this.formMode === 'add' ? 'POST' : 'PUT';
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(this.formData),
                });
                const json = await res.json();
                if (json.success) {
                    this.showFormModal = false;
                    this.showToast(json.message, 'success');
                    this.refreshAll();
                } else {
                    this.formError = json.message || 'Terjadi kesalahan';
                }
            } catch (e) {
                this.formError = 'Gagal menyimpan: ' + e.message;
            }
            this.formSaving = false;
        },

        openDeleteModal(cs) {
            this.deleteTarget = cs;
            this.showDeleteModal = true;
        },

        async confirmDelete() {
            if (!this.deleteTarget) return;
            try {
                const res = await fetch(`/api/customer-service/${this.deleteTarget.id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const json = await res.json();
                this.showDeleteModal = false;
                if (json.success) {
                    this.showToast(json.message, 'success');
                    this.refreshAll();
                } else {
                    this.showToast(json.message || 'Gagal menghapus', 'error');
                }
            } catch (e) {
                this.showToast('Gagal menghapus: ' + e.message, 'error');
            }
        },

        // Helpers
        formatShort(n) {
            if (!n) return '0';
            if (n >= 1000000) return (n / 1000000).toFixed(1).replace('.0', '') + 'Jt';
            if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
            return n.toLocaleString('id-ID');
        },

        teamColor(team) {
            const colors = {
                'AB BARAT': 'bg-emerald-100 text-emerald-700',
                'WAKAF': 'bg-purple-100 text-purple-700',
                'CABANG': 'bg-blue-100 text-blue-700',
                'PLATFORM': 'bg-amber-100 text-amber-700',
                'PRODUK': 'bg-pink-100 text-pink-700',
                'OFFLINE': 'bg-gray-100 text-gray-700',
                'WANESIA': 'bg-rose-100 text-rose-700',
                'MEDIA': 'bg-sky-100 text-sky-700',
                'CS web ads': 'bg-indigo-100 text-indigo-700',
                'CRM': 'bg-teal-100 text-teal-700',
                'Partnership': 'bg-orange-100 text-orange-700',
                'Kencleng': 'bg-yellow-100 text-yellow-700',
            };
            return colors[team] || 'bg-gray-100 text-gray-600';
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
