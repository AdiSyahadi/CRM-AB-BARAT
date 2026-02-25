<x-layouts.app active="kwitansi-v1" title="Kwitansi v1 - Abbarat" xData="kwitansiV1App()">

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
    .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-secondary {
        background: white; color: #374151; border: 1px solid #D1D5DB;
        padding: 8px 20px; border-radius: 10px; font-weight: 600; font-size: 13px;
        transition: all 0.2s; cursor: pointer;
    }
    .btn-secondary:hover { background: #F9FAFB; }
    .table-row { transition: background 0.15s; }
    .table-row:hover { background: #F0FDF4; }
    .pagination-btn { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 500; border: 1px solid #E5E7EB; background: white; cursor: pointer; transition: all 0.15s; }
    .pagination-btn:hover:not(:disabled) { background: #F0FDF4; border-color: #059669; color: #059669; }
    .pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pagination-btn.active { background: #059669; color: white; border-color: #059669; }
    .checkbox-custom { width: 16px; height: 16px; accent-color: #059669; cursor: pointer; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.1); }
    .spinner { border: 3px solid #D1FAE5; border-top: 3px solid #10B981; border-radius: 50%; width: 24px; height: 24px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
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

    {{-- ======= CREATE / EDIT MODAL ======= --}}
    <div x-show="showFormModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click.self="closeFormModal()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center z-[80] p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-lg my-8 shadow-2xl relative" @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="bi text-white text-lg" :class="editingId ? 'bi-pencil-square' : 'bi-plus-lg'"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="editingId ? 'Edit Kwitansi' : 'Tambah Kwitansi Baru'"></h3>
                        <p class="text-xs text-gray-500">Isi data kwitansi donasi</p>
                    </div>
                </div>
                <button @click="closeFormModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Form Body --}}
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">
                <div>
                    <label class="field-label">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.tanggal" class="field-input" :class="{ 'error': formErrors.tanggal }">
                    <p x-show="formErrors.tanggal" class="field-error" x-text="formErrors.tanggal?.[0]"></p>
                </div>
                <div>
                    <label class="field-label">Nama Donatur <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.nama_donatur" placeholder="Masukkan nama donatur" class="field-input" :class="{ 'error': formErrors.nama_donatur }">
                    <p x-show="formErrors.nama_donatur" class="field-error" x-text="formErrors.nama_donatur?.[0]"></p>
                </div>
                <div>
                    <label class="field-label">Nama Program <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.nama_donasi" placeholder="Masukkan nama program donasi" class="field-input" :class="{ 'error': formErrors.nama_donasi }">
                    <p x-show="formErrors.nama_donasi" class="field-error" x-text="formErrors.nama_donasi?.[0]"></p>
                </div>
                <div>
                    <label class="field-label">Jumlah Donasi <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                        <input type="text" x-model="form.jumlah_donasi_display"
                               @input="formatCurrency()"
                               placeholder="0"
                               class="field-input pl-10" :class="{ 'error': formErrors.jumlah_donasi }">
                    </div>
                    <p x-show="formErrors.jumlah_donasi" class="field-error" x-text="formErrors.jumlah_donasi?.[0]"></p>
                </div>
            </form>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3 bg-gray-50 rounded-b-2xl">
                <button @click="closeFormModal()" class="btn-secondary">Batal</button>
                <button @click="submitForm()" :disabled="saving" class="btn-primary flex items-center gap-2">
                    <svg x-show="saving" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Update' : 'Simpan')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ======= DELETE CONFIRMATION MODAL ======= --}}
    <div x-show="showDeleteModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
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
                <button @click="executeDelete()" class="bg-red-500 text-white px-5 py-2 rounded-xl hover:bg-red-600 transition font-semibold text-sm">
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
                <div class="hidden lg:flex w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl items-center justify-center">
                    <i class="bi bi-receipt text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-gray-800">Kwitansi v1</h1>
                    <p class="text-xs text-gray-500 hidden md:block">Kelola kwitansi donasi</p>
                </div>
            </div>
            <button @click="openCreateModal()" class="flex items-center gap-2 px-3 md:px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition shadow-lg shadow-primary-500/30">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden md:inline">Tambah Kwitansi</span>
            </button>
        </div>
    </header>

    <!-- Content -->
    <div class="p-4 md:p-6 space-y-6">

        <!-- Stats Cards -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-receipt text-primary-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="stats.total_kwitansi ?? '-'"></p>
                <p class="text-xs text-gray-500 mt-1">Total Kwitansi</p>
            </div>
            <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-cash-stack text-blue-600"></i>
                    </div>
                </div>
                <p class="text-lg font-bold text-gray-800" x-text="stats.total_nominal_formatted ?? '-'"></p>
                <p class="text-xs text-gray-500 mt-1">Total Nominal</p>
            </div>
            <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar-check text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="stats.today_count ?? '-'"></p>
                <p class="text-xs text-gray-500 mt-1">Hari Ini</p>
            </div>
            <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-wallet2 text-orange-600"></i>
                    </div>
                </div>
                <p class="text-lg font-bold text-gray-800" x-text="stats.today_nominal_formatted ?? '-'"></p>
                <p class="text-xs text-gray-500 mt-1">Nominal Hari Ini</p>
            </div>
        </section>

        <!-- Filters & Table -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-3 md:p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3">
                    <!-- Search -->
                    <div class="flex-1 min-w-0 sm:min-w-[200px]">
                        <div class="relative">
                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" x-model.debounce.500ms="filters.search"
                                   @input="resetAndLoad()"
                                   placeholder="Cari nama, nomor kwitansi, program..."
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition text-sm">
                        </div>
                    </div>
                    <!-- Filter Tanggal -->
                    <div class="flex gap-2">
                        <button @click="toggleTodayFilter()"
                                :class="filters.tanggal === 'today' ? 'bg-primary-500 text-white border-primary-500' : 'bg-white text-gray-600 border-gray-200 hover:border-primary-300'"
                                class="px-4 py-2.5 border rounded-xl text-sm font-medium transition flex items-center gap-2">
                            <i class="bi bi-calendar-check"></i>
                            Hari Ini
                        </button>
                        <input type="date" x-model="filters.tanggal_custom"
                               @change="applyDateFilter()"
                               class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        <button x-show="filters.tanggal"
                                @click="clearDateFilter()"
                                class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition text-sm">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div x-show="selectedIds.length > 0" x-transition class="mt-3 flex items-center gap-3 p-3 bg-primary-50 rounded-xl">
                    <span class="text-sm font-medium text-primary-700">
                        <span x-text="selectedIds.length"></span> dipilih
                    </span>
                    <div class="flex-1"></div>
                    <button @click="confirmBulkDelete()" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition flex items-center gap-1">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left w-10">
                                <input type="checkbox" @change="toggleSelectAll($event)" class="checkbox-custom" :checked="selectedIds.length === tableData.length && tableData.length > 0">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:text-primary-600"
                                @click="sortBy('nomor_kwitansi')">
                                No. Kwitansi
                                <i class="bi" :class="filters.sort === 'nomor_kwitansi' ? (filters.order === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up text-gray-300'"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:text-primary-600"
                                @click="sortBy('tanggal')">
                                Tanggal
                                <i class="bi" :class="filters.sort === 'tanggal' ? (filters.order === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up text-gray-300'"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:text-primary-600"
                                @click="sortBy('nama_donatur')">
                                Nama Donatur
                                <i class="bi" :class="filters.sort === 'nama_donatur' ? (filters.order === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up text-gray-300'"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Program</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:text-primary-600"
                                @click="sortBy('jumlah_donasi')">
                                Jumlah Donasi
                                <i class="bi" :class="filters.sort === 'jumlah_donasi' ? (filters.order === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up text-gray-300'"></i>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!-- Loading -->
                        <tr x-show="loadingTable">
                            <td colspan="7" class="py-12 text-center">
                                <div class="spinner mx-auto mb-2"></div>
                                <p class="text-sm text-gray-500">Memuat data...</p>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr x-show="!loadingTable && tableData.length === 0">
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="bi bi-receipt text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">Belum ada data kwitansi</p>
                            </td>
                        </tr>
                        <!-- Data Rows -->
                        <template x-for="item in tableData" :key="item.id">
                            <tr class="table-row">
                                <td class="px-4 py-3">
                                    <input type="checkbox" :value="item.id" class="checkbox-custom"
                                           :checked="selectedIds.includes(item.id)"
                                           @change="toggleSelect(item.id)">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-mono text-primary-600 font-medium" x-text="item.nomor_kwitansi || '-'"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700" x-text="item.tanggal_formatted || item.tanggal"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-800" x-text="item.nama_donatur"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600" x-text="item.nama_donasi || '-'"></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-800" x-text="item.jumlah_donasi_formatted"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a :href="'/kwitansi/' + item.id + '/pdf'" target="_blank"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-primary-50 text-primary-600 transition" title="Print PDF">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <button @click="openEditModal(item.id)"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-600 transition" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button @click="confirmDelete(item.id, item.nama_donatur)"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50 text-red-500 transition" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-sm text-gray-500">
                    Menampilkan <span x-text="pagination.from ?? 0"></span> - <span x-text="pagination.to ?? 0"></span>
                    dari <span x-text="pagination.total ?? 0"></span> data
                </p>
                <div class="flex items-center gap-2">
                    <button @click="prevPage()" :disabled="!pagination.prev_page_url" class="pagination-btn">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="text-sm text-gray-600">
                        Halaman <span x-text="pagination.current_page"></span> / <span x-text="pagination.last_page"></span>
                    </span>
                    <button @click="nextPage()" :disabled="!pagination.next_page_url" class="pagination-btn">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>

@push('scripts')
<script>
function kwitansiV1App() {
    return {
        // State
        loading: true,
        loadingTable: false,
        saving: false,
        showFormModal: false,
        showDeleteModal: false,
        editingId: null,
        deleteMessage: '',
        deleteIds: [],

        // Stats
        stats: {},

        // Table
        tableData: [],
        pagination: {},
        selectedIds: [],

        // Filters
        filters: {
            search: '',
            tanggal: '',
            tanggal_custom: '',
            sort: 'tanggal',
            order: 'desc',
            page: 1,
        },

        // Form
        form: {
            tanggal: '',
            nama_donatur: '',
            nama_donasi: '',
            jumlah_donasi: '',
            jumlah_donasi_display: '',
        },
        formErrors: {},

        // Init
        async init() {
            await Promise.all([this.loadStats(), this.loadTable()]);
            this.loading = false;
        },

        // ===== API Calls =====
        async loadStats() {
            try {
                const params = new URLSearchParams();
                if (this.filters.tanggal) params.append('tanggal', this.filters.tanggal);
                const res = await fetch('/api/kwitansi-v1/stats?' + params.toString());
                this.stats = await res.json();
            } catch (e) {
                console.error('Error loading stats:', e);
            }
        },

        async loadTable() {
            this.loadingTable = true;
            try {
                const params = new URLSearchParams({
                    page: this.filters.page,
                    per_page: 20,
                    search: this.filters.search,
                    sort: this.filters.sort,
                    order: this.filters.order,
                });
                if (this.filters.tanggal) params.append('tanggal', this.filters.tanggal);

                const res = await fetch('/api/kwitansi-v1/list?' + params.toString());
                const data = await res.json();

                this.tableData = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                    total: data.total,
                    prev_page_url: data.prev_page_url,
                    next_page_url: data.next_page_url,
                };
                this.selectedIds = [];
            } catch (e) {
                console.error('Error loading table:', e);
            }
            this.loadingTable = false;
        },

        // ===== Filters =====
        resetAndLoad() {
            this.filters.page = 1;
            this.loadTable();
        },

        toggleTodayFilter() {
            this.filters.tanggal = this.filters.tanggal === 'today' ? '' : 'today';
            this.filters.tanggal_custom = '';
            this.filters.page = 1;
            this.loadTable();
            this.loadStats();
        },

        applyDateFilter() {
            if (this.filters.tanggal_custom) {
                this.filters.tanggal = this.filters.tanggal_custom;
            }
            this.filters.page = 1;
            this.loadTable();
            this.loadStats();
        },

        clearDateFilter() {
            this.filters.tanggal = '';
            this.filters.tanggal_custom = '';
            this.filters.page = 1;
            this.loadTable();
            this.loadStats();
        },

        sortBy(column) {
            if (this.filters.sort === column) {
                this.filters.order = this.filters.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sort = column;
                this.filters.order = 'desc';
            }
            this.loadTable();
        },

        // ===== Pagination =====
        prevPage() {
            if (this.pagination.current_page > 1) {
                this.filters.page = this.pagination.current_page - 1;
                this.loadTable();
            }
        },
        nextPage() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.filters.page = this.pagination.current_page + 1;
                this.loadTable();
            }
        },

        // ===== Selection =====
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) this.selectedIds.splice(idx, 1);
            else this.selectedIds.push(id);
        },
        toggleSelectAll(event) {
            this.selectedIds = event.target.checked ? this.tableData.map(d => d.id) : [];
        },

        // ===== Form Modal =====
        openCreateModal() {
            this.resetForm();
            this.form.tanggal = new Date().toISOString().split('T')[0];
            this.showFormModal = true;
        },

        async openEditModal(id) {
            this.resetForm();
            this.editingId = id;
            this.showFormModal = true;
            this.saving = true;

            try {
                const res = await fetch(`/api/kwitansi-v1/${id}`);
                const json = await res.json();
                const d = json.data;
                this.form.tanggal = d.tanggal || '';
                this.form.nama_donatur = d.nama_donatur || '';
                this.form.nama_donasi = d.nama_donasi || '';
                this.form.jumlah_donasi = d.jumlah_donasi || '';
                this.form.jumlah_donasi_display = this.numberFormat(d.jumlah_donasi || 0);
            } catch (e) {
                console.error('Error loading kwitansi:', e);
                this.showToast('Gagal memuat data', 'error');
            }
            this.saving = false;
        },

        closeFormModal() {
            this.showFormModal = false;
            this.resetForm();
        },

        resetForm() {
            this.editingId = null;
            this.form = { tanggal: '', nama_donatur: '', nama_donasi: '', jumlah_donasi: '', jumlah_donasi_display: '' };
            this.formErrors = {};
        },

        formatCurrency() {
            let raw = this.form.jumlah_donasi_display.replace(/\D/g, '');
            this.form.jumlah_donasi = raw;
            this.form.jumlah_donasi_display = this.numberFormat(raw);
        },

        numberFormat(num) {
            if (!num) return '';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        async submitForm() {
            this.formErrors = {};
            this.saving = true;

            const payload = {
                tanggal: this.form.tanggal,
                nama_donatur: this.form.nama_donatur,
                nama_donasi: this.form.nama_donasi,
                jumlah_donasi: parseFloat(this.form.jumlah_donasi) || 0,
            };

            try {
                const url = this.editingId ? `/api/kwitansi-v1/${this.editingId}` : '/api/kwitansi-v1';
                const method = this.editingId ? 'PUT' : 'POST';

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
                    if (data.errors) this.formErrors = data.errors;
                    else this.showToast(data.message || 'Terjadi kesalahan', 'error');
                    this.saving = false;
                    return;
                }

                this.showToast(data.message || 'Berhasil disimpan!', 'success');
                this.closeFormModal();
                await Promise.all([this.loadTable(), this.loadStats()]);
            } catch (e) {
                console.error('Submit error:', e);
                this.showToast('Terjadi kesalahan jaringan', 'error');
            }
            this.saving = false;
        },

        // ===== Delete =====
        confirmDelete(id, name) {
            this.deleteIds = [id];
            this.deleteMessage = `Hapus kwitansi "${name}"?`;
            this.showDeleteModal = true;
        },

        confirmBulkDelete() {
            this.deleteIds = [...this.selectedIds];
            this.deleteMessage = `Hapus ${this.deleteIds.length} kwitansi yang dipilih?`;
            this.showDeleteModal = true;
        },

        async executeDelete() {
            this.showDeleteModal = false;
            this.loading = true;

            try {
                if (this.deleteIds.length === 1) {
                    await fetch(`/api/kwitansi-v1/${this.deleteIds[0]}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                } else {
                    await fetch('/api/kwitansi-v1/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ ids: this.deleteIds }),
                    });
                }
                this.showToast('Data berhasil dihapus', 'success');
                await Promise.all([this.loadTable(), this.loadStats()]);
            } catch (e) {
                console.error('Delete error:', e);
                this.showToast('Gagal menghapus data', 'error');
            }
            this.loading = false;
        },

        // ===== Toast =====
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                info: 'bg-gray-800 text-white',
            };
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl shadow-lg z-[200] transition-all transform ${colors[type] || colors.info}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
