<x-layouts.app active="partnership" title="Partnership" xData="partnershipApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<style>
    .ps-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .ps-spin { animation: ps-spin 1s linear infinite; }
    @keyframes ps-spin { to { transform: rotate(360deg); } }
    @keyframes ps-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Year filter */
    .ps-year-select {
        border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; background: white; outline: 2px solid transparent; cursor: pointer;
        font-weight: 600; color: #374151;
    }
    .ps-year-select:focus { border-color: #059669; box-shadow: 0 0 0 2px rgba(5,150,105,0.15); }

    /* YoY comparison */
    .ps-yoy-card {
        background: white; border-radius: 12px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;
    }
    .ps-yoy-header {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white; padding: 16px 20px; font-weight: 700; font-size: 15px;
        display: flex; align-items: center; gap: 8px;
    }
    .ps-yoy-table { width: 100%; font-size: 13px; border-collapse: collapse; }
    .ps-yoy-table th {
        padding: 10px 16px; text-align: left; font-size: 11px; color: #6B7280;
        text-transform: uppercase; letter-spacing: 0.05em; background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
    }
    .ps-yoy-table th:not(:first-child) { text-align: right; }
    .ps-yoy-table td {
        padding: 12px 16px; border-bottom: 1px solid #F3F4F6;
    }
    .ps-yoy-table td:not(:first-child) { text-align: right; }
    .ps-yoy-table tr:hover { background: #ECFDF5; }
    .ps-growth-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 8px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .ps-growth-up { background: #DCFCE7; color: #16A34A; }
    .ps-growth-down { background: #FEE2E2; color: #DC2626; }
    .ps-growth-neutral { background: #F3F4F6; color: #6B7280; }

    /* Monthly bar */
    .ps-bar-container { display: flex; align-items: flex-end; gap: 4px; height: 60px; }
    .ps-bar {
        flex: 1; border-radius: 3px 3px 0 0; min-width: 6px;
        transition: height 0.3s ease;
    }
    .ps-bar-label { font-size: 9px; color: #9CA3AF; text-align: center; }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-primary-600 ps-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data partnership...</span>
    </div>
</div>

{{-- ============ CREATE / EDIT MODAL ============ --}}
<div x-show="showFormModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Form Partnership"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showFormModal && closeFormModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeFormModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-primary-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingId ? 'Edit Partnership' : 'Tambah Partnership'"></h3>
            </div>
            <button @click="closeFormModal()" class="text-gray-400 hover:text-gray-600" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.tanggal" class="crud-field-input">
                    <template x-if="formErrors.tanggal"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.tanggal[0]"></p></template>
                </div>
                <div>
                    <label class="crud-field-label">Nama CS <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.nama_cs" class="crud-field-input" placeholder="Nama CS">
                    <template x-if="formErrors.nama_cs"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_cs[0]"></p></template>
                </div>
            </div>
            <div>
                <label class="crud-field-label">Jumlah Perolehan <span class="text-red-500">*</span></label>
                <input type="number" x-model="form.jml_perolehan" class="crud-field-input" placeholder="0" min="0">
                <template x-if="formErrors.jml_perolehan"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.jml_perolehan[0]"></p></template>
            </div>
            <div>
                <label class="crud-field-label">Nama Donatur</label>
                <input type="text" x-model="form.nama_donatur" class="crud-field-input" placeholder="Nama donatur / mitra">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Nama Bank</label>
                    <input type="text" x-model="form.nama_bank" class="crud-field-input" placeholder="Nama bank">
                </div>
                <div>
                    <label class="crud-field-label">No. Rekening</label>
                    <input type="text" x-model="form.no_rek" class="crud-field-input" placeholder="No. rekening">
                </div>
            </div>
            <div>
                <label class="crud-field-label">Keterangan</label>
                <textarea x-model="form.keterangan" class="crud-field-input" rows="2" placeholder="Catatan tambahan"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeFormModal()" class="crud-btn-secondary">Batal</button>
            <button @click="submitForm()" :disabled="saving" class="crud-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat ps-spin"></i></template>
                <span x-text="editingId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div x-show="showDeleteModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Partnership"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 backdrop-blur-sm"
     @keydown.escape.window="showDeleteModal && (showDeleteModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.outside="showDeleteModal = false">
        <div style="width:48px;height:48px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="bi bi-exclamation-triangle-fill text-xl text-red-600"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Hapus Data Partnership?</h3>
        <p class="text-sm text-gray-500 mb-4">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="crud-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="crud-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat ps-spin"></i></template>
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- ============ DETAIL MODAL ============ --}}
<div x-show="showDetailModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Detail Partnership"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showDetailModal && (showDetailModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" @click.outside="showDetailModal = false">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i class="bi bi-building text-primary-600 text-lg"></i>
                <h3 class="font-semibold text-gray-800">Detail Partnership</h3>
            </div>
            <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3" x-show="detailData">
            <template x-if="detailData">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Tanggal</span><span style="font-size:14px;font-weight:500" x-text="detailData.tanggal_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama CS</span><span style="font-size:14px;font-weight:500" x-text="detailData.nama_cs || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Jumlah Perolehan</span><span style="font-size:14px;font-weight:700;color:#059669" x-text="detailData.jml_perolehan_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Donatur</span><span style="font-size:14px" x-text="detailData.nama_donatur || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Bank</span><span style="font-size:14px" x-text="detailData.nama_bank || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">No. Rekening</span><span style="font-size:14px" x-text="detailData.no_rek || '-'"></span></div>
                    <div><span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Keterangan</span><p style="font-size:14px;background:#F9FAFB;border-radius:8px;padding:8px" x-text="detailData.keterangan || '-'"></p></div>
                </div>
            </template>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="showDetailModal = false" class="crud-btn-secondary">Tutup</button>
            <button @click="showDetailModal = false; openEditModal(detailData.id)" class="crud-btn-primary">
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
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600" aria-label="Toggle menu"><i class="bi bi-list text-xl"></i></button>
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-building text-primary-600 mr-1"></i> Partnership</h1>
    </div>
    <button @click="openCreateModal()" class="crud-btn-primary" style="font-size:12px">
        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah Data</span><span class="sm:hidden">Tambah</span>
    </button>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Year Filter + Stat Cards --}}
    <div class="flex flex-wrap items-center gap-3 mb-1">
        <div class="flex items-center gap-2">
            <i class="bi bi-calendar3 text-primary-600"></i>
            <select x-model="filters.year" @change="onYearChange()" class="ps-year-select">
                <option value="all">Semua Tahun</option>
                <template x-for="y in availableYears" :key="y">
                    <option :value="y" x-text="y"></option>
                </template>
            </select>
        </div>
        <span style="font-size:12px;color:#9CA3AF" x-show="filters.year !== 'all'" x-text="'Menampilkan data tahun ' + filters.year"></span>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="ps-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-clipboard-data text-primary-600"></i><span style="font-size:12px;color:#6B7280">Total Data</span></div>
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

    {{-- Year-over-Year Comparison --}}
    <div class="ps-yoy-card" x-show="yoyData.length > 0">
        <div class="ps-yoy-header">
            <i class="bi bi-graph-up-arrow"></i>
            Perbandingan Tahun ke Tahun
        </div>
        <div class="overflow-x-auto">
            <table class="ps-yoy-table">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Total Data</th>
                        <th>Total Perolehan</th>
                        <th>CS Aktif</th>
                        <th>Pertumbuhan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in yoyData" :key="row.year">
                        <tr>
                            <td>
                                <span style="font-weight:700;color:#059669;font-size:15px" x-text="row.year"></span>
                            </td>
                            <td style="font-weight:600" x-text="row.total_data"></td>
                            <td style="font-weight:700;color:#1F2937" x-text="row.total_perolehan_fmt"></td>
                            <td style="font-weight:600" x-text="row.total_cs"></td>
                            <td>
                                <template x-if="row.growth_pct !== null && row.growth_pct > 0">
                                    <span class="ps-growth-badge ps-growth-up">
                                        <i class="bi bi-arrow-up-short"></i>
                                        <span x-text="'+' + row.growth_pct + '%'"></span>
                                    </span>
                                </template>
                                <template x-if="row.growth_pct !== null && row.growth_pct < 0">
                                    <span class="ps-growth-badge ps-growth-down">
                                        <i class="bi bi-arrow-down-short"></i>
                                        <span x-text="row.growth_pct + '%'"></span>
                                    </span>
                                </template>
                                <template x-if="row.growth_pct !== null && row.growth_pct === 0">
                                    <span class="ps-growth-badge ps-growth-neutral">
                                        <i class="bi bi-dash"></i> 0%
                                    </span>
                                </template>
                                <template x-if="row.growth_pct === null">
                                    <span style="font-size:12px;color:#9CA3AF">—</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Monthly Bars per Year --}}
        <div class="p-4" x-show="yoyData.length > 0">
            <p style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:12px">Perolehan Bulanan per Tahun</p>
            <div class="space-y-4">
                <template x-for="row in yoyData" :key="'bar-'+row.year">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span style="font-size:12px;font-weight:700;color:#059669" x-text="row.year"></span>
                            <span style="font-size:11px;color:#9CA3AF" x-text="row.total_perolehan_fmt"></span>
                        </div>
                        <div class="ps-bar-container">
                            <template x-for="(m, mi) in row.monthly" :key="'m-'+row.year+'-'+mi">
                                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px">
                                    <div class="ps-bar"
                                         :style="'height:' + getBarHeight(m.total, row.monthly) + 'px;background:' + getYearColor(row.year)"
                                         :title="m.bulan + ': Rp ' + Number(m.total).toLocaleString('id-ID')"></div>
                                    <span class="ps-bar-label" x-text="m.bulan"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2">
        <div class="relative flex-1" style="min-width:180px">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
            <input type="text" x-model="filters.search" @input.debounce.500ms="resetAndLoad()"
                   class="crud-field-input" style="padding-left:36px" placeholder="Cari nama CS, donatur, keterangan...">
        </div>
        <input type="date" x-model="filters.date_from" @change="resetAndLoad()" class="crud-field-input" style="width:auto" title="Dari tanggal">
        <input type="date" x-model="filters.date_to" @change="resetAndLoad()" class="crud-field-input" style="width:auto" title="Sampai tanggal">
        <button @click="clearFilters()" style="font-size:12px;color:#6B7280;cursor:pointer;border:none;background:none" title="Reset Filter" aria-label="Reset filter">
            <i class="bi bi-x-circle"></i>
        </button>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
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
                        <tr style="cursor:pointer;transition:background 0.15s" class="hover:bg-primary-50/40" @click="openDetailModal(row.id)">
                            <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="pagination.from + idx"></td>
                            <td style="padding:12px 16px;color:#4B5563" x-text="row.tanggal_fmt"></td>
                            <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama_cs || '-'"></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;color:#059669" x-text="row.jml_perolehan_fmt"></td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden md:table-cell" x-text="row.nama_donatur || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden lg:table-cell" x-text="row.nama_bank || '-'"></td>
                            <td style="padding:12px 16px;color:#6B7280;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="hidden xl:table-cell" x-text="row.keterangan || '-'"></td>
                            <td style="padding:12px 16px;text-align:center" @click.stop>
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#D97706" title="Edit" aria-label="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button @click="confirmDelete(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#EF4444" title="Hapus" aria-label="Hapus">
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
                <button class="pagination-btn" @click="goToPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        aria-label="Halaman sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="pagination-btn" @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        aria-label="Halaman berikutnya">
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
        sidebarOpen: false,
        loading: true,
        loadingTable: false,
        saving: false,
        showFormModal: false,
        showDeleteModal: false,
        showDetailModal: false,
        editingId: null,
        deletingId: null,
        detailData: null,

        stats: {},
        tableData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        filters: { search: '', date_from: '', date_to: '', sort: 'tanggal', order: 'desc', year: 'all' },
        form: { tanggal: '', nama_cs: '', jml_perolehan: '', nama_donatur: '', nama_bank: '', no_rek: '', keterangan: '' },
        formErrors: {},

        availableYears: [],
        yoyData: [],
        yearColors: ['#059669', '#2563EB', '#10B981', '#D97706', '#DC2626', '#6366F1', '#0891B2'],

        async init() {
            try {
                await Promise.all([this.loadYears(), this.loadStats(), this.loadTable(), this.loadYoyComparison()]);
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        async loadYears() {
            try {
                var res = await fetch('/api/partnership/years', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.availableYears = await res.json();
            } catch (e) { console.error('Years error', e); }
        },

        async loadYoyComparison() {
            try {
                var res = await fetch('/api/partnership/year-comparison', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.yoyData = await res.json();
            } catch (e) { console.error('YoY error', e); }
        },

        onYearChange: function() {
            this.loadStats();
            this.loadTable(1);
        },

        getBarHeight: function(value, monthly) {
            var max = 0;
            for (var i = 0; i < monthly.length; i++) {
                if (monthly[i].total > max) max = monthly[i].total;
            }
            if (max === 0) return 2;
            return Math.max(2, Math.round((value / max) * 55));
        },

        getYearColor: function(year) {
            var idx = this.availableYears.indexOf(year);
            if (idx === -1) idx = 0;
            return this.yearColors[idx % this.yearColors.length];
        },

        async loadStats() {
            try {
                var url = '/api/partnership/stats';
                if (this.filters.year && this.filters.year !== 'all') {
                    url += '?year=' + encodeURIComponent(this.filters.year);
                }
                var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.stats = await res.json();
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
                if (this.filters.year && this.filters.year !== 'all') {
                    qs += '&year=' + encodeURIComponent(this.filters.year);
                }

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
        clearFilters: function() {
            this.filters = { search: '', date_from: '', date_to: '', sort: 'tanggal', order: 'desc', year: 'all' };
            this.loadStats();
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
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page), this.loadYears(), this.loadYoyComparison()]);
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
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page), this.loadYears(), this.loadYoyComparison()]);
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

        showToast: window.showToast,
    };
}
</script>
@endpush

</x-layouts.app>
