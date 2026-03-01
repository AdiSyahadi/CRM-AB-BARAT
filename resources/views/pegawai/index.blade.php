<x-layouts.app active="data-pegawai" title="Data Pegawai" xData="dataPegawaiApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<style>
    .pg-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .pg-spin { animation: pg-spin 1s linear infinite; }
    @keyframes pg-spin { to { transform: rotate(360deg); } }
    @keyframes pg-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-primary-600 pg-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data pegawai...</span>
    </div>
</div>

{{-- ============ CREATE / EDIT MODAL ============ --}}
<div x-show="showFormModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Form Pegawai"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showFormModal && closeFormModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeFormModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-person-plus-fill text-primary-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingId ? 'Edit Pegawai' : 'Tambah Pegawai'"></h3>
            </div>
            <button @click="closeFormModal()" aria-label="Tutup" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div>
                <label class="crud-field-label">Nama Pegawai <span style="color:#EF4444">*</span></label>
                <input type="text" x-model="form.nama_pegawai" class="crud-field-input" placeholder="Nama lengkap">
                <template x-if="formErrors.nama_pegawai"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_pegawai[0]"></p></template>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Tempat Lahir</label>
                    <input type="text" x-model="form.tempat_lahir" class="crud-field-input" placeholder="Kota lahir">
                </div>
                <div>
                    <label class="crud-field-label">Tanggal Lahir</label>
                    <input type="date" x-model="form.tanggal_lahir" class="crud-field-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Jenis Kelamin <span style="color:#EF4444">*</span></label>
                    <select x-model="form.jenis_kelamin" class="crud-field-input">
                        <option value="">— Pilih —</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <template x-if="formErrors.jenis_kelamin"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.jenis_kelamin[0]"></p></template>
                </div>
                <div>
                    <label class="crud-field-label">No. Telepon</label>
                    <input type="text" x-model="form.no_telepon" class="crud-field-input" placeholder="08xxxxxxxxxx">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Jabatan</label>
                    <select x-model="form.id_jabatan" class="crud-field-input">
                        <option value="">— Pilih —</option>
                        @foreach($jabatanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="crud-field-label">Tanggal Masuk <span style="color:#EF4444">*</span></label>
                    <input type="date" x-model="form.tanggal_masuk" class="crud-field-input">
                    <template x-if="formErrors.tanggal_masuk"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.tanggal_masuk[0]"></p></template>
                </div>
            </div>
            <div>
                <label class="crud-field-label">Alamat</label>
                <textarea x-model="form.alamat" class="crud-field-input" rows="2" placeholder="Alamat lengkap"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeFormModal()" class="crud-btn-secondary">Batal</button>
            <button @click="submitForm()" :disabled="saving" class="crud-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat pg-spin"></i></template>
                <span x-text="editingId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div x-show="showDeleteModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Pegawai"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 backdrop-blur-sm"
     @keydown.escape.window="showDeleteModal && (showDeleteModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.outside="showDeleteModal = false">
        <div style="width:48px;height:48px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="bi bi-exclamation-triangle-fill text-xl" style="color:#DC2626"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Hapus Data Pegawai?</h3>
        <p class="text-sm text-gray-500 mb-4">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="crud-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="crud-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat pg-spin"></i></template>
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- ============ DETAIL MODAL ============ --}}
<div x-show="showDetailModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Detail Pegawai"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showDetailModal && (showDetailModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" @click.outside="showDetailModal = false">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-badge-fill text-primary-600 text-lg"></i>
                <h3 class="font-semibold text-gray-800">Detail Pegawai</h3>
            </div>
            <button @click="showDetailModal = false" aria-label="Tutup" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3" x-show="detailData">
            <template x-if="detailData">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama</span><span style="font-size:14px;font-weight:500" x-text="detailData.nama_pegawai"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">TTL</span><span style="font-size:14px" x-text="(detailData.tempat_lahir || '-') + ', ' + (detailData.tanggal_lahir_fmt || '-')"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Jenis Kelamin</span><span style="font-size:14px" x-text="detailData.gender_label"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">No. Telepon</span><span style="font-size:14px" x-text="detailData.no_telepon || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Jabatan</span><span style="font-size:14px" x-text="detailData.jabatan_label"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Tanggal Masuk</span><span style="font-size:14px" x-text="detailData.tanggal_masuk_fmt"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Masa Kerja</span><span style="font-size:14px;font-weight:500;color:#059669" x-text="detailData.masa_kerja"></span></div>
                    <div><span style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Alamat</span><p style="font-size:14px;background:#F9FAFB;border-radius:8px;padding:8px" x-text="detailData.alamat || '-'"></p></div>
                </div>
            </template>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="showDetailModal = false" class="crud-btn-secondary">Tutup</button>
            <button @click="showDetailModal = false; openEditModal(detailData.id_pegawai)" class="crud-btn-primary">
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
        <button @click="$dispatch('toggle-sidebar')" aria-label="Toggle menu" class="lg:hidden text-gray-600"><i class="bi bi-list text-xl"></i></button>
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-person-badge-fill text-primary-600 mr-1"></i> Data Pegawai</h1>
    </div>
    <button @click="openCreateModal()" class="crud-btn-primary" style="font-size:12px">
        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah Pegawai</span><span class="sm:hidden">Tambah</span>
    </button>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="pg-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-people-fill text-primary-600"></i><span style="font-size:12px;color:#6B7280">Total Pegawai</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total ?? '—'"></p>
        </div>
        <div class="pg-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-gender-male text-sky-600"></i><span style="font-size:12px;color:#6B7280">Laki-laki</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.laki_laki ?? '—'"></p>
        </div>
        <div class="pg-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-gender-female text-pink-600"></i><span style="font-size:12px;color:#6B7280">Perempuan</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.perempuan ?? '—'"></p>
        </div>
        <div class="pg-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-clock-history text-emerald-600"></i><span style="font-size:12px;color:#6B7280">Rata-rata Masa Kerja</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.avg_masa_kerja ?? '—'"></p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2">
        <div class="relative flex-1" style="min-width:180px">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
            <input type="text" x-model="filters.search" @input.debounce.500ms="resetAndLoad()"
                   class="crud-field-input" style="padding-left:36px" placeholder="Cari nama, alamat, telepon...">
        </div>
        <select x-model="filters.jenis_kelamin" @change="resetAndLoad()" class="crud-field-input" style="width:auto">
            <option value="">Semua Gender</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>
        <select x-model="filters.jabatan" @change="resetAndLoad()" class="crud-field-input" style="width:auto">
            <option value="">Semua Jabatan</option>
            @foreach($jabatanOptions as $id => $label)
                <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
        </select>
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
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('nama_pegawai')">
                            Nama <i class="bi" :class="sortIcon('nama_pegawai')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden md:table-cell" @click="sortBy('jenis_kelamin')">
                            Gender <i class="bi" :class="sortIcon('jenis_kelamin')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden lg:table-cell" @click="sortBy('id_jabatan')">
                            Jabatan <i class="bi" :class="sortIcon('id_jabatan')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left" class="hidden sm:table-cell">No. Telepon</th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden lg:table-cell" @click="sortBy('tanggal_masuk')">
                            Tgl Masuk <i class="bi" :class="sortIcon('tanggal_masuk')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:left" class="hidden xl:table-cell">Masa Kerja</th>
                        <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr x-show="loadingTable">
                        <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat pg-spin" style="margin-right:4px"></i> Memuat...</td>
                    </tr>
                    <tr x-show="!loadingTable && tableData.length === 0">
                        <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                    </tr>
                    <template x-for="(row, idx) in tableData" :key="row.id_pegawai">
                        <tr style="cursor:pointer;transition:background 0.15s" class="hover:bg-primary-50/40" @click="openDetailModal(row.id_pegawai)">
                            <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="pagination.from + idx"></td>
                            <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama_pegawai"></td>
                            <td style="padding:12px 16px" class="hidden md:table-cell">
                                <span style="padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:500"
                                      :style="row.jenis_kelamin === 'L' ? 'background:#E0F2FE;color:#0369A1' : 'background:#FCE7F3;color:#BE185D'"
                                      x-text="row.gender_label"></span>
                            </td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden lg:table-cell" x-text="row.jabatan_label"></td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden sm:table-cell" x-text="row.no_telepon || '-'"></td>
                            <td style="padding:12px 16px;color:#4B5563" class="hidden lg:table-cell" x-text="row.tanggal_masuk_fmt"></td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden xl:table-cell" x-text="row.masa_kerja"></td>
                            <td style="padding:12px 16px;text-align:center" @click.stop>
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(row.id_pegawai)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#D97706" title="Edit" aria-label="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button @click="confirmDelete(row.id_pegawai)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#EF4444" title="Hapus" aria-label="Hapus">
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
                        aria-label="Halaman sebelumnya"
                        style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                        :style="!pagination.prev_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        aria-label="Halaman berikutnya"
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
function dataPegawaiApp() {
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

        stats: {},
        tableData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        filters: { search: '', jenis_kelamin: '', jabatan: '', sort: 'nama_pegawai', order: 'asc' },
        form: { nama_pegawai: '', tempat_lahir: '', tanggal_lahir: '', jenis_kelamin: '', alamat: '', no_telepon: '', id_jabatan: '', tanggal_masuk: '' },
        formErrors: {},

        async init() {
            try {
                await Promise.all([this.loadStats(), this.loadTable()]);
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        async loadStats() {
            try {
                var res = await fetch('/api/data-pegawai/stats', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.stats = await res.json();
            } catch (e) { console.error('Stats error', e); }
        },

        async loadTable(page) {
            if (page === undefined) page = 1;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.filters.search);
                qs += '&jenis_kelamin=' + encodeURIComponent(this.filters.jenis_kelamin);
                qs += '&jabatan=' + encodeURIComponent(this.filters.jabatan);
                qs += '&sort=' + encodeURIComponent(this.filters.sort);
                qs += '&order=' + encodeURIComponent(this.filters.order);

                var res = await fetch('/api/data-pegawai/list?' + qs, { headers: { 'Accept': 'application/json' } });
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
            this.filters = { search: '', jenis_kelamin: '', jabatan: '', sort: 'nama_pegawai', order: 'asc' };
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
            this.form = { nama_pegawai: '', tempat_lahir: '', tanggal_lahir: '', jenis_kelamin: '', alamat: '', no_telepon: '', id_jabatan: '', tanggal_masuk: '' };
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
                var res = await fetch('/api/data-pegawai/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.form = {
                    nama_pegawai: data.nama_pegawai || '',
                    tempat_lahir: data.tempat_lahir || '',
                    tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.substring(0, 10) : '',
                    jenis_kelamin: data.jenis_kelamin || '',
                    alamat: data.alamat || '',
                    no_telepon: data.no_telepon || '',
                    id_jabatan: data.id_jabatan ? String(data.id_jabatan) : '',
                    tanggal_masuk: data.tanggal_masuk ? data.tanggal_masuk.substring(0, 10) : '',
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
                var url = this.editingId ? '/api/data-pegawai/' + this.editingId : '/api/data-pegawai';
                var method = this.editingId ? 'PUT' : 'POST';
                var payload = {};
                for (var k in this.form) payload[k] = this.form[k];
                if (payload.id_jabatan === '') payload.id_jabatan = null;
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
                var res = await fetch('/api/data-pegawai/' + this.deletingId, { method: 'DELETE', headers: headers });
                var json = await res.json();
                this.showToast(json.message || 'Dihapus', 'success');
                this.showDeleteModal = false;
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page)]);
            } catch (e) { this.showToast('Gagal menghapus', 'error'); }
            this.saving = false;
        },

        openDetailModal: async function(id) {
            try {
                var res = await fetch('/api/data-pegawai/' + id, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                var data = await res.json();
                var jabatanMap = @json($jabatanOptions);
                data.jabatan_label = jabatanMap[data.id_jabatan] || (data.id_jabatan ? 'Jabatan #' + data.id_jabatan : '-');
                data.gender_label = data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                data.tanggal_lahir_fmt = data.tanggal_lahir ? new Date(data.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
                data.tanggal_masuk_fmt = data.tanggal_masuk ? new Date(data.tanggal_masuk).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
                if (data.tanggal_masuk) {
                    var diff = Math.floor((new Date() - new Date(data.tanggal_masuk)) / (1000 * 60 * 60 * 24));
                    var y = Math.floor(diff / 365);
                    var m = Math.floor((diff % 365) / 30);
                    data.masa_kerja = y > 0 ? y + ' tahun ' + m + ' bulan' : m + ' bulan';
                } else {
                    data.masa_kerja = '-';
                }
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
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:pg-fadeInUp .3s ease;max-width:90vw';
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
