<x-layouts.app active="partnership" title="Partnership" xData="partnershipApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<style>
    .ps-field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; transition: all 0.15s; background: white; outline: none;
    }
    .ps-field-input:focus { border-color: #8B5CF6; box-shadow: 0 0 0 2px rgba(139,92,246,0.15); }
    .ps-field-label { display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px; }
    .ps-btn-primary {
        background: #7C3AED; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .ps-btn-primary:hover { background: #6D28D9; }
    .ps-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
    .ps-btn-secondary {
        background: #F3F4F6; color: #374151; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
    }
    .ps-btn-secondary:hover { background: #E5E7EB; }
    .ps-btn-danger {
        background: #DC2626; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .ps-btn-danger:hover { background: #B91C1C; }
    .ps-btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
    .ps-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .ps-spin { animation: ps-spin 1s linear infinite; }
    @keyframes ps-spin { to { transform: rotate(360deg); } }
    @keyframes ps-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Yearly Comparison */
    .ps-year-section { background: white; border-radius: 12px; border: 1px solid #F3F4F6; overflow: hidden; }
    .ps-year-section-header {
        padding: 14px 16px; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center;
        justify-content: space-between; gap: 8px;
    }
    .ps-year-table { width: 100%; font-size: 14px; border-collapse: collapse; }
    .ps-year-table thead { background: #F9FAFB; font-size: 11px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; }
    .ps-year-table th, .ps-year-table td { padding: 12px 16px; text-align: left; }
    .ps-year-table tbody tr { border-top: 1px solid #F3F4F6; transition: background 0.15s; }
    .ps-year-table tbody tr:hover { background: #FAFAFE; }
    .ps-growth-badge {
        display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px;
        border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .ps-growth-up { background: #DCFCE7; color: #16A34A; }
    .ps-growth-down { background: #FEE2E2; color: #DC2626; }
    .ps-growth-neutral { background: #F3F4F6; color: #6B7280; }
    .ps-year-filter {
        padding: 6px 10px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 13px;
        background: white; outline: none; cursor: pointer; color: #374151;
    }
    .ps-year-filter:focus { border-color: #8B5CF6; box-shadow: 0 0 0 2px rgba(139,92,246,0.15); }
    .ps-tab { padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all 0.2s; }
    .ps-tab-active { background: #7C3AED; color: white; }
    .ps-tab-inactive { background: #F3F4F6; color: #6B7280; }
    .ps-tab-inactive:hover { background: #E5E7EB; }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-purple-600 ps-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data partnership...</span>
    </div>
</div>

{{-- ============ CREATE / EDIT MODAL ============ --}}
<div x-show="showFormModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showFormModal && closeFormModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeFormModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-purple-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingId ? 'Edit Partnership' : 'Tambah Partnership'"></h3>
            </div>
            <button @click="closeFormModal()" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ps-field-label">Tanggal <span style="color:#EF4444">*</span></label>
                    <input type="date" x-model="form.tanggal" class="ps-field-input">
                    <template x-if="formErrors.tanggal"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.tanggal[0]"></p></template>
                </div>
                <div>
                    <label class="ps-field-label">Nama CS <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.nama_cs" class="ps-field-input" placeholder="Nama CS">
                    <template x-if="formErrors.nama_cs"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_cs[0]"></p></template>
                </div>
            </div>
            <div>
                <label class="ps-field-label">Jumlah Perolehan <span style="color:#EF4444">*</span></label>
                <input type="number" x-model="form.jml_perolehan" class="ps-field-input" placeholder="0" min="0">
                <template x-if="formErrors.jml_perolehan"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.jml_perolehan[0]"></p></template>
            </div>
            <div>
                <label class="ps-field-label">Nama Donatur</label>
                <input type="text" x-model="form.nama_donatur" class="ps-field-input" placeholder="Nama donatur / mitra">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ps-field-label">Nama Bank</label>
                    <input type="text" x-model="form.nama_bank" class="ps-field-input" placeholder="Nama bank">
                </div>
                <div>
                    <label class="ps-field-label">No. Rekening</label>
                    <input type="text" x-model="form.no_rek" class="ps-field-input" placeholder="No. rekening">
                </div>
            </div>
            <div>
                <label class="ps-field-label">Keterangan</label>
                <textarea x-model="form.keterangan" class="ps-field-input" rows="2" placeholder="Catatan tambahan"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeFormModal()" class="ps-btn-secondary">Batal</button>
            <button @click="submitForm()" :disabled="saving" class="ps-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat ps-spin"></i></template>
                <span x-text="editingId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div x-show="showDeleteModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 backdrop-blur-sm"
     @keydown.escape.window="showDeleteModal && (showDeleteModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.outside="showDeleteModal = false">
        <div style="width:48px;height:48px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="bi bi-exclamation-triangle-fill text-xl" style="color:#DC2626"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Hapus Data Partnership?</h3>
        <p class="text-sm text-gray-500 mb-4">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="ps-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="ps-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat ps-spin"></i></template>
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- ============ DETAIL MODAL ============ --}}
<div x-show="showDetailModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showDetailModal && (showDetailModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" @click.outside="showDetailModal = false">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i class="bi bi-building text-purple-600 text-lg"></i>
                <h3 class="font-semibold text-gray-800">Detail Partnership</h3>
            </div>
            <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3" x-show="detailData">
            <template x-if="detailData">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Tanggal</span><span style="font-size:14px;font-weight:500" x-text="detailData.tanggal_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama CS</span><span style="font-size:14px;font-weight:500" x-text="detailData.nama_cs || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Jumlah Perolehan</span><span style="font-size:14px;font-weight:700;color:#7C3AED" x-text="detailData.jml_perolehan_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Donatur</span><span style="font-size:14px" x-text="detailData.nama_donatur || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Bank</span><span style="font-size:14px" x-text="detailData.nama_bank || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">No. Rekening</span><span style="font-size:14px" x-text="detailData.no_rek || '-'"></span></div>
                    <div><span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Keterangan</span><p style="font-size:14px;background:#F9FAFB;border-radius:8px;padding:8px" x-text="detailData.keterangan || '-'"></p></div>
                </div>
            </template>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="showDetailModal = false" class="ps-btn-secondary">Tutup</button>
            <button @click="showDetailModal = false; openEditModal(detailData.id)" class="ps-btn-primary">
                <i class="bi bi-pencil-square"></i> Edit
            </button>
        </div>
    </div>
</div>
@endpush

{{-- ===== MAIN CONTENT ===== --}}

{{-- Sticky Header --}}
<div class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button @click="$dispatch('toggle-sidebar')" class="lg:hidden text-gray-600"><i class="bi bi-list text-xl"></i></button>
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-building text-purple-600 mr-1"></i> Partnership</h1>
    </div>
    <button @click="openCreateModal()" class="ps-btn-primary" style="font-size:12px">
        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah Data</span><span class="sm:hidden">Tambah</span>
    </button>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="ps-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-clipboard-data text-purple-600"></i><span style="font-size:12px;color:#6B7280">Total Data</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total_data ?? '—'"></p>
        </div>
        <div class="ps-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-cash-stack text-emerald-600"></i><span style="font-size:12px;color:#6B7280">Total Perolehan</span></div>
            <p style="font-size:18px;font-weight:700;color:#1F2937" x-text="stats.total_perolehan ?? '—'"></p>
        </div>
        <div class="ps-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-people-fill text-sky-600"></i><span style="font-size:12px;color:#6B7280">CS Aktif</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total_cs ?? '—'"></p>
        </div>
        <div class="ps-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-calendar-event text-amber-600"></i><span style="font-size:12px;color:#6B7280">Data Terbaru</span></div>
            <p style="font-size:16px;font-weight:700;color:#1F2937" x-text="stats.latest_date ?? '—'"></p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2">
        {{-- Year Filter --}}
        <select x-model="filters.year" @change="onYearChange()" class="ps-year-filter" title="Filter Tahun">
            <option value="all">Semua Tahun</option>
            <template x-for="y in availableYears" :key="y">
                <option :value="y" x-text="y"></option>
            </template>
        </select>
        <div style="width:1px;height:24px;background:#E5E7EB" class="hidden sm:block"></div>
        <div class="relative flex-1" style="min-width:180px">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
            <input type="text" x-model="filters.search" @input.debounce.500ms="resetAndLoad()"
                   class="ps-field-input" style="padding-left:36px" placeholder="Cari nama CS, donatur, keterangan...">
        </div>
        <input type="date" x-model="filters.date_from" @change="resetAndLoad()" class="ps-field-input" style="width:auto" title="Dari tanggal">
        <input type="date" x-model="filters.date_to" @change="resetAndLoad()" class="ps-field-input" style="width:auto" title="Sampai tanggal">
        <button @click="clearFilters()" style="font-size:12px;color:#6B7280;cursor:pointer;border:none;background:none" title="Reset Filter">
            <i class="bi bi-x-circle"></i>
        </button>
    </div>

    {{-- View Tabs --}}
    <div class="flex items-center gap-2">
        <button @click="activeView = 'data'" class="ps-tab" :class="activeView === 'data' ? 'ps-tab-active' : 'ps-tab-inactive'">
            <i class="bi bi-table"></i> Data
        </button>
        <button @click="activeView = 'yearly'; loadYearlyComparison()" class="ps-tab" :class="activeView === 'yearly' ? 'ps-tab-active' : 'ps-tab-inactive'">
            <i class="bi bi-bar-chart-line"></i> Perbandingan Tahunan
        </button>
    </div>

    {{-- ============ YEARLY COMPARISON VIEW ============ --}}
    <template x-if="activeView === 'yearly'">
        <div class="space-y-4">
            {{-- Yearly Summary Table --}}
            <div class="ps-year-section">
                <div class="ps-year-section-header">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-bar-chart-line text-purple-600"></i>
                        <h3 style="font-weight:600;font-size:15px;color:#1F2937">Perbandingan Perolehan Tahunan</h3>
                    </div>
                </div>
                <div x-show="loadingYearly" style="padding:40px;text-align:center;color:#9CA3AF">
                    <i class="bi bi-arrow-repeat ps-spin" style="margin-right:4px"></i> Memuat data tahunan...
                </div>
                <div x-show="!loadingYearly" class="overflow-x-auto">
                    <table class="ps-year-table">
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th style="text-align:right">Total Data</th>
                                <th style="text-align:right">Total Perolehan</th>
                                <th style="text-align:right">CS Aktif</th>
                                <th style="text-align:center">Pertumbuhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in yearlyData" :key="row.year">
                                <tr>
                                    <td>
                                        <span style="font-weight:700;font-size:16px;color:#7C3AED" x-text="row.year"></span>
                                    </td>
                                    <td style="text-align:right;font-weight:500" x-text="row.total_data.toLocaleString('id-ID')"></td>
                                    <td style="text-align:right;font-weight:700;color:#1F2937" x-text="row.total_perolehan_fmt"></td>
                                    <td style="text-align:right" x-text="row.total_cs"></td>
                                    <td style="text-align:center">
                                        <template x-if="idx < yearlyData.length - 1">
                                            <span class="ps-growth-badge"
                                                  :class="yearlyGrowth(idx) > 0 ? 'ps-growth-up' : yearlyGrowth(idx) < 0 ? 'ps-growth-down' : 'ps-growth-neutral'">
                                                <i class="bi" :class="yearlyGrowth(idx) > 0 ? 'bi-arrow-up-short' : yearlyGrowth(idx) < 0 ? 'bi-arrow-down-short' : 'bi-dash'"></i>
                                                <span x-text="Math.abs(yearlyGrowth(idx)).toFixed(1) + '%'"></span>
                                            </span>
                                        </template>
                                        <template x-if="idx === yearlyData.length - 1">
                                            <span class="ps-growth-badge ps-growth-neutral">—</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="yearlyData.length === 0 && !loadingYearly">
                                <td colspan="5" style="text-align:center;padding:24px;color:#9CA3AF">Tidak ada data</td>
                            </tr>
                        </tbody>
                        {{-- Totals row --}}
                        <tfoot x-show="yearlyData.length > 0">
                            <tr style="background:#F9FAFB;border-top:2px solid #E5E7EB;font-weight:700">
                                <td>Total</td>
                                <td style="text-align:right" x-text="yearlyData.reduce((s,r) => s + r.total_data, 0).toLocaleString('id-ID')"></td>
                                <td style="text-align:right;color:#7C3AED" x-text="'Rp ' + yearlyData.reduce((s,r) => s + r.total_perolehan, 0).toLocaleString('id-ID')"></td>
                                <td style="text-align:right">—</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Monthly Breakdown per Year --}}
            <template x-for="yearRow in yearlyData" :key="'detail-' + yearRow.year">
                <div class="ps-year-section" x-show="yearlyMonthly[yearRow.year]">
                    <div class="ps-year-section-header" style="cursor:pointer" @click="toggleYearExpand(yearRow.year)">
                        <div class="flex items-center gap-2">
                            <i class="bi text-purple-600" :class="expandedYears.includes(yearRow.year) ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                            <span style="font-weight:600;font-size:14px;color:#1F2937" x-text="'Detail Bulanan ' + yearRow.year"></span>
                        </div>
                        <span style="font-size:12px;color:#6B7280" x-text="yearRow.total_perolehan_fmt"></span>
                    </div>
                    <div x-show="expandedYears.includes(yearRow.year)" x-collapse class="overflow-x-auto">
                        <table class="ps-year-table">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th style="text-align:right">Total Data</th>
                                    <th style="text-align:right">Total Perolehan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="m in yearlyMonthly[yearRow.year] || []" :key="m.month">
                                    <tr>
                                        <td x-text="monthName(m.month)"></td>
                                        <td style="text-align:right" x-text="m.total_data.toLocaleString('id-ID')"></td>
                                        <td style="text-align:right;font-weight:600;color:#7C3AED" x-text="'Rp ' + m.total_perolehan.toLocaleString('id-ID')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Data Table --}}
    <div x-show="activeView === 'data'" class="bg-white rounded-xl border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" style="font-size:14px">
                <thead style="background:#F9FAFB;font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em">
                    <tr>
                        <th style="padding:12px 16px;text-align:left;width:40px">#</th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('tanggal')">
                            Tanggal <i class="bi" :class="sortIcon('tanggal')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('nama_cs')">
                            Nama CS <i class="bi" :class="sortIcon('nama_cs')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:right;cursor:pointer" @click="sortBy('jml_perolehan')">
                            Perolehan <i class="bi" :class="sortIcon('jml_perolehan')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden md:table-cell" @click="sortBy('nama_donatur')">
                            Donatur <i class="bi" :class="sortIcon('nama_donatur')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left" class="hidden lg:table-cell">Bank</th>
                        <th style="padding:12px 16px;text-align:left" class="hidden xl:table-cell">Keterangan</th>
                        <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr x-show="loadingTable">
                        <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat ps-spin" style="margin-right:4px"></i> Memuat...</td>
                    </tr>
                    <tr x-show="!loadingTable && tableData.length === 0">
                        <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                    </tr>
                    <template x-for="(row, idx) in tableData" :key="row.id">
                        <tr style="cursor:pointer;transition:background 0.15s" class="hover:bg-purple-50/40" @click="openDetailModal(row.id)">
                            <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="pagination.from + idx"></td>
                            <td style="padding:12px 16px;color:#4B5563" x-text="row.tanggal_fmt"></td>
                            <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama_cs || '-'"></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;color:#7C3AED" x-text="row.jml_perolehan_fmt"></td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden md:table-cell" x-text="row.nama_donatur || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden lg:table-cell" x-text="row.nama_bank || '-'"></td>
                            <td style="padding:12px 16px;color:#6B7280;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="hidden xl:table-cell" x-text="row.keterangan || '-'"></td>
                            <td style="padding:12px 16px;text-align:center" @click.stop>
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#D97706" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button @click="confirmDelete(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#EF4444" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-4 py-3 border-t" style="font-size:12px;color:#6B7280">
            <span x-show="pagination.total > 0" x-text="'Menampilkan ' + pagination.from + '–' + pagination.to + ' dari ' + pagination.total"></span>
            <span x-show="pagination.total === 0">Tidak ada data</span>
            <div class="flex gap-1">
                <button @click="goToPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                        :style="!pagination.prev_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                        :style="!pagination.next_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== ALPINE.JS APP ===== --}}
@push('scripts')
<script>
function partnershipApp() {
    return {
        loading: true,
        loadingTable: false,
        loadingYearly: false,
        saving: false,
        showFormModal: false,
        showDeleteModal: false,
        showDetailModal: false,
        editingId: null,
        deletingId: null,
        detailData: null,
        activeView: 'data',

        stats: {},
        tableData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        filters: { search: '', date_from: '', date_to: '', sort: 'tanggal', order: 'desc', year: 'all' },
        form: { tanggal: '', nama_cs: '', jml_perolehan: '', nama_donatur: '', nama_bank: '', no_rek: '', keterangan: '' },
        formErrors: {},

        // Yearly comparison
        availableYears: [],
        yearlyData: [],
        yearlyMonthly: {},
        expandedYears: [],

        async init() {
            try {
                await Promise.all([this.loadStats(), this.loadTable()]);
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        async loadStats() {
            try {
                var qs = '?year=' + encodeURIComponent(this.filters.year);
                var res = await fetch('/api/partnership/stats' + qs, { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    var data = await res.json();
                    this.stats = data;
                    if (data.years) this.availableYears = data.years;
                }
            } catch (e) { console.error('Stats error', e); }
        },

        async loadTable(page) {
            if (page === undefined) page = 1;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.filters.search);
                qs += '&date_from=' + encodeURIComponent(this.filters.date_from);
                qs += '&date_to=' + encodeURIComponent(this.filters.date_to);
                qs += '&sort=' + encodeURIComponent(this.filters.sort);
                qs += '&order=' + encodeURIComponent(this.filters.order);
                qs += '&year=' + encodeURIComponent(this.filters.year);

                var res = await fetch('/api/partnership/list?' + qs, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) { console.error('List API error', res.status); this.loadingTable = false; return; }
                var json = await res.json();
                this.tableData = json.data || [];
                this.pagination = {
                    current_page: json.current_page || 1,
                    last_page: json.last_page || 1,
                    from: json.from || 0,
                    to: json.to || 0,
                    total: json.total || 0,
                    prev_page_url: json.prev_page_url,
                    next_page_url: json.next_page_url,
                };
            } catch (e) { console.error('Table error', e); }
            this.loadingTable = false;
        },

        resetAndLoad: function() { this.loadTable(1); },
        clearFilters: function() {, year: 'all' };
            Promise.all([this.loadStats(), this.loadTable(1)] search: '', date_from: '', date_to: '', sort: 'tanggal', order: 'desc' };
            this.loadTable(1);
        },
        sortBy: function(col) {
            if (this.filters.sort === col) {
                this.filters.order = this.filters.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sort = col;
                this.filters.order = 'asc';
            }
            this.loadTable(1);
        },
        sortIcon: function(col) {
            if (this.filters.sort !== col) return 'bi-chevron-expand';
            return this.filters.order === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },
        goToPage: function(p) {
            if (p < 1 || p > this.pagination.last_page) return;
            this.loadTable(p);
        },

        resetForm: function() {
            this.form = { tanggal: '', nama_cs: '', jml_perolehan: '', nama_donatur: '', nama_bank: '', no_rek: '', keterangan: '' };
            this.formErrors = {};
        },
        openCreateModal: function() {
            this.editingId = null;
            this.resetForm();
            this.showFormModal = true;
        },
        openEditModal: async function(id) {
            this.editingId = id;
            this.resetForm();
            this.showFormModal = true;
            try {
                var res = await fetch('/api/partnership/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.form = {
                    tanggal: data.tanggal ? data.tanggal.substring(0, 10) : '',
                    nama_cs: data.nama_cs || '',
                    jml_perolehan: data.jml_perolehan || '',
                    nama_donatur: data.nama_donatur || '',
                    nama_bank: data.nama_bank || '',
                    no_rek: data.no_rek || '',
                    keterangan: data.keterangan || '',
                };
            } catch (e) {
                this.showToast('Gagal memuat data', 'error');
                this.showFormModal = false;
            }
        },
        closeFormModal: function() { this.showFormModal = false; this.editingId = null; this.resetForm(); },

        submitForm: async function() {
            this.saving = true;
            this.formErrors = {};
            try {
                var url = this.editingId ? '/api/partnership/' + this.editingId : '/api/partnership';
                var method = this.editingId ? 'PUT' : 'POST';
                var payload = {};
                for (var k in this.form) payload[k] = this.form[k];
                if (payload.jml_perolehan === '') payload.jml_perolehan = 0;
                var csrfEl = document.querySelector('meta[name="csrf-token"]');
                var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                if (csrfEl) headers['X-CSRF-TOKEN'] = csrfEl.content;

                var res = await fetch(url, { method: method, headers: headers, body: JSON.stringify(payload) });
                var json = await res.json();
                if (!res.ok) {
                    if (json.errors) this.formErrors = json.errors;
                    this.showToast(json.message || 'Validasi gagal', 'error');
                    this.saving = false;
                    return;
                }
                this.showToast(json.message || 'Berhasil!', 'success');
                this.closeFormModal();
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page)]);
            } catch (e) { this.showToast('Terjadi kesalahan', 'error'); }
            this.saving = false;
        },

        confirmDelete: function(id) { this.deletingId = id; this.showDeleteModal = true; },
        executeDelete: async function() {
            this.saving = true;
            try {
                var csrfEl = document.querySelector('meta[name="csrf-token"]');
                var headers = { 'Accept': 'application/json' };
                if (csrfEl) headers['X-CSRF-TOKEN'] = csrfEl.content;
                var res = await fetch('/api/partnership/' + this.deletingId, { method: 'DELETE', headers: headers });
                var json = await res.json();
                this.showToast(json.message || 'Dihapus', 'success');
                this.showDeleteModal = false;
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page)]);
            } catch (e) { this.showToast('Gagal menghapus', 'error'); }
            this.saving = false;
        },

        openDetailModal: async function(id) {
            try {
                var res = await fetch('/api/partnership/' + id, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                var data = await res.json();
                this.detailData = data;
                this.showDetailModal = true;
            } catch (e) { this.showToast('Gagal memuat detail', 'error'); }
        },

        // Year filter change handler
        onYearChange: function() {
            Promise.all([this.loadStats(), this.loadTable(1)]);
        },

        // Load yearly comparison data
        loadYearlyComparison: async function() {
            if (this.yearlyData.length > 0) return; // already loaded
            this.loadingYearly = true;
            try {
                var res = await fetch('/api/partnership/yearly-comparison', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    var json = await res.json();
                    this.yearlyData = json.yearly || [];
                    this.yearlyMonthly = json.monthly_breakdown || {};
                }
            } catch (e) { console.error('Yearly comparison error', e); }
            this.loadingYearly = false;
        },

        // Calculate YoY growth percentage (idx is current year row, idx+1 is previous year)
        yearlyGrowth: function(idx) {
            if (idx >= this.yearlyData.length - 1) return 0;
            var current = this.yearlyData[idx].total_perolehan;
            var previous = this.yearlyData[idx + 1].total_perolehan;
            if (previous === 0) return current > 0 ? 100 : 0;
            return ((current - previous) / previous) * 100;
        },

        // Month name helper
        monthName: function(m) {
            var names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return names[m] || 'Bulan ' + m;
        },

        // Toggle year detail expansion
        toggleYearExpand: function(year) {
            var idx = this.expandedYears.indexOf(year);
            if (idx === -1) { this.expandedYears.push(year); }
            else { this.expandedYears.splice(idx, 1); }
        },

        showToast: function(message, type) {
            if (!type) type = 'success';
            var bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#7c3aed';
            var icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'x-circle-fill' : 'info-circle-fill';
            var toast = document.createElement('div');
            toast.innerHTML = '<i class="bi bi-' + icon + '"></i> ' + message;
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:ps-fadeInUp .3s ease;max-width:90vw';
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
