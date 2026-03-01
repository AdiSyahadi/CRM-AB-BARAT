<x-layouts.app active="absensi" title="Absensi" xData="absensiApp()">

{{-- ===== CUSTOM STYLES (plain CSS, no @apply) ===== --}}
@push('styles')
<style>
    .ab-stat-card {
        background: white; border-radius: 12px; padding: 16px; border: 1px solid #F3F4F6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    /* Tab buttons with proper plain CSS */
    .ab-tab-wrap {
        display: inline-flex; gap: 4px; background: #F3F4F6; border-radius: 12px; padding: 4px;
    }
    .ab-tab-btn {
        padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 10px;
        transition: all 0.2s; border: none; cursor: pointer; white-space: nowrap;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .ab-tab-active {
        background: #059669; color: white; box-shadow: 0 2px 6px rgba(5,150,105,0.3);
    }
    .ab-tab-inactive {
        background: transparent; color: #4B5563;
    }
    .ab-tab-inactive:hover { background: #E5E7EB; }
    .ab-badge {
        display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 500;
    }
    .ab-spin { animation: ab-spin 1s linear infinite; }
    @keyframes ab-spin { to { transform: rotate(360deg); } }
    @keyframes ab-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

{{-- ===== LOADING OVERLAY & MODALS ===== --}}
@push('before-sidebar')
{{-- Loading Overlay --}}
<div x-show="loading" x-cloak
     class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-sm flex items-center justify-center">
    <div class="flex flex-col items-center gap-3">
        <i class="bi bi-arrow-repeat text-3xl text-primary-600 ab-spin"></i>
        <span class="text-sm text-gray-500 font-medium">Memuat data absensi...</span>
    </div>
</div>

{{-- ============ UBUDIYAH FORM MODAL ============ --}}
<div x-show="showUbudiyahModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showUbudiyahModal && closeUbudiyahModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeUbudiyahModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingUbudiyahId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-indigo-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingUbudiyahId ? 'Edit Absensi Ubudiyah' : 'Tambah Absensi Ubudiyah'"></h3>
            </div>
            <button @click="closeUbudiyahModal()" aria-label="Tutup" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div>
                <label class="crud-field-label">Nama <span style="color:#EF4444">*</span></label>
                <input type="text" x-model="ubudiyahForm.nama" class="crud-field-input" placeholder="Nama pegawai">
                <template x-if="formErrors.nama"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama[0]"></p></template>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Tanggal <span style="color:#EF4444">*</span></label>
                    <input type="date" x-model="ubudiyahForm.tanggal" class="crud-field-input">
                </div>
                <div>
                    <label class="crud-field-label">Jam <span style="color:#EF4444">*</span></label>
                    <input type="time" x-model="ubudiyahForm.jam" class="crud-field-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Status <span style="color:#EF4444">*</span></label>
                    <select x-model="ubudiyahForm.status" class="crud-field-input">
                        <option value="">— Pilih —</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Tugas diluar">Tugas diluar</option>
                    </select>
                </div>
                <div>
                    <label class="crud-field-label">Ubudiyah</label>
                    <select x-model="ubudiyahForm.ubudiyah" class="crud-field-input">
                        <option value="">— Pilih —</option>
                        <option value="Shalat Tahajud">Shalat Tahajud</option>
                        <option value="Shalat Dhuha">Shalat Dhuha</option>
                        <option value="Tidak Ubudiyah">Tidak Ubudiyah</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="crud-field-label">Alamat</label>
                <input type="text" x-model="ubudiyahForm.alamat" class="crud-field-input" placeholder="Alamat/lokasi">
            </div>
            <div>
                <label class="crud-field-label">Keterangan</label>
                <input type="text" x-model="ubudiyahForm.keterangan" class="crud-field-input" placeholder="Catatan tambahan">
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeUbudiyahModal()" class="crud-btn-secondary">Batal</button>
            <button @click="submitUbudiyah()" :disabled="saving" class="crud-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat ab-spin"></i></template>
                <span x-text="editingUbudiyahId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ HARIAN CS FORM MODAL ============ --}}
<div x-show="showHarianModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[80] flex items-start justify-center pt-10 bg-black/30 backdrop-blur-sm overflow-y-auto pb-10"
     @keydown.escape.window="showHarianModal && closeHarianModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" @click.outside="closeHarianModal()">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div class="flex items-center gap-2">
                <i :class="editingHarianId ? 'bi bi-pencil-square text-amber-600' : 'bi bi-plus-circle-fill text-green-600'" class="text-lg"></i>
                <h3 class="font-semibold text-gray-800" x-text="editingHarianId ? 'Edit Absen Harian CS' : 'Tambah Absen Harian CS'"></h3>
            </div>
            <button @click="closeHarianModal()" aria-label="Tutup" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
            <div>
                <label class="crud-field-label">Nama CS <span style="color:#EF4444">*</span></label>
                <input type="text" x-model="harianForm.nama_cs" class="crud-field-input" placeholder="Nama CS">
                <template x-if="formErrors.nama_cs"><p style="font-size:12px;color:#EF4444;margin-top:4px" x-text="formErrors.nama_cs[0]"></p></template>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Tanggal <span style="color:#EF4444">*</span></label>
                    <input type="date" x-model="harianForm.tanggal" class="crud-field-input">
                </div>
                <div>
                    <label class="crud-field-label">Jam <span style="color:#EF4444">*</span></label>
                    <input type="time" x-model="harianForm.jam" class="crud-field-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="crud-field-label">Tipe Absen <span style="color:#EF4444">*</span></label>
                    <select x-model="harianForm.tipe_absen" class="crud-field-input">
                        <option value="">— Pilih —</option>
                        <option value="Masuk">Masuk</option>
                        <option value="Pulang">Pulang</option>
                    </select>
                </div>
                <div>
                    <label class="crud-field-label">Status Kehadiran</label>
                    <input type="text" x-model="harianForm.status_kehadiran" class="crud-field-input" placeholder="cth: HadirWFO">
                </div>
            </div>
            <div>
                <label class="crud-field-label">Lokasi</label>
                <input type="text" x-model="harianForm.lokasi" class="crud-field-input" placeholder="Lokasi absen">
            </div>
            <div>
                <label class="crud-field-label">Keterangan</label>
                <textarea x-model="harianForm.keterangan" class="crud-field-input" rows="2" placeholder="Catatan tambahan"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-2xl">
            <button @click="closeHarianModal()" class="crud-btn-secondary">Batal</button>
            <button @click="submitHarian()" :disabled="saving" class="crud-btn-primary">
                <template x-if="saving"><i class="bi bi-arrow-repeat ab-spin"></i></template>
                <span x-text="editingHarianId ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div x-show="showDeleteModal" x-cloak x-transition.opacity
     role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Absensi"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 backdrop-blur-sm"
     @keydown.escape.window="showDeleteModal && (showDeleteModal = false)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.outside="showDeleteModal = false">
        <div style="width:48px;height:48px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="bi bi-exclamation-triangle-fill text-xl" style="color:#DC2626"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Hapus Data Absensi?</h3>
        <p class="text-sm text-gray-500 mb-4">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-2 justify-center">
            <button @click="showDeleteModal = false" class="crud-btn-secondary">Batal</button>
            <button @click="executeDelete()" :disabled="saving" class="crud-btn-danger">
                <template x-if="saving"><i class="bi bi-arrow-repeat ab-spin"></i></template>
                Hapus
            </button>
        </div>
    </div>
</div>

{{-- ============ PHOTO PREVIEW MODAL ============ --}}
<div x-show="showPhotoModal" x-cloak x-transition.opacity
     class="fixed inset-0 z-[85] flex items-center justify-center bg-black/50 backdrop-blur-sm"
     @click="showPhotoModal = false" @keydown.escape.window="showPhotoModal = false">
    <div style="max-width:480px;margin:0 16px;position:relative" @click.stop>
        <img :src="photoUrl" style="border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.25);max-height:80vh;width:auto" alt="Foto Absensi">
        <button @click="showPhotoModal = false" aria-label="Tutup" style="position:absolute;top:8px;right:8px;background:rgba(255,255,255,0.8);border-radius:50%;padding:8px;border:none;cursor:pointer">
            <i class="bi bi-x-lg" style="color:#374151"></i>
        </button>
    </div>
</div>
@endpush

{{-- ===== MAIN CONTENT ===== --}}

{{-- Sticky Header --}}
<div class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button @click="$dispatch('toggle-sidebar')" aria-label="Toggle menu" class="lg:hidden text-gray-600"><i class="bi bi-list text-xl"></i></button>
        <h1 class="text-lg font-bold text-gray-800"><i class="bi bi-calendar-check-fill text-primary-600 mr-1"></i> Absensi</h1>
    </div>
    <div class="flex items-center gap-2">
        <input type="date" x-model="statDate" @change="loadStats()" class="crud-field-input" style="width:auto;font-size:12px" title="Tanggal statistik">
        <button @click="activeTab === 'ubudiyah' ? openUbudiyahCreate() : openHarianCreate()" class="crud-btn-primary" style="font-size:12px">
            <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Tambah</span>
        </button>
    </div>
</div>

<div class="p-4 sm:p-6 space-y-5">

    {{-- Stat Cards - showing BOTH daily + total --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="ab-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-moon-stars-fill" style="color:#4F46E5"></i><span style="font-size:12px;color:#6B7280">Ubudiyah</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.ubudiyah_today ?? '—'"></p>
            <p style="font-size:11px;color:#9CA3AF;margin-top:2px">Total: <span style="font-weight:600;color:#4F46E5" x-text="stats.ubudiyah_total ?? '-'"></span></p>
        </div>
        <div class="ab-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-sunrise-fill" style="color:#D97706"></i><span style="font-size:12px;color:#6B7280">Tahajud</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.tahajud_today ?? '—'"></p>
            <p style="font-size:11px;color:#9CA3AF;margin-top:2px" x-text="'Tgl: ' + (stats.tanggal || '-')"></p>
        </div>
        <div class="ab-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-sun-fill" style="color:#EA580C"></i><span style="font-size:12px;color:#6B7280">Dhuha</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.dhuha_today ?? '—'"></p>
            <p style="font-size:11px;color:#9CA3AF;margin-top:2px" x-text="'Tgl: ' + (stats.tanggal || '-')"></p>
        </div>
        <div class="ab-stat-card">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-box-arrow-in-right" style="color:#16A34A"></i><span style="font-size:12px;color:#6B7280">CS Masuk</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937" x-text="stats.cs_masuk_today ?? '—'"></p>
            <p style="font-size:11px;color:#9CA3AF;margin-top:2px">Total CS: <span style="font-weight:600;color:#16A34A" x-text="stats.cs_total ?? '-'"></span></p>
        </div>
        <div class="ab-stat-card col-span-2 lg:col-span-1">
            <div class="flex items-center gap-2 mb-1"><i class="bi bi-building" style="color:#0284C7"></i><span style="font-size:12px;color:#6B7280">WFO / WFH</span></div>
            <p style="font-size:24px;font-weight:700;color:#1F2937">
                <span x-text="stats.cs_wfo ?? '—'"></span>
                <span style="font-size:14px;color:#9CA3AF">/</span>
                <span x-text="stats.cs_wfh ?? '—'"></span>
            </p>
            <p style="font-size:11px;color:#9CA3AF;margin-top:2px" x-text="'Tgl: ' + (stats.tanggal || '-')"></p>
        </div>
    </div>

    {{-- Tab Selector with proper plain CSS --}}
    <div class="ab-tab-wrap">
        <button @click="switchTab('ubudiyah')" class="ab-tab-btn"
                :class="activeTab === 'ubudiyah' ? 'ab-tab-active' : 'ab-tab-inactive'">
            <i class="bi bi-moon-stars"></i> Absensi Ubudiyah
        </button>
        <button @click="switchTab('harian')" class="ab-tab-btn"
                :class="activeTab === 'harian' ? 'ab-tab-active' : 'ab-tab-inactive'">
            <i class="bi bi-clock-history"></i> Absen Harian CS
        </button>
    </div>

    {{-- ========== TAB: UBUDIYAH ========== --}}
    <div x-show="activeTab === 'ubudiyah'" x-cloak>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2 mb-4">
            <div class="relative flex-1" style="min-width:160px">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
                <input type="text" x-model="uFilter.search" @input.debounce.500ms="loadUbudiyah(1)" class="crud-field-input" style="padding-left:36px" placeholder="Cari nama, alamat...">
            </div>
            <select x-model="uFilter.status" @change="loadUbudiyah(1)" class="crud-field-input" style="width:auto;font-size:12px">
                <option value="">Semua Status</option>
                <option value="Hadir">Hadir</option>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
                <option value="Tugas diluar">Tugas diluar</option>
            </select>
            <select x-model="uFilter.ubudiyah" @change="loadUbudiyah(1)" class="crud-field-input" style="width:auto;font-size:12px">
                <option value="">Semua Ubudiyah</option>
                <option value="Shalat Tahajud">Shalat Tahajud</option>
                <option value="Shalat Dhuha">Shalat Dhuha</option>
                <option value="Tidak Ubudiyah">Tidak Ubudiyah</option>
            </select>
            <input type="date" x-model="uFilter.tanggal_dari" @change="loadUbudiyah(1)" class="crud-field-input" style="width:auto;font-size:12px" title="Dari tanggal">
            <input type="date" x-model="uFilter.tanggal_sampai" @change="loadUbudiyah(1)" class="crud-field-input" style="width:auto;font-size:12px" title="Sampai tanggal">
            <button @click="clearUFilter()" style="font-size:12px;color:#6B7280;cursor:pointer;border:none;background:none" title="Reset" aria-label="Reset filter"><i class="bi bi-x-circle"></i></button>
        </div>

        {{-- Ubudiyah Table --}}
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" style="font-size:14px">
                    <thead style="background:#F9FAFB;font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em">
                        <tr>
                            <th style="padding:12px 16px;text-align:left;width:40px">#</th>
                            <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="uSortBy('nama')">
                                Nama <i class="bi" :class="uSortIcon('nama')"></i>
                            </th>
                            <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="uSortBy('tanggal')">
                                Tanggal <i class="bi" :class="uSortIcon('tanggal')"></i>
                            </th>
                            <th style="padding:12px 16px;text-align:left" class="hidden sm:table-cell">Jam</th>
                            <th style="padding:12px 16px;text-align:left">Status</th>
                            <th style="padding:12px 16px;text-align:left" class="hidden md:table-cell">Ubudiyah</th>
                            <th style="padding:12px 16px;text-align:left" class="hidden lg:table-cell">Keterangan</th>
                            <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr x-show="loadingTable">
                            <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat ab-spin" style="margin-right:4px"></i> Memuat...</td>
                        </tr>
                        <tr x-show="!loadingTable && ubudiyahData.length === 0">
                            <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                        </tr>
                        <template x-for="(row, idx) in ubudiyahData" :key="row.id">
                            <tr class="hover:bg-primary-50/40" style="transition:background 0.15s">
                                <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="uPagination.from + idx"></td>
                                <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama"></td>
                                <td style="padding:12px 16px;color:#4B5563" x-text="row.tanggal_fmt"></td>
                                <td style="padding:12px 16px;color:#4B5563" class="hidden sm:table-cell" x-text="row.jam_fmt"></td>
                                <td style="padding:12px 16px">
                                    <span class="ab-badge"
                                          :style="{
                                              'green': 'background:#DCFCE7;color:#15803D',
                                              'amber': 'background:#FEF3C7;color:#B45309',
                                              'red': 'background:#FEE2E2;color:#B91C1C',
                                              'blue': 'background:#DBEAFE;color:#1D4ED8',
                                          }[row.status_color] || 'background:#F3F4F6;color:#374151'"
                                          x-text="row.status"></span>
                                </td>
                                <td style="padding:12px 16px" class="hidden md:table-cell">
                                    <span x-show="row.ubudiyah" class="ab-badge"
                                          :style="{
                                              'indigo': 'background:#E0E7FF;color:#4338CA',
                                              'amber': 'background:#FEF3C7;color:#B45309',
                                          }[row.ubudiyah_color] || 'background:#F3F4F6;color:#4B5563'"
                                          x-text="row.ubudiyah"></span>
                                    <span x-show="!row.ubudiyah" style="color:#9CA3AF">-</span>
                                </td>
                                <td style="padding:12px 16px;color:#6B7280;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="hidden lg:table-cell" x-text="row.keterangan || '-'"></td>
                                <td style="padding:12px 16px;text-align:center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button x-show="row.foto_url" @click="openPhoto(row.foto_url)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#3B82F6" title="Lihat Foto" aria-label="Lihat foto">
                                            <i class="bi bi-image"></i>
                                        </button>
                                        <button @click="openUbudiyahEdit(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#D97706" title="Edit" aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button @click="confirmDelete('ubudiyah', row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#EF4444" title="Hapus" aria-label="Hapus">
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
                <span x-show="uPagination.total > 0" x-text="'Menampilkan ' + uPagination.from + '–' + uPagination.to + ' dari ' + uPagination.total"></span>
                <span x-show="uPagination.total === 0">Tidak ada data</span>
                <div class="flex gap-1">
                    <button @click="loadUbudiyah(uPagination.current_page - 1)" :disabled="!uPagination.prev_page_url"
                            aria-label="Halaman sebelumnya"
                            style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                            :style="!uPagination.prev_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button @click="loadUbudiyah(uPagination.current_page + 1)" :disabled="!uPagination.next_page_url"
                            aria-label="Halaman berikutnya"
                            style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                            :style="!uPagination.next_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== TAB: HARIAN CS ========== --}}
    <div x-show="activeTab === 'harian'" x-cloak>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-xl border p-3 flex flex-wrap items-center gap-2 mb-4">
            <div class="relative flex-1" style="min-width:160px">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:13px"></i>
                <input type="text" x-model="hFilter.search" @input.debounce.500ms="loadHarian(1)" class="crud-field-input" style="padding-left:36px" placeholder="Cari nama CS, lokasi...">
            </div>
            <select x-model="hFilter.tipe_absen" @change="loadHarian(1)" class="crud-field-input" style="width:auto;font-size:12px">
                <option value="">Semua Tipe</option>
                <option value="Masuk">Masuk</option>
                <option value="Pulang">Pulang</option>
            </select>
            <select x-model="hFilter.status_kehadiran" @change="loadHarian(1)" class="crud-field-input" style="width:auto;font-size:12px">
                <option value="">Semua Status</option>
                <template x-for="s in options.status_kehadiran" :key="s">
                    <option :value="s" x-text="s"></option>
                </template>
            </select>
            <input type="date" x-model="hFilter.tanggal_dari" @change="loadHarian(1)" class="crud-field-input" style="width:auto;font-size:12px" title="Dari tanggal">
            <input type="date" x-model="hFilter.tanggal_sampai" @change="loadHarian(1)" class="crud-field-input" style="width:auto;font-size:12px" title="Sampai tanggal">
            <button @click="clearHFilter()" style="font-size:12px;color:#6B7280;cursor:pointer;border:none;background:none" title="Reset" aria-label="Reset filter"><i class="bi bi-x-circle"></i></button>
        </div>

        {{-- Harian CS Table --}}
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" style="font-size:14px">
                    <thead style="background:#F9FAFB;font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em">
                        <tr>
                            <th style="padding:12px 16px;text-align:left;width:40px">#</th>
                            <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="hSortBy('nama_cs')">
                                Nama CS <i class="bi" :class="hSortIcon('nama_cs')"></i>
                            </th>
                            <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="hSortBy('tanggal')">
                                Tanggal <i class="bi" :class="hSortIcon('tanggal')"></i>
                            </th>
                            <th style="padding:12px 16px;text-align:left" class="hidden sm:table-cell">Jam</th>
                            <th style="padding:12px 16px;text-align:left;cursor:pointer" @click="hSortBy('tipe_absen')">
                                Tipe <i class="bi" :class="hSortIcon('tipe_absen')"></i>
                            </th>
                            <th style="padding:12px 16px;text-align:left" class="hidden md:table-cell">Status</th>
                            <th style="padding:12px 16px;text-align:left" class="hidden lg:table-cell">Lokasi</th>
                            <th style="padding:12px 16px;text-align:center;width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr x-show="loadingTable">
                            <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-arrow-repeat ab-spin" style="margin-right:4px"></i> Memuat...</td>
                        </tr>
                        <tr x-show="!loadingTable && harianData.length === 0">
                            <td colspan="8" style="padding:40px 16px;text-align:center;color:#9CA3AF"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px"></i> Tidak ada data</td>
                        </tr>
                        <template x-for="(row, idx) in harianData" :key="row.id">
                            <tr class="hover:bg-primary-50/40" style="transition:background 0.15s">
                                <td style="padding:12px 16px;color:#9CA3AF;font-size:12px" x-text="hPagination.from + idx"></td>
                                <td style="padding:12px 16px;font-weight:500;color:#1F2937" x-text="row.nama_cs"></td>
                                <td style="padding:12px 16px;color:#4B5563" x-text="row.tanggal_fmt"></td>
                                <td style="padding:12px 16px;color:#4B5563" class="hidden sm:table-cell" x-text="row.jam_fmt"></td>
                                <td style="padding:12px 16px">
                                    <span class="ab-badge"
                                          :style="row.tipe_color === 'green' ? 'background:#DCFCE7;color:#15803D' : 'background:#FFEDD5;color:#C2410C'"
                                          x-text="row.tipe_absen"></span>
                                </td>
                                <td style="padding:12px 16px;font-size:12px" class="hidden md:table-cell" x-text="row.status_label"></td>
                                <td style="padding:12px 16px;color:#6B7280;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="hidden lg:table-cell" x-text="row.lokasi || '-'"></td>
                                <td style="padding:12px 16px;text-align:center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button x-show="row.foto_url" @click="openPhoto(row.foto_url)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#3B82F6" title="Lihat Foto" aria-label="Lihat foto">
                                            <i class="bi bi-image"></i>
                                        </button>
                                        <button @click="openHarianEdit(row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#D97706" title="Edit" aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button @click="confirmDelete('harian', row.id)" style="padding:6px;border-radius:8px;border:none;background:none;cursor:pointer;color:#EF4444" title="Hapus" aria-label="Hapus">
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
                <span x-show="hPagination.total > 0" x-text="'Menampilkan ' + hPagination.from + '–' + hPagination.to + ' dari ' + hPagination.total"></span>
                <span x-show="hPagination.total === 0">Tidak ada data</span>
                <div class="flex gap-1">
                    <button @click="loadHarian(hPagination.current_page - 1)" :disabled="!hPagination.prev_page_url"
                            aria-label="Halaman sebelumnya"
                            style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                            :style="!hPagination.prev_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button @click="loadHarian(hPagination.current_page + 1)" :disabled="!hPagination.next_page_url"
                            aria-label="Halaman berikutnya"
                            style="padding:4px 10px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;background:white;cursor:pointer"
                            :style="!hPagination.next_page_url ? 'opacity:0.4;cursor:not-allowed' : ''">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== ALPINE.JS APP ===== --}}
@push('scripts')
<script>
function absensiApp() {
    return {
        loading: true,
        loadingTable: false,
        saving: false,
        activeTab: 'ubudiyah',
        statDate: new Date().toISOString().split('T')[0],

        showUbudiyahModal: false,
        showHarianModal: false,
        showDeleteModal: false,
        showPhotoModal: false,
        photoUrl: '',

        editingUbudiyahId: null,
        editingHarianId: null,
        deleteType: null,
        deleteId: null,
        formErrors: {},

        stats: {},
        options: { ubudiyah_names: [], cs_names: [], status_kehadiran: [] },

        ubudiyahData: [],
        uPagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        uFilter: { search: '', status: '', ubudiyah: '', tanggal_dari: '', tanggal_sampai: '', sort: 'tanggal', order: 'desc' },

        harianData: [],
        hPagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        hFilter: { search: '', tipe_absen: '', status_kehadiran: '', tanggal_dari: '', tanggal_sampai: '', sort: 'tanggal', order: 'desc' },

        ubudiyahForm: { nama: '', tanggal: '', jam: '', status: '', ubudiyah: '', keterangan: '', alamat: '' },
        harianForm: { nama_cs: '', tanggal: '', jam: '', tipe_absen: '', status_kehadiran: '', lokasi: '', keterangan: '' },

        async init() {
            try {
                await Promise.all([this.loadStats(), this.loadOptions(), this.loadUbudiyah(1)]);
            } catch (e) { console.error('Init error', e); }
            this.loading = false;
        },

        switchTab: async function(tab) {
            if (this.activeTab === tab) return;
            this.activeTab = tab;
            this.loadingTable = true;
            if (tab === 'ubudiyah' && this.ubudiyahData.length === 0) {
                await this.loadUbudiyah(1);
            } else if (tab === 'harian' && this.harianData.length === 0) {
                await this.loadHarian(1);
            }
            this.loadingTable = false;
        },

        loadStats: async function() {
            try {
                var res = await fetch('/api/absensi/stats?tanggal=' + encodeURIComponent(this.statDate), { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.stats = await res.json();
            } catch (e) { console.error('Stats error', e); }
        },

        loadOptions: async function() {
            try {
                var res = await fetch('/api/absensi/options', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.options = await res.json();
            } catch (e) { console.error('Options error', e); }
        },

        loadUbudiyah: async function(page) {
            if (page === undefined) page = 1;
            if (page < 1) return;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.uFilter.search);
                qs += '&status=' + encodeURIComponent(this.uFilter.status);
                qs += '&ubudiyah=' + encodeURIComponent(this.uFilter.ubudiyah);
                qs += '&tanggal_dari=' + encodeURIComponent(this.uFilter.tanggal_dari);
                qs += '&tanggal_sampai=' + encodeURIComponent(this.uFilter.tanggal_sampai);
                qs += '&sort=' + encodeURIComponent(this.uFilter.sort);
                qs += '&order=' + encodeURIComponent(this.uFilter.order);

                var res = await fetch('/api/absensi/ubudiyah/list?' + qs, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) { this.loadingTable = false; return; }
                var json = await res.json();
                this.ubudiyahData = json.data || [];
                this.uPagination = {
                    current_page: json.current_page || 1, last_page: json.last_page || 1,
                    from: json.from || 0, to: json.to || 0, total: json.total || 0,
                    prev_page_url: json.prev_page_url, next_page_url: json.next_page_url,
                };
            } catch (e) { console.error('Ubudiyah list error', e); }
            this.loadingTable = false;
        },

        loadHarian: async function(page) {
            if (page === undefined) page = 1;
            if (page < 1) return;
            this.loadingTable = true;
            try {
                var qs = 'page=' + page + '&per_page=20';
                qs += '&search=' + encodeURIComponent(this.hFilter.search);
                qs += '&tipe_absen=' + encodeURIComponent(this.hFilter.tipe_absen);
                qs += '&status_kehadiran=' + encodeURIComponent(this.hFilter.status_kehadiran);
                qs += '&tanggal_dari=' + encodeURIComponent(this.hFilter.tanggal_dari);
                qs += '&tanggal_sampai=' + encodeURIComponent(this.hFilter.tanggal_sampai);
                qs += '&sort=' + encodeURIComponent(this.hFilter.sort);
                qs += '&order=' + encodeURIComponent(this.hFilter.order);

                var res = await fetch('/api/absensi/harian/list?' + qs, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) { this.loadingTable = false; return; }
                var json = await res.json();
                this.harianData = json.data || [];
                this.hPagination = {
                    current_page: json.current_page || 1, last_page: json.last_page || 1,
                    from: json.from || 0, to: json.to || 0, total: json.total || 0,
                    prev_page_url: json.prev_page_url, next_page_url: json.next_page_url,
                };
            } catch (e) { console.error('Harian list error', e); }
            this.loadingTable = false;
        },

        clearUFilter: function() {
            this.uFilter = { search: '', status: '', ubudiyah: '', tanggal_dari: '', tanggal_sampai: '', sort: 'tanggal', order: 'desc' };
            this.loadUbudiyah(1);
        },
        uSortBy: function(col) {
            if (this.uFilter.sort === col) { this.uFilter.order = this.uFilter.order === 'asc' ? 'desc' : 'asc'; }
            else { this.uFilter.sort = col; this.uFilter.order = 'asc'; }
            this.loadUbudiyah(1);
        },
        uSortIcon: function(col) {
            if (this.uFilter.sort !== col) return 'bi-chevron-expand';
            return this.uFilter.order === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        clearHFilter: function() {
            this.hFilter = { search: '', tipe_absen: '', status_kehadiran: '', tanggal_dari: '', tanggal_sampai: '', sort: 'tanggal', order: 'desc' };
            this.loadHarian(1);
        },
        hSortBy: function(col) {
            if (this.hFilter.sort === col) { this.hFilter.order = this.hFilter.order === 'asc' ? 'desc' : 'asc'; }
            else { this.hFilter.sort = col; this.hFilter.order = 'asc'; }
            this.loadHarian(1);
        },
        hSortIcon: function(col) {
            if (this.hFilter.sort !== col) return 'bi-chevron-expand';
            return this.hFilter.order === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        // UBUDIYAH CRUD
        resetUbudiyahForm: function() {
            this.ubudiyahForm = { nama: '', tanggal: '', jam: '', status: '', ubudiyah: '', keterangan: '', alamat: '' };
            this.formErrors = {};
        },
        openUbudiyahCreate: function() {
            this.editingUbudiyahId = null;
            this.resetUbudiyahForm();
            this.ubudiyahForm.tanggal = new Date().toISOString().split('T')[0];
            this.ubudiyahForm.jam = new Date().toTimeString().substring(0, 5);
            this.showUbudiyahModal = true;
        },
        openUbudiyahEdit: async function(id) {
            this.editingUbudiyahId = id;
            this.resetUbudiyahForm();
            this.showUbudiyahModal = true;
            try {
                var res = await fetch('/api/absensi/ubudiyah/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.ubudiyahForm = {
                    nama: data.nama || '',
                    tanggal: data.tanggal ? data.tanggal.substring(0, 10) : '',
                    jam: data.jam ? data.jam.substring(0, 5) : '',
                    status: data.status || '',
                    ubudiyah: data.ubudiyah || '',
                    keterangan: data.keterangan || '',
                    alamat: data.alamat || '',
                };
            } catch (e) {
                this.showToast('Gagal memuat data', 'error');
                this.showUbudiyahModal = false;
            }
        },
        closeUbudiyahModal: function() { this.showUbudiyahModal = false; this.editingUbudiyahId = null; this.resetUbudiyahForm(); },
        submitUbudiyah: async function() {
            this.saving = true;
            this.formErrors = {};
            try {
                var url = this.editingUbudiyahId ? '/api/absensi/ubudiyah/' + this.editingUbudiyahId : '/api/absensi/ubudiyah';
                var method = this.editingUbudiyahId ? 'PUT' : 'POST';
                var csrfEl = document.querySelector('meta[name="csrf-token"]');
                var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                if (csrfEl) headers['X-CSRF-TOKEN'] = csrfEl.content;
                var res = await fetch(url, { method: method, headers: headers, body: JSON.stringify(this.ubudiyahForm) });
                var json = await res.json();
                if (!res.ok) { if (json.errors) this.formErrors = json.errors; this.showToast(json.message || 'Validasi gagal', 'error'); this.saving = false; return; }
                this.showToast(json.message, 'success');
                this.closeUbudiyahModal();
                await Promise.all([this.loadStats(), this.loadUbudiyah(this.uPagination.current_page)]);
            } catch (e) { this.showToast('Terjadi kesalahan', 'error'); }
            this.saving = false;
        },

        // HARIAN CRUD
        resetHarianForm: function() {
            this.harianForm = { nama_cs: '', tanggal: '', jam: '', tipe_absen: '', status_kehadiran: '', lokasi: '', keterangan: '' };
            this.formErrors = {};
        },
        openHarianCreate: function() {
            this.editingHarianId = null;
            this.resetHarianForm();
            this.harianForm.tanggal = new Date().toISOString().split('T')[0];
            this.harianForm.jam = new Date().toTimeString().substring(0, 5);
            this.showHarianModal = true;
        },
        openHarianEdit: async function(id) {
            this.editingHarianId = id;
            this.resetHarianForm();
            this.showHarianModal = true;
            try {
                var res = await fetch('/api/absensi/harian/' + id, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                this.harianForm = {
                    nama_cs: data.nama_cs || '',
                    tanggal: data.tanggal ? data.tanggal.substring(0, 10) : '',
                    jam: data.jam ? data.jam.substring(0, 5) : '',
                    tipe_absen: data.tipe_absen || '',
                    status_kehadiran: data.status_kehadiran || '',
                    lokasi: data.lokasi || '',
                    keterangan: data.keterangan || '',
                };
            } catch (e) {
                this.showToast('Gagal memuat data', 'error');
                this.showHarianModal = false;
            }
        },
        closeHarianModal: function() { this.showHarianModal = false; this.editingHarianId = null; this.resetHarianForm(); },
        submitHarian: async function() {
            this.saving = true;
            this.formErrors = {};
            try {
                var url = this.editingHarianId ? '/api/absensi/harian/' + this.editingHarianId : '/api/absensi/harian';
                var method = this.editingHarianId ? 'PUT' : 'POST';
                var csrfEl = document.querySelector('meta[name="csrf-token"]');
                var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                if (csrfEl) headers['X-CSRF-TOKEN'] = csrfEl.content;
                var res = await fetch(url, { method: method, headers: headers, body: JSON.stringify(this.harianForm) });
                var json = await res.json();
                if (!res.ok) { if (json.errors) this.formErrors = json.errors; this.showToast(json.message || 'Validasi gagal', 'error'); this.saving = false; return; }
                this.showToast(json.message, 'success');
                this.closeHarianModal();
                await Promise.all([this.loadStats(), this.loadHarian(this.hPagination.current_page)]);
            } catch (e) { this.showToast('Terjadi kesalahan', 'error'); }
            this.saving = false;
        },

        // DELETE
        confirmDelete: function(type, id) {
            this.deleteType = type;
            this.deleteId = id;
            this.showDeleteModal = true;
        },
        executeDelete: async function() {
            this.saving = true;
            try {
                var url = this.deleteType === 'ubudiyah'
                    ? '/api/absensi/ubudiyah/' + this.deleteId
                    : '/api/absensi/harian/' + this.deleteId;
                var csrfEl = document.querySelector('meta[name="csrf-token"]');
                var headers = { 'Accept': 'application/json' };
                if (csrfEl) headers['X-CSRF-TOKEN'] = csrfEl.content;
                var res = await fetch(url, { method: 'DELETE', headers: headers });
                var json = await res.json();
                this.showToast(json.message, 'success');
                this.showDeleteModal = false;
                await this.loadStats();
                if (this.deleteType === 'ubudiyah') await this.loadUbudiyah(this.uPagination.current_page);
                else await this.loadHarian(this.hPagination.current_page);
            } catch (e) { this.showToast('Gagal menghapus', 'error'); }
            this.saving = false;
        },

        // PHOTO
        openPhoto: function(url) {
            this.photoUrl = url;
            this.showPhotoModal = true;
        },

        // TOAST
        showToast: function(message, type) {
            if (!type) type = 'success';
            var bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#059669';
            var icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'x-circle-fill' : 'info-circle-fill';
            var toast = document.createElement('div');
            toast.innerHTML = '<i class="bi bi-' + icon + '"></i> ' + message;
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:ab-fadeInUp .3s ease;max-width:90vw';
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        },
    };
}
</script>
@endpush

</x-layouts.app>
