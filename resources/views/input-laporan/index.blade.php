<x-layouts.app active="input-laporan" title="Input Laporan - CRM Dashboard" xData="inputLaporanApp()">

@push('styles')
<style>
    .form-section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .field-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px 12px;
        font-size: 13px; transition: all 0.15s; background: white;
    }
    .field-input:focus { outline: 2px solid transparent; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
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
    .btn-danger { background: #EF4444; color: white; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-danger:hover { background: #DC2626; }
    .table-row { transition: background 0.15s; }
    .table-row:hover { background: #F0FDF4; }
    .badge { font-size: 10px; padding: 2px 8px; border-radius: 9999px; font-weight: 600; }
    .pagination-btn { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 500; border: 1px solid #E5E7EB; background: white; cursor: pointer; transition: all 0.15s; }
    .pagination-btn:hover:not(:disabled) { background: #F0FDF4; border-color: #059669; color: #059669; }
    .pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pagination-btn.active { background: #059669; color: white; border-color: #059669; }
    .checkbox-custom { width: 16px; height: 16px; accent-color: #059669; cursor: pointer; }
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
         role="dialog" aria-modal="true" aria-label="Form Input Laporan"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click.self="closeFormModal()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center z-[80] p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-3xl my-8 shadow-2xl relative" @click.stop
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
                        <h3 class="text-lg font-bold text-gray-800" x-text="editingId ? 'Edit Laporan' : 'Tambah Laporan Baru'"></h3>
                        <p class="text-xs text-gray-500">Isi data laporan perolehan harian</p>
                    </div>
                </div>
                <button @click="closeFormModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600" aria-label="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Form Body --}}
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">

                {{-- Section 1: Info Dasar --}}
                <div>
                    <p class="form-section-title text-primary-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-info-circle"></i> Informasi Dasar
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" x-model="form.tanggal" class="field-input" :class="{ 'error': formErrors.tanggal }">
                            <p x-show="formErrors.tanggal" class="field-error" x-text="formErrors.tanggal?.[0]"></p>
                        </div>
                        <div>
                            <label class="field-label">Tim <span class="text-red-500">*</span></label>
                            <select x-model="form.tim" @change="filterCsByTim()" class="field-input" :class="{ 'error': formErrors.tim }">
                                <option value="">-- Pilih Tim --</option>
                                <template x-for="t in options.tim" :key="t">
                                    <option :value="t" x-text="t"></option>
                                </template>
                            </select>
                            <p x-show="formErrors.tim" class="field-error" x-text="formErrors.tim?.[0]"></p>
                        </div>
                        <div>
                            <label class="field-label">Nama CS <span class="text-red-500">*</span></label>
                            <select x-model="form.nama_cs" class="field-input" :class="{ 'error': formErrors.nama_cs }">
                                <option value="">-- Pilih CS --</option>
                                <template x-for="cs in filteredCsNames" :key="cs">
                                    <option :value="cs" x-text="cs"></option>
                                </template>
                            </select>
                            <p x-show="formErrors.nama_cs" class="field-error" x-text="formErrors.nama_cs?.[0]"></p>
                        </div>
                        <div>
                            <label class="field-label">Jam Perolehan <span class="text-red-500">*</span></label>
                            <select x-model="form.perolehan_jam" class="field-input" :class="{ 'error': formErrors.perolehan_jam }">
                                <option value="">-- Pilih Jam --</option>
                                <template x-for="j in options.perolehan_jam" :key="j">
                                    <option :value="j" x-text="j"></option>
                                </template>
                            </select>
                            <p x-show="formErrors.perolehan_jam" class="field-error" x-text="formErrors.perolehan_jam?.[0]"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Perolehan & Program --}}
                <div>
                    <p class="form-section-title text-blue-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-graph-up-arrow"></i> Perolehan & Program
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Jumlah Database</label>
                            <input type="number" x-model="form.jml_database" class="field-input" min="0" placeholder="0">
                        </div>
                        <div>
                            <label class="field-label">Jumlah Perolehan (Rp)</label>
                            <input type="number" x-model="form.jml_perolehan" class="field-input" min="0" placeholder="0">
                        </div>
                        <div>
                            <label class="field-label">Hasil Dari</label>
                            <select x-model="form.hasil_dari" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="h in options.hasil_dari" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Program</label>
                            <input type="text" x-model="form.program" class="field-input" placeholder="Infaq, Palestina, dll">
                        </div>
                        <div>
                            <label class="field-label">Program Utama</label>
                            <input type="text" x-model="form.program_utama" class="field-input" placeholder="Program utama">
                        </div>
                        <div>
                            <label class="field-label">Prg Cross Selling</label>
                            <select x-model="form.prg_cross_selling" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="p in options.prg_cross_selling" :key="p">
                                    <option :value="p" x-text="p"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Nama Produk</label>
                            <input type="text" x-model="form.nama_produk" class="field-input" placeholder="Nama produk">
                        </div>
                        <div>
                            <label class="field-label">Zakat</label>
                            <input type="text" x-model="form.zakat" class="field-input" placeholder="Zakat">
                        </div>
                        <div>
                            <label class="field-label">Wakaf</label>
                            <input type="text" x-model="form.wakaf" class="field-input" placeholder="Wakaf">
                        </div>
                    </div>
                </div>

                {{-- Section 3: Donatur --}}
                <div>
                    <p class="form-section-title text-amber-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-person-vcard"></i> Data Donatur
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Nama Donatur</label>
                            <input type="text" x-model="form.nama_donatur" class="field-input" placeholder="Nama donatur">
                        </div>
                        <div>
                            <label class="field-label">No HP</label>
                            <input type="text" x-model="form.no_hp" @input.debounce.500ms="lookupDonatur()" class="field-input" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="relative">
                            <label class="field-label">DID <span class="text-xs text-gray-400">(otomatis)</span></label>
                            <input type="text" x-model="form.did" class="field-input bg-gray-50" readonly placeholder="Auto-fill dari No HP">
                            <template x-if="lookingUpDonatur">
                                <div class="absolute right-3 top-8">
                                    <svg class="animate-spin h-4 w-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="field-label">Kategori Donatur</label>
                            <select x-model="form.kat_donatur" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="k in options.kat_donatur" :key="k">
                                    <option :value="k" x-text="k"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Jenis Kelamin</label>
                            <select x-model="form.jenis_kelamin" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="g in options.jenis_kelamin" :key="g">
                                    <option :value="g" x-text="g"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Kode Negara</label>
                            <input type="text" x-model="form.kode_negara" class="field-input" placeholder="62">
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" x-model="form.email" class="field-input" placeholder="email@contoh.com">
                        </div>
                        <div>
                            <label class="field-label">Sosmed Account</label>
                            <input type="text" x-model="form.sosmed_account" class="field-input" placeholder="@username">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="field-label">Alamat</label>
                            <textarea x-model="form.alamat" class="field-input" rows="2" placeholder="Alamat lengkap"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Channel & Toko --}}
                <div>
                    <p class="form-section-title text-purple-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-shop"></i> Channel & Toko
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Channel</label>
                            <select x-model="form.channel" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="c in options.channel" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">E-Commerce</label>
                            <select x-model="form.e_commerce" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="e in options.e_commerce" :key="e">
                                    <option :value="e" x-text="e"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Nama Platform</label>
                            <select x-model="form.nama_platform" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="p in options.nama_platform" :key="p">
                                    <option :value="p" x-text="p"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Nama Toko</label>
                            <input type="text" x-model="form.nama_toko" class="field-input" placeholder="Nama toko">
                        </div>
                        <div>
                            <label class="field-label">Adsense</label>
                            <input type="text" x-model="form.adsense" class="field-input" placeholder="Adsense">
                        </div>
                        <div>
                            <label class="field-label">Jenis Konten</label>
                            <input type="text" x-model="form.jenis_konten" class="field-input" placeholder="Jenis konten">
                        </div>
                    </div>
                </div>

                {{-- Section 5: Banking & Follow-up --}}
                <div>
                    <p class="form-section-title text-rose-700 mb-3 flex items-center gap-2">
                        <i class="bi bi-bank2"></i> Banking & Follow-up
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Nama Bank</label>
                            <select x-model="form.nama_bank" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="b in options.nama_bank" :key="b">
                                    <option :value="b" x-text="b"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">No Rekening</label>
                            <input type="text" x-model="form.no_rek" class="field-input" placeholder="No rekening">
                        </div>
                        <div>
                            <label class="field-label">Follow-up WA</label>
                            <select x-model="form.followup_wa" class="field-input">
                                <option value="">-- Pilih --</option>
                                <template x-for="f in options.followup_wa" :key="f">
                                    <option :value="f" x-text="f"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Fundraiser</label>
                            <input type="text" x-model="form.fundraiser" class="field-input" placeholder="Fundraiser">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label">Keterangan</label>
                            <textarea x-model="form.keterangan" class="field-input" rows="2" placeholder="Catatan tambahan"></textarea>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3 bg-gray-50/50 rounded-b-2xl">
                <button type="button" @click="closeFormModal()" class="btn-secondary">Batal</button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-primary flex items-center gap-2">
                    <svg x-show="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="submitting ? 'Menyimpan...' : (editingId ? 'Perbarui' : 'Simpan')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ======= DELETE CONFIRM MODAL ======= --}}
    <div x-show="showDeleteModal" x-cloak
         role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Laporan"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         @click.self="showDeleteModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[90] p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center" @click.stop>
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Laporan?</h3>
            <p class="text-sm text-gray-500 mb-6" x-text="'Laporan dari ' + (deletingRow?.nama_cs || '') + ' pada ' + formatDate(deletingRow?.tanggal) + ' akan dihapus permanen.'"></p>
            <div class="flex gap-3 justify-center">
                <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
                <button @click="executeDelete()" :disabled="submitting" class="btn-danger px-6 py-2 text-sm">
                    <span x-text="submitting ? 'Menghapus...' : 'Ya, Hapus'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ======= TOAST ======= --}}
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-[100] max-w-sm">
        <div class="flex items-center gap-3 px-5 py-3 rounded-xl shadow-lg border"
             :class="toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'">
            <i class="bi text-lg" :class="toast.type === 'success' ? 'bi-check-circle-fill text-emerald-500' : 'bi-x-circle-fill text-red-500'"></i>
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>
    </div>
@endpush

{{-- ======= STICKY HEADER ======= --}}
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="flex items-center justify-between px-4 md:px-6 py-3">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl transition" aria-label="Toggle menu">
                <i class="bi bi-list text-xl"></i>
            </button>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg md:text-xl font-bold text-gray-800">Input Laporan</h1>
                    <span class="px-2 py-0.5 bg-primary-100 text-primary-700 text-[10px] font-bold rounded-full uppercase" x-text="(pagination.total || 0).toLocaleString('id-ID') + ' Data'"></span>
                </div>
                <p class="text-xs text-gray-500">Kelola data laporan perolehan harian CS</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="fetchData(); fetchStats()" class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition" title="Refresh" aria-label="Refresh data">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-500 text-white text-xs font-semibold rounded-xl hover:bg-primary-600 shadow-lg shadow-primary-500/20 transition">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">Tambah Laporan</span>
            </button>
        </div>
    </div>
</header>

{{-- ======= CONTENT AREA ======= --}}
<div class="p-4 md:p-6 space-y-5">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-file-earmark-text-fill text-blue-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Laporan</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="stats.total_laporan?.toLocaleString('id-ID') ?? '-'"></div>
            <div class="flex items-center gap-1 mt-1">
                <template x-if="stats.growth_laporan !== null && stats.growth_laporan > 0"><span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="stats.growth_laporan + '%'"></span></span></template>
                <template x-if="stats.growth_laporan !== null && stats.growth_laporan < 0"><span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(stats.growth_laporan) + '%'"></span></span></template>
                <template x-if="stats.growth_laporan !== null && stats.growth_laporan === 0"><span class="text-[10px] text-gray-400">Stabil</span></template>
                <template x-if="stats.growth_laporan === null"><span class="text-[10px] text-gray-400">Semua data</span></template>
            </div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Perolehan</span>
            </div>
            <div class="text-xl font-bold text-gray-800" x-text="'Rp ' + (stats.total_perolehan || 0).toLocaleString('id-ID')"></div>
            <div class="flex items-center gap-1 mt-1">
                <template x-if="stats.growth_perolehan !== null && stats.growth_perolehan > 0"><span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="stats.growth_perolehan + '%'"></span></span></template>
                <template x-if="stats.growth_perolehan !== null && stats.growth_perolehan < 0"><span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(stats.growth_perolehan) + '%'"></span></span></template>
                <template x-if="stats.growth_perolehan !== null && stats.growth_perolehan === 0"><span class="text-[10px] text-gray-400">Stabil</span></template>
                <template x-if="stats.growth_perolehan === null"><span class="text-[10px] text-gray-400">Semua data</span></template>
            </div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-database-fill text-amber-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Database</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="(stats.total_database || 0).toLocaleString('id-ID')"></div>
        </div>
        <div class="stat-card bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-people-fill text-purple-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">CS Aktif Hari Ini</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="stats.total_cs || '0'"></div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model.debounce.400ms="filters.search" @input="resetPage(); fetchData()"
                       placeholder="Cari nama CS, donatur, DID, no HP..."
                       class="field-input pl-9">
            </div>
            <select x-model="filters.tim" @change="resetPage(); fetchData()" class="field-input w-auto min-w-[140px]">
                <option value="all">Semua Tim</option>
                <template x-for="t in options.tim" :key="t">
                    <option :value="t" x-text="t"></option>
                </template>
            </select>
            <input type="date" x-model="filters.tanggal" @change="resetPage(); fetchData(); fetchStats()"
                   class="field-input w-auto">
            <button @click="clearFilters()" class="btn-secondary flex items-center gap-1 whitespace-nowrap">
                <i class="bi bi-x-circle text-xs"></i>
                <span>Reset</span>
            </button>
        </div>
        {{-- Bulk Actions --}}
        <div x-show="selectedIds.length > 0" x-cloak class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-3">
            <span class="text-xs text-gray-500"><strong x-text="selectedIds.length"></strong> item dipilih</span>
            <button @click="bulkDelete()" class="btn-danger flex items-center gap-1">
                <i class="bi bi-trash3 text-xs"></i>
                <span>Hapus Terpilih</span>
            </button>
            <button @click="selectedIds = []; selectAll = false" class="text-xs text-gray-500 hover:text-gray-700 cursor-pointer">Batal</button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-3 py-3 w-10">
                            <input type="checkbox" class="checkbox-custom" x-model="selectAll" @change="toggleSelectAll()">
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 cursor-pointer hover:text-primary-600" @click="toggleSort('tanggal')">
                            Tanggal <i class="bi" :class="sortIcon('tanggal')"></i>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 cursor-pointer hover:text-primary-600" @click="toggleSort('tim')">
                            Tim <i class="bi" :class="sortIcon('tim')"></i>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 cursor-pointer hover:text-primary-600" @click="toggleSort('nama_cs')">
                            Nama CS <i class="bi" :class="sortIcon('nama_cs')"></i>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Jam</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Donatur</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 cursor-pointer hover:text-primary-600" @click="toggleSort('jml_perolehan')">
                            Perolehan <i class="bi" :class="sortIcon('jml_perolehan')"></i>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 cursor-pointer hover:text-primary-600" @click="toggleSort('hasil_dari')">
                            Hasil Dari <i class="bi" :class="sortIcon('hasil_dari')"></i>
                        </th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody x-show="rows.length > 0">
                    <template x-for="row in rows" :key="row.id">
                        <tr class="table-row border-b border-gray-50">
                            <td class="px-3 py-2.5">
                                <input type="checkbox" class="checkbox-custom" :value="row.id" x-model="selectedIds">
                            </td>
                            <td class="px-3 py-2.5 text-gray-700 whitespace-nowrap" x-text="formatDate(row.tanggal)"></td>
                            <td class="px-3 py-2.5">
                                <span class="badge" :class="teamBadgeClass(row.tim)" x-text="row.tim || '-'"></span>
                            </td>
                            <td class="px-3 py-2.5 font-medium text-gray-800" x-text="row.nama_cs || '-'"></td>
                            <td class="px-3 py-2.5 text-gray-500 text-xs whitespace-nowrap" x-text="row.perolehan_jam || '-'"></td>
                            <td class="px-3 py-2.5 text-gray-700">
                                <div x-text="row.nama_donatur || '-'" class="font-medium text-xs"></div>
                                <div x-show="row.no_hp" class="text-[11px] text-gray-400" x-text="row.no_hp"></div>
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-800" x-text="row.jml_perolehan ? 'Rp ' + Number(row.jml_perolehan).toLocaleString('id-ID') : '-'"></td>
                            <td class="px-3 py-2.5 text-gray-600 text-xs" x-text="row.hasil_dari || '-'"></td>
                            <td class="px-3 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(row)" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit" aria-label="Edit">
                                        <i class="bi bi-pencil-square text-sm"></i>
                                    </button>
                                    <button @click="confirmDelete(row)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus" aria-label="Hapus">
                                        <i class="bi bi-trash3 text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div x-show="!loading && rows.length === 0" class="py-16 text-center">
            <i class="bi bi-inbox text-4xl text-gray-300"></i>
            <p class="text-gray-400 mt-3 text-sm">Belum ada data laporan</p>
            <button @click="openCreateModal()" class="mt-3 text-primary-600 text-sm font-semibold hover:underline">
                + Tambah Laporan Pertama
            </button>
        </div>

        {{-- Pagination --}}
        <div x-show="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-xs text-gray-500">
                Menampilkan <strong x-text="pagination.from || 0"></strong> - <strong x-text="pagination.to || 0"></strong>
                dari <strong x-text="pagination.total?.toLocaleString('id-ID') || 0"></strong> data
            </p>
            <div class="flex items-center gap-1">
                <button class="pagination-btn" :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)" aria-label="Halaman sebelumnya">
                    <i class="bi bi-chevron-left text-xs"></i>
                </button>
                <template x-for="p in paginationPages()" :key="'pg'+p">
                    <button class="pagination-btn"
                            :class="{ 'active' : p === pagination.current_page }"
                            :disabled="p === '...'"
                            @click="p !== '...' && goToPage(p)"
                            x-text="p"></button>
                </template>
                <button class="pagination-btn" :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.current_page + 1)" aria-label="Halaman berikutnya">
                    <i class="bi bi-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function inputLaporanApp() {
    return {
        // ─── State ──────────────────────────────
        loading: true,
        submitting: false,
        lookingUpDonatur: false,
        rows: [],
        stats: {},
        options: { tim: [], cs_names: [], perolehan_jam: [], hasil_dari: [], prg_cross_selling: [], followup_wa: [], kat_donatur: [], jenis_kelamin: [], nama_bank: [], channel: [], e_commerce: [], nama_platform: [] },
        filteredCsNames: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
        filters: { search: '', tim: 'all', tanggal: '' },
        sort: { field: 'created_at', order: 'desc' },
        selectedIds: [],
        selectAll: false,

        // ─── Modals ─────────────────────────────
        showFormModal: false,
        showDeleteModal: false,
        editingId: null,
        deletingRow: null,

        // ─── Form (inline default — NO this.emptyForm()) ───
        form: {
            tanggal: new Date().toISOString().split('T')[0],
            tim: '', nama_cs: '', perolehan_jam: '',
            jml_database: '', jml_perolehan: '',
            hasil_dari: '', program: '', program_utama: '', prg_cross_selling: '', nama_produk: '', zakat: '', wakaf: '',
            nama_donatur: '', did: '', kat_donatur: '', jenis_kelamin: '', kode_negara: '', no_hp: '', email: '', sosmed_account: '', alamat: '',
            channel: '', e_commerce: '', nama_platform: '', nama_toko: '', adsense: '', jenis_konten: '',
            nama_bank: '', no_rek: '', followup_wa: '', fundraiser: '', keterangan: '',
        },
        formErrors: {},

        // ─── Toast ──────────────────────────────
        toast: { show: false, message: '', type: 'success' },

        // ─── Init ───────────────────────────────
        init() {
            this.fetchOptions().then(() => {
                this.fetchData();
                this.fetchStats();
            });
        },

        emptyForm() {
            return {
                tanggal: new Date().toISOString().split('T')[0],
                tim: '', nama_cs: '', perolehan_jam: '',
                jml_database: '', jml_perolehan: '',
                hasil_dari: '', program: '', program_utama: '', prg_cross_selling: '', nama_produk: '', zakat: '', wakaf: '',
                nama_donatur: '', did: '', kat_donatur: '', jenis_kelamin: '', kode_negara: '', no_hp: '', email: '', sosmed_account: '', alamat: '',
                channel: '', e_commerce: '', nama_platform: '', nama_toko: '', adsense: '', jenis_konten: '',
                nama_bank: '', no_rek: '', followup_wa: '', fundraiser: '', keterangan: '',
            };
        },

        // ─── API Calls ──────────────────────────
        async fetchOptions() {
            try {
                const res = await fetch('/api/input-laporan/options');
                if (!res.ok) throw new Error('Gagal memuat opsi');
                this.options = await res.json();
            } catch (e) {
                console.error('fetchOptions:', e);
            }
        },

        async lookupDonatur() {
            const noHp = this.form.no_hp;
            if (!noHp || noHp.length < 8) {
                return;
            }
            
            this.lookingUpDonatur = true;
            try {
                const res = await fetch(`/api/input-laporan/lookup-donatur?no_hp=${encodeURIComponent(noHp)}`);
                if (!res.ok) throw new Error('Lookup failed');
                const data = await res.json();
                
                if (data.found && data.donatur) {
                    const d = data.donatur;
                    this.form.did = d.did || '';
                    if (!this.form.nama_donatur && d.nama_donatur) this.form.nama_donatur = d.nama_donatur;
                    if (!this.form.kat_donatur && d.kat_donatur) this.form.kat_donatur = d.kat_donatur;
                    if (!this.form.jenis_kelamin && d.jenis_kelamin) this.form.jenis_kelamin = d.jenis_kelamin;
                    if (!this.form.kode_negara && d.kode_negara) this.form.kode_negara = d.kode_negara;
                    if (!this.form.email && d.email) this.form.email = d.email;
                    if (!this.form.sosmed_account && d.sosmed_account) this.form.sosmed_account = d.sosmed_account;
                    if (!this.form.alamat && d.alamat) this.form.alamat = d.alamat;
                }
            } catch (e) {
                console.error('lookupDonatur:', e);
            } finally {
                this.lookingUpDonatur = false;
            }
        },

        async fetchData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: 20,
                    search: this.filters.search,
                    tim: this.filters.tim,
                    tanggal: this.filters.tanggal,
                    sort: this.sort.field,
                    order: this.sort.order,
                });
                const res = await fetch('/api/input-laporan/list?' + params);
                if (!res.ok) throw new Error('Gagal memuat data');
                const json = await res.json();
                this.rows = json.data || [];
                this.pagination = {
                    current_page: json.current_page,
                    last_page: json.last_page,
                    from: json.from,
                    to: json.to,
                    total: json.total,
                };
                this.selectedIds = [];
                this.selectAll = false;
            } catch (e) {
                console.error('fetchData:', e);
                this.showToast('Gagal memuat data laporan.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async fetchStats() {
            try {
                const params = this.filters.tanggal ? '?tanggal=' + this.filters.tanggal : '';
                const res = await fetch('/api/input-laporan/stats' + params);
                if (!res.ok) throw new Error('Gagal memuat stats');
                this.stats = await res.json();
            } catch (e) {
                console.error('fetchStats:', e);
            }
        },

        async submitForm() {
            this.submitting = true;
            this.formErrors = {};
            try {
                const url = this.editingId
                    ? `/api/input-laporan/${this.editingId}`
                    : '/api/input-laporan';
                const method = this.editingId ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();
                if (!res.ok) {
                    if (json.errors) {
                        this.formErrors = json.errors;
                        return;
                    }
                    throw new Error(json.message || 'Gagal menyimpan');
                }
                this.showToast(json.message || 'Berhasil disimpan!', 'success');
                this.closeFormModal();
                this.fetchData();
                this.fetchStats();
            } catch (e) {
                console.error('submitForm:', e);
                this.showToast(e.message || 'Terjadi kesalahan.', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async executeDelete() {
            if (!this.deletingRow) return;
            this.submitting = true;
            try {
                const res = await fetch(`/api/input-laporan/${this.deletingRow.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                this.showToast(json.message || 'Berhasil dihapus!', 'success');
                this.showDeleteModal = false;
                this.deletingRow = null;
                this.fetchData();
                this.fetchStats();
            } catch (e) {
                console.error('executeDelete:', e);
                this.showToast(e.message || 'Terjadi kesalahan.', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async bulkDelete() {
            if (!confirm(`Hapus ${this.selectedIds.length} laporan yang dipilih?`)) return;
            this.submitting = true;
            try {
                const res = await fetch('/api/input-laporan/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: this.selectedIds }),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                this.showToast(json.message, 'success');
                this.fetchData();
                this.fetchStats();
            } catch (e) {
                console.error('bulkDelete:', e);
                this.showToast(e.message || 'Terjadi kesalahan.', 'error');
            } finally {
                this.submitting = false;
            }
        },

        // ─── Modal Helpers ──────────────────────
        openCreateModal() {
            this.editingId = null;
            this.form = this.emptyForm();
            this.formErrors = {};
            this.filteredCsNames = this.getAllCsNames();
            this.showFormModal = true;
        },

        openEditModal(row) {
            this.editingId = row.id;
            this.form = { ...this.emptyForm() };
            for (const key in this.form) {
                if (row[key] !== undefined && row[key] !== null) {
                    this.form[key] = row[key];
                }
            }
            this.formErrors = {};
            this.filterCsByTim();
            this.showFormModal = true;
        },

        closeFormModal() {
            this.showFormModal = false;
            this.editingId = null;
            this.formErrors = {};
        },

        confirmDelete(row) {
            this.deletingRow = row;
            this.showDeleteModal = true;
        },

        // ─── CS Name Filtering ──────────────────
        filterCsByTim() {
            if (!this.form.tim) {
                this.filteredCsNames = this.getAllCsNames();
                return;
            }
            const names = (this.options.cs_names || [])
                .filter(cs => cs.tim === this.form.tim)
                .map(cs => cs.nama_cs);
            this.filteredCsNames = [...new Set(names)].sort();
            if (this.form.nama_cs && !this.filteredCsNames.includes(this.form.nama_cs)) {
                this.form.nama_cs = '';
            }
        },

        getAllCsNames() {
            const names = (this.options.cs_names || []).map(cs => cs.nama_cs);
            return [...new Set(names)].sort();
        },

        // ─── Sort & Pagination ──────────────────
        toggleSort(field) {
            if (this.sort.field === field) {
                this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort.field = field;
                this.sort.order = 'asc';
            }
            this.fetchData();
        },

        sortIcon(field) {
            if (this.sort.field !== field) return 'bi-arrow-down-up text-gray-300';
            return this.sort.order === 'asc' ? 'bi-sort-up text-primary-600' : 'bi-sort-down text-primary-600';
        },

        resetPage() {
            this.pagination.current_page = 1;
        },

        goToPage(page) {
            if (page < 1 || page > this.pagination.last_page) return;
            this.pagination.current_page = page;
            this.fetchData();
        },

        paginationPages() {
            const last = this.pagination.last_page;
            const curr = this.pagination.current_page;
            if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);
            const pages = [];
            pages.push(1);
            if (curr > 3) pages.push('...');
            for (let i = Math.max(2, curr - 1); i <= Math.min(last - 1, curr + 1); i++) pages.push(i);
            if (curr < last - 2) pages.push('...');
            pages.push(last);
            return pages;
        },

        clearFilters() {
            this.filters = { search: '', tim: 'all', tanggal: '' };
            this.resetPage();
            this.fetchData();
            this.fetchStats();
        },

        // ─── Selection ──────────────────────────
        toggleSelectAll() {
            this.selectedIds = this.selectAll ? this.rows.map(r => r.id) : [];
        },

        // ─── Formatting ─────────────────────────
        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        teamBadgeClass(tim) {
            const map = {
                'AB BARAT': 'bg-blue-100 text-blue-700',
                'WAKAF': 'bg-emerald-100 text-emerald-700',
                'CABANG': 'bg-amber-100 text-amber-700',
                'PRODUK': 'bg-purple-100 text-purple-700',
                'PLATFORM': 'bg-pink-100 text-pink-700',
                'KENCLENG': 'bg-teal-100 text-teal-700',
                'OFFLINE': 'bg-gray-100 text-gray-700',
                'Partnership': 'bg-indigo-100 text-indigo-700',
                'WANESIA': 'bg-rose-100 text-rose-700',
                'CRM': 'bg-cyan-100 text-cyan-700',
            };
            return map[tim] || 'bg-gray-100 text-gray-600';
        },

        // ─── Toast ──────────────────────────────
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },
    };
}
</script>
@endpush

</x-layouts.app>
