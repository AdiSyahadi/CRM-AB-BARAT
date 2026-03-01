<x-layouts.app active="manajemen-user" title="Manajemen User" xData="manajemenUserApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<style>
    .mu-field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; transition: all 0.15s; background: white; outline: 2px solid transparent;
    }
    .mu-field-input:focus { border-color: #4F46E5; box-shadow: 0 0 0 2px rgba(79,70,229,0.15); }
    .mu-field-label { display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px; }
    .mu-btn-primary {
        background: #4F46E5; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .mu-btn-primary:hover { background: #4338CA; }
    .mu-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
    .mu-btn-secondary {
        background: #F3F4F6; color: #374151; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
    }
    .mu-btn-secondary:hover { background: #E5E7EB; }
    .mu-btn-danger {
        background: #DC2626; color: white; padding: 8px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; transition: all 0.2s; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .mu-btn-danger:hover { background: #B91C1C; }
    .mu-btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
    .mu-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .mu-badge-success { background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 500; }
    .mu-badge-warning { background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 500; }
    .mu-avatar {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #E5E7EB;
    }
    .mu-avatar-placeholder {
        width: 36px; height: 36px; border-radius: 50%; background: #EEF2FF; color: #4F46E5;
        display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
        border: 2px solid #E5E7EB;
    }
    .mu-spin { animation: mu-spin 1s linear infinite; }
    @keyframes mu-spin { to { transform: rotate(360deg); } }
    @keyframes mu-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-indigo-600 mu-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data user...</span>
    </div>
</div>

{{-- ============ CREATE / EDIT MODAL ============ --}}
<div x-show="showFormModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Form User"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showFormModal && closeFormModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" @click.outside="closeFormModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-indigo-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingId ? 'Edit User' : 'Tambah User'"></h3>
            </div>
            <button @click="closeFormModal()" class="text-gray-400 hover:text-gray-600" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3">
            <div>
                <label class="mu-field-label">Nama <span style="color:#EF4444">*</span></label>
                <input type="text" x-model="form.name" class="mu-field-input" placeholder="Nama lengkap">
                <template x-if="formErrors.name"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.name[0]"></p></template>
            </div>
            <div>
                <label class="mu-field-label">Email <span style="color:#EF4444">*</span></label>
                <input type="email" x-model="form.email" class="mu-field-input" placeholder="email@example.com">
                <template x-if="formErrors.email"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.email[0]"></p></template>
            </div>
            <div>
                <label class="mu-field-label">
                    Password
                    <span x-show="!editingId" style="color:#EF4444">*</span>
                    <span x-show="editingId" style="font-weight:400;color:#9CA3AF;font-size:11px">(kosongkan jika tidak ingin mengubah)</span>
                </label>
                <div style="position:relative">
                    <input :type="showPassword ? 'text' : 'password'" x-model="form.password" class="mu-field-input" style="padding-right:40px" placeholder="Minimal 6 karakter">
                    <button type="button" @click="showPassword = !showPassword"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:14px"
                            aria-label="Tampilkan password">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
                <template x-if="formErrors.password"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.password[0]"></p></template>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeFormModal()" class="mu-btn-secondary">Batal</button>
            <button @click="submitForm()" :disabled="saving" class="mu-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat mu-spin"></i></template>
                <span x-text="editingId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div x-show="showDeleteModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus User"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 backdrop-blur-sm"
     @keydown.escape.window="showDeleteModal && (showDeleteModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.outside="showDeleteModal = false">
        <div style="width:48px;height:48px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="bi bi-exclamation-triangle-fill text-xl" style="color:#DC2626"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Hapus User?</h3>
        <p class="text-sm text-gray-500 mb-4">Data user yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="mu-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="mu-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat mu-spin"></i></template>
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- ============ DETAIL MODAL ============ --}}
<div x-show="showDetailModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Detail User"
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showDetailModal && (showDetailModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" @click.outside="showDetailModal = false">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-circle text-indigo-600 text-lg"></i>
                <h3 class="font-semibold text-gray-800">Detail User</h3>
            </div>
            <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3" x-show="detailData">
            <template x-if="detailData">
                <div style="display:flex;flex-direction:column;gap:10px">
                    {{-- Avatar --}}
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
                        <template x-if="detailData.photo_url">
                            <img :src="detailData.photo_url" class="mu-avatar" style="width:48px;height:48px">
                        </template>
                        <template x-if="!detailData.photo_url">
                            <div class="mu-avatar-placeholder" style="width:48px;height:48px;font-size:18px" x-text="detailData.name ? detailData.name.charAt(0).toUpperCase() : '?'"></div>
                        </template>
                        <div>
                            <p style="font-weight:600;color:#1F2937;font-size:16px" x-text="detailData.name"></p>
                            <p style="font-size:13px;color:#6B7280" x-text="detailData.email"></p>
                        </div>
                    </div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">ID</span><span style="font-size:14px;font-weight:500" x-text="detailData.id"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Nama</span><span style="font-size:14px;font-weight:500" x-text="detailData.name || '-'"></span></div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Email</span><span style="font-size:14px;font-weight:500" x-text="detailData.email || '-'"></span></div>
                    <div class="flex justify-between items-center">
                        <span style="font-size:12px;color:#6B7280">Email Verified</span>
                        <span :class="detailData.email_verified_at ? 'mu-badge-success' : 'mu-badge-warning'"
                              x-text="detailData.email_verified_at ? 'Terverifikasi' : 'Belum'"></span>
                    </div>
                    <div class="flex justify-between"><span style="font-size:12px;color:#6B7280">Dibuat</span><span style="font-size:14px" x-text="detailData.created_fmt || '-'"></span></div>
                </div>
            </template>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="showDetailModal = false" class="mu-btn-secondary">Tutup</button>
            <button @click="showDetailModal = false; openEditModal(detailData.id)" class="mu-btn-primary">
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
        <button @click="$dispatch('toggle-sidebar')" class="lg:hidden text-gray-600" aria-label="Toggle menu"><i class="bi bi-list text-xl"></i></button>
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-person-gear text-indigo-600 mr-1"></i> Manajemen User</h1>
    </div>
    <button @click="openCreateModal()" class="mu-btn-primary" style="font-size:12px">
        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah User</span><span class="sm:hidden">Tambah</span>
    </button>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="mu-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-people-fill text-indigo-600"></i><span style="font-size:12px;color:#6B7280">Total User</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.total ?? '—'"></p>
        </div>
        <div class="mu-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-patch-check-fill text-green-600"></i><span style="font-size:12px;color:#6B7280">Terverifikasi</span></div>
            <p style="font-size:24px;font-weight:700;color:#065F46" x-text="stats.verified ?? '—'"></p>
        </div>
        <div class="mu-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-exclamation-circle-fill text-amber-600"></i><span style="font-size:12px;color:#6B7280">Belum Verifikasi</span></div>
            <p style="font-size:24px;font-weight:700;color:#92400E" x-text="stats.unverified ?? '—'"></p>
        </div>
        <div class="mu-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-person-plus-fill text-sky-600"></i><span style="font-size:12px;color:#6B7280">Baru (30 hari)</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.recent_month ?? '—'"></p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2">
        <div class="relative flex-1" style="min-width:200px">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
            <input type="text" x-model="filters.search" @input.debounce.500ms="resetAndLoad()"
                   class="mu-field-input" style="padding-left:36px" placeholder="Cari nama atau email...">
        </div>
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
                        <th style="padding:12px 16px;text-align:left">User</th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="sortBy('email')">
                            Email <i class="bi" :class="sortIcon('email')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:center" class="hidden md:table-cell">Status</th>
                        <th style="padding:12px 16px;text-align:left;cursor:pointer" class="hidden lg:table-cell" @click="sortBy('created_at')">
                            Dibuat <i class="bi" :class="sortIcon('created_at')"></i>
                        </th>
                        <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr x-show="loadingTable">
                        <td colspan="6" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat mu-spin" style="margin-right:4px"></i> Memuat...</td>
                    </tr>
                    <tr x-show="!loadingTable && tableData.length === 0">
                        <td colspan="6" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                    </tr>
                    <template x-for="(row, idx) in tableData" :key="row.id">
                        <tr style="cursor:pointer;transition:background 0.15s" class="hover:bg-indigo-50/40" @click="openDetailModal(row.id)">
                            <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="pagination.from + idx"></td>
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <template x-if="row.photo_url">
                                        <img :src="row.photo_url" class="mu-avatar">
                                    </template>
                                    <template x-if="!row.photo_url">
                                        <div class="mu-avatar-placeholder" x-text="row.name ? row.name.charAt(0).toUpperCase() : '?'"></div>
                                    </template>
                                    <span style="font-weight:500;color:#1F2937" x-text="row.name || '-'"></span>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:#4B5563" x-text="row.email || '-'"></td>
                            <td style="padding:12px 16px;text-align:center" class="hidden md:table-cell">
                                <span :class="row.email_verified_at ? 'mu-badge-success' : 'mu-badge-warning'"
                                      x-text="row.email_verified_at ? 'Verified' : 'Unverified'"></span>
                            </td>
                            <td style="padding:12px 16px;color:#4B5563;font-size:12px" class="hidden lg:table-cell" x-text="row.created_fmt"></td>
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
                <button @click="goToPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                        :style="!pagination.prev_page_url ? 'opacity:0.4;cursor:not-allowed' : ''"
                        aria-label="Halaman sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                        :style="!pagination.next_page_url ? 'opacity:0.4;cursor:not-allowed' : ''"
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
function manajemenUserApp() {
    return {
        loading: true,
        loadingTable: false,
        saving: false,
        showFormModal: false,
        showDeleteModal: false,
        showDetailModal: false,
        showPassword: false,
        editingId: null,
        deletingId: null,
        detailData: null,

        stats: {},
        tableData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        filters: { search: '', sort: 'created_at', order: 'desc' },
        form: { name: '', email: '', password: '' },
        formErrors: {},

        async init() {
            try {
                await Promise.all([this.loadStats(), this.loadTable()]);
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        async loadStats() {
            try {
                var res = await fetch('/api/manajemen-user/stats', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.stats = await res.json();
            } catch (e) { console.error('Stats error', e); }
        },

        async loadTable(page) {
            if (page === undefined) page = 1;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.filters.search);
                qs += '&sort=' + encodeURIComponent(this.filters.sort);
                qs += '&order=' + encodeURIComponent(this.filters.order);

                var res = await fetch('/api/manajemen-user/list?' + qs, { headers: { 'Accept': 'application/json' } });
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
            this.filters = { search: '', sort: 'created_at', order: 'desc' };
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
            this.form = { name: '', email: '', password: '' };
            this.formErrors = {};
            this.showPassword = false;
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
                var res = await fetch('/api/manajemen-user/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.form = {
                    name: data.name || '',
                    email: data.email || '',
                    password: '',
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
                var url = this.editingId ? '/api/manajemen-user/' + this.editingId : '/api/manajemen-user';
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
                var res = await fetch('/api/manajemen-user/' + this.deletingId, { method: 'DELETE', headers: headers });
                var json = await res.json();
                if (!res.ok) {
                    this.showToast(json.message || 'Gagal menghapus', 'error');
                } else {
                    this.showToast(json.message || 'Dihapus', 'success');
                }
                this.showDeleteModal = false;
                await Promise.all([this.loadStats(), this.loadTable(this.pagination.current_page)]);
            } catch (e) { this.showToast('Gagal menghapus', 'error'); }
            this.saving = false;
        },

        openDetailModal: async function(id) {
            try {
                var res = await fetch('/api/manajemen-user/' + id, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                var data = await res.json();
                this.detailData = data;
                this.showDetailModal = true;
            } catch (e) { this.showToast('Gagal memuat detail', 'error'); }
        },

        showToast: function(message, type) {
            if (!type) type = 'success';
            var bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#4F46E5';
            var icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'x-circle-fill' : 'info-circle-fill';
            var toast = document.createElement('div');
            toast.innerHTML = '<i class="bi bi-' + icon + '"></i> ' + message;
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:mu-fadeInUp .3s ease;max-width:90vw';
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
