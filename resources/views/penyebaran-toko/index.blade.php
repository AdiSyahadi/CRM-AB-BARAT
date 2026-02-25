<x-layouts.app active="penyebaran-toko" title="Penyebaran Toko" xData="penyebaranTokoApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    /* ---- Map styles ---- */
    .pt-map-container {
        background: white; border-radius: 12px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;
    }
    .pt-map-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px; border-bottom: 1px solid #F3F4F6;
    }
    .pt-map-header h3 { font-size: 14px; font-weight: 600; color: #1F2937; display: flex; align-items: center; gap: 6px; }
    .pt-map-legend { display: flex; align-items: center; gap: 12px; font-size: 12px; color: #6B7280; }
    .pt-map-legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px; }
    #penyebaranMap { height: 420px; width: 100%; z-index: 1; }
    .pt-map-info { padding: 8px 16px; background: #F9FAFB; font-size: 12px; color: #6B7280; display: flex; align-items: center; gap: 6px; }
    .pt-view-toggle {
        display: inline-flex; border-radius: 10px; overflow: hidden; border: 1px solid #E5E7EB; background: #F3F4F6;
    }
    .pt-view-btn {
        padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
        border: none; background: transparent; color: #6B7280; transition: all 0.15s;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .pt-view-btn.active { background: #059669; color: white; }
    .pt-view-btn:hover:not(.active) { background: #E5E7EB; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; box-shadow: 0 4px 12px rgba(0,0,0,.12) !important; }
    .leaflet-popup-content { margin: 10px 14px !important; font-size: 13px; line-height: 1.5; }
    .pt-popup-title { font-weight: 600; color: #1F2937; margin-bottom: 4px; font-size: 14px; }
    .pt-popup-row { color: #4B5563; font-size: 12px; margin-bottom: 2px; }
    .pt-popup-row span { font-weight: 500; color: #1F2937; }
    .pt-popup-badge { display: inline-block; padding: 1px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; }

    /* ---- Existing styles ---- */
    .pt-field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; transition: all 0.15s; background: white; outline: none;
    }
    .pt-field-input:focus { border-color: #059669; box-shadow: 0 0 0 2px rgba(5,150,105,0.15); }
    .pt-field-label { display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px; }
    .pt-btn-primary {
        background: #059669; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .pt-btn-primary:hover { background: #047857; }
    .pt-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
    .pt-btn-secondary {
        background: #F3F4F6; color: #374151; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
    }
    .pt-btn-secondary:hover { background: #E5E7EB; }
    .pt-btn-danger {
        background: #DC2626; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .pt-btn-danger:hover { background: #B91C1C; }
    .pt-btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
    .pt-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .pt-badge-success { background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 500; }
    .pt-badge-danger { background: #FEE2E2; color: #991B1B; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 500; }
    .pt-spin { animation: pt-spin 1s linear infinite; }
    @keyframes pt-spin { to { transform: rotate(360deg); } }
    @keyframes pt-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-emerald-600 pt-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data penyebaran toko...</span>
    </div>
</div>

{{-- ============ CREATE / EDIT MODAL ============ --}}
<div x-show="showFormModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showFormModal && closeFormModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeFormModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-emerald-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingId ? 'Edit Penyebaran Toko' : 'Tambah Penyebaran Toko'"></h3>
            </div>
            <button @click="closeFormModal()" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="pt-field-label">Tanggal Registrasi <span style="color:#EF4444">*</span></label>
                    <input type="date" x-model="form.tanggal_registrasi" class="pt-field-input">
                    <template x-if="formErrors.tanggal_registrasi"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.tanggal_registrasi[0]"></p></template>
                </div>
                <div>
                    <label class="pt-field-label">Nama CS <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.nama_cs" class="pt-field-input" placeholder="Nama CS">
                    <template x-if="formErrors.nama_cs"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_cs[0]"></p></template>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="pt-field-label">Nama Toko <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.nama_toko" class="pt-field-input" placeholder="Nama toko">
                    <template x-if="formErrors.nama_toko"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_toko[0]"></p></template>
                </div>
                <div>
                    <label class="pt-field-label">Nama Donatur <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.nama_donatur" class="pt-field-input" placeholder="Nama donatur">
                    <template x-if="formErrors.nama_donatur"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_donatur[0]"></p></template>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="pt-field-label">No. Kencleng <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.nomor_kencleng" class="pt-field-input" placeholder="Nomor kencleng">
                    <template x-if="formErrors.nomor_kencleng"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nomor_kencleng[0]"></p></template>
                </div>
                <div>
                    <label class="pt-field-label">No. HP <span style="color:#EF4444">*</span></label>
                    <input type="text" x-model="form.no_hp" class="pt-field-input" placeholder="08xxxxxxxxxx">
                    <template x-if="formErrors.no_hp"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.no_hp[0]"></p></template>
                </div>
            </div>
            <div>
                <label class="pt-field-label">Alamat <span style="color:#EF4444">*</span></label>
                <textarea x-model="form.alamat" class="pt-field-input" rows="2" placeholder="Alamat lengkap"></textarea>
                <template x-if="formErrors.alamat"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.alamat[0]"></p></template>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="pt-field-label">Status</label>
                    <select x-model="form.status" class="pt-field-input">
                        <option value="Di terima">Di terima</option>
                        <option value="Di tolak">Di tolak</option>
                    </select>
                </div>
                <div>
                    <label class="pt-field-label">Keterangan</label>
                    <input type="text" x-model="form.keterangan" class="pt-field-input" placeholder="Catatan">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="pt-field-label">Latitude</label>
                    <input type="text" x-model="form.latitude" class="pt-field-input" placeholder="-6.xxxxx">
                </div>
                <div>
                    <label class="pt-field-label">Longitude</label>
                    <input type="text" x-model="form.longitude" class="pt-field-input" placeholder="106.xxxxx">
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeFormModal()" class="pt-btn-secondary">Batal</button>
            <button @click="submitForm()" :disabled="saving" class="pt-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat pt-spin"></i></template>
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
        <h3 class="font-semibold text-gray-800 mb-1">Hapus Data Penyebaran Toko?</h3>
        <p class="text-sm text-gray-500 mb-4">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="pt-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="pt-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat pt-spin"></i></template>
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
                <i class="bi bi-geo-alt-fill text-emerald-600 text-lg"></i>
                <h3 class="font-semibold text-gray-800">Detail Penyebaran Toko</h3>
            </div>
            <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3" x-show="detailData">
            <template x-if="detailData">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Tanggal</span><span style="font-size:14px;font-weight:500" x-text="detailData.tanggal_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama CS</span><span style="font-size:14px;font-weight:500" x-text="detailData.nama_cs || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Toko</span><span style="font-size:14px;font-weight:500" x-text="detailData.nama_toko || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama Donatur</span><span style="font-size:14px" x-text="detailData.nama_donatur || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">No. Kencleng</span><span style="font-size:14px" x-text="detailData.nomor_kencleng || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">No. HP</span><span style="font-size:14px" x-text="detailData.no_hp || '-'"></span></div>
                    <div class="flex justify-between items-center">
                        <span style="font-size:12px;color:#6B7280">Status</span>
                        <span :class="detailData.status === 'Di terima' ? 'pt-badge-success' : 'pt-badge-danger'" x-text="detailData.status || '-'"></span>
                    </div>
                    <div><span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Alamat</span><p style="font-size:14px;background:#F9FAFB;border-radius:8px;padding:8px" x-text="detailData.alamat || '-'"></p></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Keterangan</span><span style="font-size:14px" x-text="detailData.keterangan || '-'"></span></div>
                    <template x-if="detailData.foto_url">
                        <div>
                            <span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Foto</span>
                            <a :href="detailData.foto_url" target="_blank" style="color:#059669;font-size:13px;text-decoration:underline">
                                <i class="bi bi-image"></i> Lihat Foto
                            </a>
                        </div>
                    </template>
                    <template x-if="detailData.latitude && detailData.longitude">
                        <div>
                            <span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Lokasi</span>
                            <a :href="'https://www.google.com/maps?q=' + detailData.latitude + ',' + detailData.longitude" target="_blank" style="color:#059669;font-size:13px;text-decoration:underline">
                                <i class="bi bi-geo-alt"></i> Buka di Google Maps
                            </a>
                        </div>
                    </template>
                </div>
            </template>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="showDetailModal = false" class="pt-btn-secondary">Tutup</button>
            <button @click="showDetailModal = false; openEditModal(detailData.id)" class="pt-btn-primary">
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
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-geo-alt-fill text-emerald-600 mr-1"></i> Penyebaran Toko</h1>
    </div>
    <button @click="openCreateModal()" class="pt-btn-primary" style="font-size:12px">
        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah Data</span><span class="sm:hidden">Tambah</span>
    </button>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="pt-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-shop text-emerald-600"></i><span style="font-size:12px;color:#6B7280">Total Toko</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total ?? '—'"></p>
        </div>
        <div class="pt-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-check-circle-fill text-green-600"></i><span style="font-size:12px;color:#6B7280">Di Terima</span></div>
            <p style="font-size:24px;font-weight:700;color:#065F46" x-text="stats.diterima ?? '—'"></p>
        </div>
        <div class="pt-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-x-circle-fill text-red-600"></i><span style="font-size:12px;color:#6B7280">Di Tolak</span></div>
            <p style="font-size:24px;font-weight:700;color:#991B1B" x-text="stats.ditolak ?? '—'"></p>
        </div>
        <div class="pt-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-people-fill text-sky-600"></i><span style="font-size:12px;color:#6B7280">CS Aktif</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total_cs ?? '—'"></p>
        </div>
    </div>

    {{-- View Toggle --}}
    <div class="flex items-center justify-between">
        <div class="pt-view-toggle">
            <button @click="activeView = 'map'" :class="activeView === 'map' ? 'active' : ''" class="pt-view-btn">
                <i class="bi bi-map"></i> Peta
            </button>
            <button @click="activeView = 'table'" :class="activeView === 'table' ? 'active' : ''" class="pt-view-btn">
                <i class="bi bi-table"></i> Tabel
            </button>
        </div>
        <span style="font-size:12px;color:#6B7280" x-show="activeView === 'map'" x-cloak>
            <i class="bi bi-geo-alt"></i> <span x-text="mapData.length"></span> lokasi ditampilkan
        </span>
    </div>

    {{-- Map Section --}}
    <div x-show="activeView === 'map'" x-cloak class="pt-map-container">
        <div class="pt-map-header">
            <h3><i class="bi bi-map-fill text-emerald-600"></i> Peta Penyebaran Toko</h3>
            <div class="pt-map-legend">
                <span><span class="pt-map-legend-dot" style="background:#16a34a"></span>Di Terima</span>
                <span><span class="pt-map-legend-dot" style="background:#dc2626"></span>Di Tolak</span>
            </div>
        </div>
        <div id="penyebaranMap"></div>
        <div class="pt-map-info">
            <i class="bi bi-info-circle"></i> Klik marker untuk melihat detail toko. Scroll untuk zoom.
        </div>
    </div>

    {{-- Filter Bar --}}
    <div x-show="activeView === 'table'" x-cloak class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2">
        <div class="relative flex-1" style="min-width:180px">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
            <input type="text" x-model="filters.search" @input.debounce.500ms="resetAndLoad()"
                   class="pt-field-input" style="padding-left:36px" placeholder="Cari nama toko, CS, donatur, alamat...">
        </div>
        <select x-model="filters.status" @change="resetAndLoad()" class="pt-field-input" style="width:auto">
            <option value="">Semua Status</option>
            <option value="Di terima">Di terima</option>
            <option value="Di tolak">Di tolak</option>
        </select>
        <input type="date" x-model="filters.date_from" @change="resetAndLoad()" class="pt-field-input" style="width:auto" title="Dari tanggal">
        <input type="date" x-model="filters.date_to" @change="resetAndLoad()" class="pt-field-input" style="width:auto" title="Sampai tanggal">
        <button @click="clearFilters()" style="font-size:12px;color:#6B7280;cursor:pointer;border:none;background:none" title="Reset Filter">
            <i class="bi bi-x-circle"></i>
        </button>
    </div>

    {{-- Data Table --}}
    <div x-show="activeView === 'table'" x-cloak class="bg-white rounded-xl border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" style="font-size:14px">
                <thead style="background:#F9FAFB;font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em">
                    <tr>
                        <th style="padding:12px 16px;text-align:left;width:40px">#</th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('tanggal_registrasi')">
                            Tanggal <i class="bi" :class="sortIcon('tanggal_registrasi')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('nama_cs')">
                            CS <i class="bi" :class="sortIcon('nama_cs')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('nama_toko')">
                            Nama Toko <i class="bi" :class="sortIcon('nama_toko')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden md:table-cell" @click="sortBy('nama_donatur')">
                            Donatur <i class="bi" :class="sortIcon('nama_donatur')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden lg:table-cell" @click="sortBy('nomor_kencleng')">
                            No. Kencleng <i class="bi" :class="sortIcon('nomor_kencleng')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left" class="hidden lg:table-cell">No. HP</th>
                        <th style="padding:12px 16px;text-align:center;cursor:pointer" @click="sortBy('status')">
                            Status <i class="bi" :class="sortIcon('status')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr x-show="loadingTable">
                        <td colspan="9" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat pt-spin" style="margin-right:4px"></i> Memuat...</td>
                    </tr>
                    <tr x-show="!loadingTable && tableData.length === 0">
                        <td colspan="9" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                    </tr>
                    <template x-for="(row, idx) in tableData" :key="row.id">
                        <tr style="cursor:pointer;transition:background 0.15s" class="hover:bg-emerald-50/40" @click="openDetailModal(row.id)">
                            <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="pagination.from + idx"></td>
                            <td style="padding:12px 16px;color:#4B5563" x-text="row.tanggal_fmt"></td>
                            <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama_cs || '-'"></td>
                            <td style="padding:12px 16px;color:#1F2937" x-text="row.nama_toko || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden md:table-cell" x-text="row.nama_donatur || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden lg:table-cell" x-text="row.nomor_kencleng || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden lg:table-cell" x-text="row.no_hp || '-'"></td>
                            <td style="padding:12px 16px;text-align:center">
                                <span :class="row.status === 'Di terima' ? 'pt-badge-success' : 'pt-badge-danger'" x-text="row.status || '-'"></span>
                            </td>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function penyebaranTokoApp() {
    return {
        loading: true,
        loadingTable: false,
        saving: false,
        showFormModal: false,
        showDeleteModal: false,
        showDetailModal: false,
        editingId: null,
        deletingId: null,
        detailData: null,
        activeView: 'map',
        mapData: [],
        mapInstance: null,
        markersLayer: null,

        stats: {},
        tableData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        filters: { search: '', status: '', date_from: '', date_to: '', sort: 'tanggal_registrasi', order: 'desc' },
        form: { tanggal_registrasi: '', nama_cs: '', nama_toko: '', nama_donatur: '', nomor_kencleng: '', no_hp: '', alamat: '', status: 'Di terima', keterangan: '', latitude: '', longitude: '' },
        formErrors: {},

        async init() {
            try {
                await Promise.all([this.loadStats(), this.loadTable(), this.loadMapData()]);
                this.$nextTick(() => { this.initMap(); });
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        async loadMapData() {
            try {
                var res = await fetch('/api/penyebaran-toko/map-data', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.mapData = await res.json();
            } catch (e) { console.error('Map data error', e); }
        },

        initMap() {
            var mapEl = document.getElementById('penyebaranMap');
            if (!mapEl || this.mapInstance) return;

            // Default center: Cianjur, Jawa Barat
            this.mapInstance = L.map('penyebaranMap', { scrollWheelZoom: true }).setView([-6.8174, 107.1428], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.mapInstance);

            this.markersLayer = L.layerGroup().addTo(this.mapInstance);
            this.renderMarkers();

            // Fix map sizing when container becomes visible
            var self = this;
            setTimeout(function() { self.mapInstance.invalidateSize(); }, 200);
            this.$watch('activeView', function(val) {
                if (val === 'map') {
                    setTimeout(function() { self.mapInstance.invalidateSize(); }, 100);
                }
            });
        },

        renderMarkers() {
            if (!this.markersLayer) return;
            this.markersLayer.clearLayers();

            var bounds = [];
            var self = this;

            this.mapData.forEach(function(item) {
                var lat = parseFloat(item.latitude);
                var lng = parseFloat(item.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                var isAccepted = item.status === 'Di terima';
                var color = isAccepted ? '#16a34a' : '#dc2626';
                var bgColor = isAccepted ? '#D1FAE5' : '#FEE2E2';
                var statusText = item.status || '-';
                var badgeStyle = 'background:' + bgColor + ';color:' + color + ';';

                var icon = L.divIcon({
                    className: '',
                    html: '<div style="width:24px;height:24px;border-radius:50%;background:' + color + ';border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    popupAnchor: [0, -14]
                });

                var popupContent = '<div>'
                    + '<div class="pt-popup-title">' + (item.nama_toko || '-') + '</div>'
                    + '<div class="pt-popup-row">CS: <span>' + (item.nama_cs || '-') + '</span></div>'
                    + '<div class="pt-popup-row">Donatur: <span>' + (item.nama_donatur || '-') + '</span></div>'
                    + '<div class="pt-popup-row">Kencleng: <span>' + (item.nomor_kencleng || '-') + '</span></div>'
                    + '<div class="pt-popup-row">Tanggal: <span>' + (item.tanggal_fmt || '-') + '</span></div>'
                    + '<div class="pt-popup-row" style="margin-top:4px">Status: <span class="pt-popup-badge" style="' + badgeStyle + '">' + statusText + '</span></div>'
                    + '<div class="pt-popup-row" style="margin-top:6px;font-size:11px;color:#9CA3AF">' + (item.alamat || '') + '</div>'
                    + '</div>';

                var marker = L.marker([lat, lng], { icon: icon }).bindPopup(popupContent, { maxWidth: 260 });
                self.markersLayer.addLayer(marker);
                bounds.push([lat, lng]);
            });

            if (bounds.length > 0) {
                // Find densest cluster: group by ~0.01° grid (~1km), zoom into the busiest cell
                var grid = {};
                var maxKey = null;
                var maxCount = 0;
                bounds.forEach(function(b) {
                    var key = (Math.round(b[0] * 100)) + ',' + (Math.round(b[1] * 100));
                    if (!grid[key]) grid[key] = { sumLat: 0, sumLng: 0, count: 0 };
                    grid[key].sumLat += b[0];
                    grid[key].sumLng += b[1];
                    grid[key].count++;
                    if (grid[key].count > maxCount) { maxCount = grid[key].count; maxKey = key; }
                });

                if (maxKey && maxCount >= 2) {
                    var cell = grid[maxKey];
                    var cLat = cell.sumLat / cell.count;
                    var cLng = cell.sumLng / cell.count;
                    this.mapInstance.setView([cLat, cLng], 15);
                } else {
                    this.mapInstance.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
                }
            }
        },

        async loadStats() {
            try {
                var res = await fetch('/api/penyebaran-toko/stats', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.stats = await res.json();
            } catch (e) { console.error('Stats error', e); }
        },

        async loadTable(page) {
            if (page === undefined) page = 1;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.filters.search);
                qs += '&status=' + encodeURIComponent(this.filters.status);
                qs += '&date_from=' + encodeURIComponent(this.filters.date_from);
                qs += '&date_to=' + encodeURIComponent(this.filters.date_to);
                qs += '&sort=' + encodeURIComponent(this.filters.sort);
                qs += '&order=' + encodeURIComponent(this.filters.order);

                var res = await fetch('/api/penyebaran-toko/list?' + qs, { headers: { 'Accept': 'application/json' } });
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
            this.filters = { search: '', status: '', date_from: '', date_to: '', sort: 'tanggal_registrasi', order: 'desc' };
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
            this.form = { tanggal_registrasi: '', nama_cs: '', nama_toko: '', nama_donatur: '', nomor_kencleng: '', no_hp: '', alamat: '', status: 'Di terima', keterangan: '', latitude: '', longitude: '' };
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
                var res = await fetch('/api/penyebaran-toko/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.form = {
                    tanggal_registrasi: data.tanggal_registrasi ? data.tanggal_registrasi.substring(0, 10) : '',
                    nama_cs: data.nama_cs || '',
                    nama_toko: data.nama_toko || '',
                    nama_donatur: data.nama_donatur || '',
                    nomor_kencleng: data.nomor_kencleng || '',
                    no_hp: data.no_hp || '',
                    alamat: data.alamat || '',
                    status: data.status || 'Di terima',
                    keterangan: data.keterangan || '',
                    latitude: data.latitude || '',
                    longitude: data.longitude || '',
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
                var url = this.editingId ? '/api/penyebaran-toko/' + this.editingId : '/api/penyebaran-toko';
                var method = this.editingId ? 'PUT' : 'POST';
                var payload = {};
                for (var k in this.form) payload[k] = this.form[k];
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
                var res = await fetch('/api/penyebaran-toko/' + this.deletingId, { method: 'DELETE', headers: headers });
                var json = await res.json();
                this.showToast(json.message || 'Dihapus', 'success');
                this.showDeleteModal = false;
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page)]);
            } catch (e) { this.showToast('Gagal menghapus', 'error'); }
            this.saving = false;
        },

        openDetailModal: async function(id) {
            try {
                var res = await fetch('/api/penyebaran-toko/' + id, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                var data = await res.json();
                this.detailData = data;
                this.showDetailModal = true;
            } catch (e) { this.showToast('Gagal memuat detail', 'error'); }
        },

        showToast: function(message, type) {
            if (!type) type = 'success';
            var bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#059669';
            var icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'x-circle-fill' : 'info-circle-fill';
            var toast = document.createElement('div');
            toast.innerHTML = '<i class="bi bi-' + icon + '"></i> ' + message;
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:pt-fadeInUp .3s ease;max-width:90vw';
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
