<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\KwitansiV2Controller;
use App\Http\Controllers\AnalisisDonaturController;
use App\Http\Controllers\DonaturCrmController;
use App\Http\Controllers\LaporanPerolehanCrmController;
use App\Http\Controllers\MonitorCsController;
use App\Http\Controllers\PerformaCsController;
use App\Http\Controllers\DonasiWebController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\InputLaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KwitansiCrmController;
use App\Http\Controllers\LaporanRamadhanCrmController;
use App\Http\Controllers\DataPegawaiCrmController;
use App\Http\Controllers\AbsensiCrmController;
use App\Http\Controllers\PartnershipCrmController;
use App\Http\Controllers\PenyebaranTokoCrmController;
use App\Http\Controllers\ManajemenUserCrmController;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/kwitansiv2/{id}/pdf', [KwitansiV2Controller::class, 'generatePDF'])->name('kwitansiv2.pdf');
Route::get('/kwitansi/{id}/pdf', [KwitansiController::class, 'generatePDF'])->name('kwitansi.pdf');

// Protected Routes with auth middleware
Route::middleware(['auth'])->group(function () {
    
    // Dashboard = Analisis Donatur
    Route::get('/dashboard', function() { return redirect()->route('analisis-donatur'); })->name('dashboard');
    
    // Coming Soon Page
    Route::get('/coming-soon/{menu?}', function($menu = 'Fitur') {
        return view('coming-soon', ['menu' => $menu]);
    })->name('coming-soon');
    
    // Analisis Donatur (Main Dashboard)
    Route::get('/analisis-donatur', [AnalisisDonaturController::class, 'index'])->name('analisis-donatur');
    Route::get('/analisis-donatur/export', [AnalisisDonaturController::class, 'export'])->name('analisis-donatur.export');
    Route::get('/analisis-donatur/export-pdf', [AnalisisDonaturController::class, 'exportPdf'])->name('analisis-donatur.export-pdf');
    Route::get('/analisis-donatur/chart-data', [AnalisisDonaturController::class, 'chartData'])->name('analisis-donatur.chart-data');
    Route::get('/analisis-donatur/donatur-list', [AnalisisDonaturController::class, 'donaturList'])->name('analisis-donatur.donatur-list');

    // ============================================
    // DONATUR CRM ROUTES
    // ============================================
    
    // Main Views
    Route::get('/donatur', [DonaturCrmController::class, 'index'])->name('donatur.index');
    Route::get('/donatur/create', [DonaturCrmController::class, 'create'])->name('donatur.create');
    Route::get('/donatur/{id}', [DonaturCrmController::class, 'show'])->name('donatur.show');
    Route::get('/donatur/{id}/edit', [DonaturCrmController::class, 'edit'])->name('donatur.edit');
    
    // API Endpoints untuk Donatur CRM
    Route::prefix('api/donatur')->name('donatur.api.')->group(function () {
        // CRUD API
        Route::get('/', [DonaturCrmController::class, 'apiIndex'])->name('index');
        Route::post('/', [DonaturCrmController::class, 'store'])->name('store');
        Route::get('/{id}', [DonaturCrmController::class, 'apiShow'])->name('show');
        Route::put('/{id}', [DonaturCrmController::class, 'update'])->name('update');
        Route::delete('/{id}', [DonaturCrmController::class, 'destroy'])->name('destroy');
        
        // CRM Stats & Segments
        Route::get('/crm/stats', [DonaturCrmController::class, 'stats'])->name('stats');
        Route::get('/crm/segments', [DonaturCrmController::class, 'segments'])->name('segments');
        Route::get('/crm/follow-up-tasks', [DonaturCrmController::class, 'followUpTasks'])->name('follow-up-tasks');
        Route::get('/crm/alerts', [DonaturCrmController::class, 'alerts'])->name('alerts');
        
        // Donatur History & Notes
        Route::get('/{id}/history', [DonaturCrmController::class, 'history'])->name('history');
        Route::get('/{id}/notes', [DonaturCrmController::class, 'getNotes'])->name('notes');
        Route::post('/{id}/note', [DonaturCrmController::class, 'addNote'])->name('add-note');
        Route::get('/{id}/activity-logs', [DonaturCrmController::class, 'getActivityLogs'])->name('activity-logs');
        
        // Utility
        Route::get('/check-phone/{phone}', [DonaturCrmController::class, 'checkPhone'])->name('check-phone');
        
        // Bulk Actions
        Route::post('/bulk/delete', [DonaturCrmController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk/assign', [DonaturCrmController::class, 'bulkAssign'])->name('bulk-assign');
        
        // Export
        Route::get('/export/excel', [DonaturCrmController::class, 'export'])->name('export');
    });
    
    // ============================================
    // LAPORAN PEROLEHAN CRM ROUTES
    // ============================================
    
    // Main View
    Route::get('/laporan-perolehan', [LaporanPerolehanCrmController::class, 'index'])->name('laporan-perolehan.index');
    
    // API Endpoints untuk Laporan Perolehan CRM
    Route::prefix('api/laporan-perolehan')->name('laporan-perolehan.api.')->group(function () {
        Route::get('/today-stats', [LaporanPerolehanCrmController::class, 'apiTodayStats'])->name('today-stats');
        Route::get('/hourly-breakdown', [LaporanPerolehanCrmController::class, 'apiHourlyBreakdown'])->name('hourly-breakdown');
        Route::get('/cs-leaderboard', [LaporanPerolehanCrmController::class, 'apiCsLeaderboard'])->name('cs-leaderboard');
        Route::get('/source-breakdown', [LaporanPerolehanCrmController::class, 'apiSourceBreakdown'])->name('source-breakdown');
        Route::get('/team-breakdown', [LaporanPerolehanCrmController::class, 'apiTeamBreakdown'])->name('team-breakdown');
        Route::get('/trend-comparison', [LaporanPerolehanCrmController::class, 'apiTrendComparison'])->name('trend-comparison');
        Route::get('/live-feed', [LaporanPerolehanCrmController::class, 'apiLiveFeed'])->name('live-feed');
        Route::get('/export', [LaporanPerolehanCrmController::class, 'apiExport'])->name('export');
    });
    
    // ============================================
    // CUSTOMER SERVICE MANAGEMENT ROUTES
    // ============================================
    
    // Daftar CS
    Route::get('/daftar-cs', [CustomerServiceController::class, 'daftarIndex'])->name('daftar-cs.index');
    
    // Performa Bulanan
    Route::get('/performa-bulanan', [CustomerServiceController::class, 'performaBulananIndex'])->name('performa-bulanan.index');
    
    // API Endpoints untuk Customer Service
    Route::prefix('api/customer-service')->name('customer-service.api.')->group(function () {
        // Daftar CS APIs
        Route::get('/cs-list', [CustomerServiceController::class, 'apiCsList'])->name('cs-list');
        Route::get('/overview-stats', [CustomerServiceController::class, 'apiOverviewStats'])->name('overview-stats');
        Route::get('/cs-detail', [CustomerServiceController::class, 'apiCsDetail'])->name('cs-detail');
        Route::post('/store', [CustomerServiceController::class, 'apiStore'])->name('store');
        Route::put('/{id}/update', [CustomerServiceController::class, 'apiUpdate'])->name('update');
        Route::delete('/{id}/delete', [CustomerServiceController::class, 'apiDelete'])->name('delete');
        
        // Performa Bulanan APIs
        Route::get('/monthly-pivot', [CustomerServiceController::class, 'apiMonthlyPivot'])->name('monthly-pivot');
        Route::get('/monthly-trend', [CustomerServiceController::class, 'apiMonthlyTrend'])->name('monthly-trend');
        Route::get('/top-performers', [CustomerServiceController::class, 'apiTopPerformers'])->name('top-performers');
        Route::get('/export-monthly', [CustomerServiceController::class, 'apiExportMonthly'])->name('export-monthly');
    });

    // ============================================
    // INPUT LAPORAN ROUTES
    // ============================================
    
    // Main View
    Route::get('/input-laporan', [InputLaporanController::class, 'index'])->name('input-laporan.index');
    
    // API Endpoints untuk Input Laporan
    Route::prefix('api/input-laporan')->name('input-laporan.api.')->group(function () {
        Route::get('/list', [InputLaporanController::class, 'apiList'])->name('list');
        Route::get('/options', [InputLaporanController::class, 'apiOptions'])->name('options');
        Route::get('/stats', [InputLaporanController::class, 'apiStats'])->name('stats');
        Route::get('/lookup-donatur', [InputLaporanController::class, 'apiLookupDonatur'])->name('lookup-donatur');
        Route::post('/', [InputLaporanController::class, 'apiStore'])->name('store');
        Route::get('/{id}', [InputLaporanController::class, 'apiShow'])->name('show');
        Route::put('/{id}', [InputLaporanController::class, 'apiUpdate'])->name('update');
        Route::delete('/{id}', [InputLaporanController::class, 'apiDelete'])->name('delete');
        Route::post('/bulk-delete', [InputLaporanController::class, 'apiBulkDelete'])->name('bulk-delete');
    });

    // ============================================
    // MONITOR CS ROUTES
    // ============================================
    
    // Main View
    Route::get('/monitor-cs', [MonitorCsController::class, 'index'])->name('monitor-cs.index');
    
    // API Endpoints untuk Monitor CS
    Route::prefix('api/monitor-cs')->name('monitor-cs.api.')->group(function () {
        Route::get('/cs-status-summary', [MonitorCsController::class, 'apiCsStatusSummary'])->name('cs-status-summary');
        Route::get('/cs-list-status', [MonitorCsController::class, 'apiCsListStatus'])->name('cs-list-status');
        Route::get('/activity-timeline', [MonitorCsController::class, 'apiActivityTimeline'])->name('activity-timeline');
        Route::get('/cs-detail', [MonitorCsController::class, 'apiCsDetail'])->name('cs-detail');
    });

    // ============================================
    // PERFORMA CS ROUTES
    // ============================================
    
    // Main View
    Route::get('/performa-cs', [PerformaCsController::class, 'index'])->name('performa-cs.index');
    
    // API Endpoints untuk Performa CS
    Route::prefix('api/performa-cs')->name('performa-cs.api.')->group(function () {
        Route::get('/overview-summary', [PerformaCsController::class, 'apiOverviewSummary'])->name('overview-summary');
        Route::get('/h2h-comparison', [PerformaCsController::class, 'apiH2hComparison'])->name('h2h-comparison');
        Route::get('/leaderboard', [PerformaCsController::class, 'apiLeaderboard'])->name('leaderboard');
        Route::get('/cs-list', [PerformaCsController::class, 'apiCsList'])->name('cs-list');
        Route::get('/cs-detail', [PerformaCsController::class, 'apiCsDetail'])->name('cs-detail');
        Route::get('/insights-alerts', [PerformaCsController::class, 'apiInsightsAlerts'])->name('insights-alerts');
        Route::get('/chart-data', [PerformaCsController::class, 'apiChartData'])->name('chart-data');
        Route::get('/export', [PerformaCsController::class, 'apiExport'])->name('export');
    });

    // ============================================
    // KWITANSI V1 CRM ROUTES
    // ============================================
    
    // Main View
    Route::get('/kwitansi-v1', [KwitansiCrmController::class, 'index'])->name('kwitansi-v1.index');
    
    // API Endpoints untuk Kwitansi v1
    Route::prefix('api/kwitansi-v1')->name('kwitansi-v1.api.')->group(function () {
        Route::get('/list', [KwitansiCrmController::class, 'apiList'])->name('list');
        Route::get('/stats', [KwitansiCrmController::class, 'apiStats'])->name('stats');
        Route::post('/', [KwitansiCrmController::class, 'apiStore'])->name('store');
        Route::get('/{id}', [KwitansiCrmController::class, 'apiShow'])->name('show');
        Route::put('/{id}', [KwitansiCrmController::class, 'apiUpdate'])->name('update');
        Route::delete('/{id}', [KwitansiCrmController::class, 'apiDelete'])->name('delete');
        Route::post('/bulk-delete', [KwitansiCrmController::class, 'apiBulkDelete'])->name('bulk-delete');
    });

    // ============================================
    // LAPORAN RAMADHAN ROUTES
    // ============================================
    
    // Main View
    Route::get('/laporan-ramadhan', [LaporanRamadhanCrmController::class, 'index'])->name('laporan-ramadhan.index');
    
    // API Endpoints untuk Laporan Ramadhan
    Route::prefix('api/laporan-ramadhan')->name('laporan-ramadhan.api.')->group(function () {
        Route::get('/periods', [LaporanRamadhanCrmController::class, 'apiPeriods'])->name('periods');
        Route::post('/periods', [LaporanRamadhanCrmController::class, 'apiStorePeriod'])->name('periods.store');
        Route::put('/periods/{id}', [LaporanRamadhanCrmController::class, 'apiUpdatePeriod'])->name('periods.update');
        Route::delete('/periods/{id}', [LaporanRamadhanCrmController::class, 'apiDeletePeriod'])->name('periods.delete');
        Route::get('/stats', [LaporanRamadhanCrmController::class, 'apiStats'])->name('stats');
        Route::get('/perbandingan-cs', [LaporanRamadhanCrmController::class, 'apiPerbandinganCS'])->name('perbandingan-cs');
        Route::get('/perbandingan-tim', [LaporanRamadhanCrmController::class, 'apiPerbandinganTim'])->name('perbandingan-tim');
        Route::get('/trend-harian', [LaporanRamadhanCrmController::class, 'apiTrendHarian'])->name('trend-harian');
        Route::get('/options', [LaporanRamadhanCrmController::class, 'apiOptions'])->name('options');
    });

    // ============================================
    // DONASI WEBSITE ROUTES (Second Database)
    // ============================================
    
    // Main View
    Route::get('/donasi-web', [DonasiWebController::class, 'index'])->name('donasi-web.index');
    
    // API Endpoints untuk Donasi Website Analytics
    Route::prefix('api/donasi-web')->name('donasi-web.api.')->group(function () {
        Route::get('/overview-stats', [DonasiWebController::class, 'apiOverviewStats'])->name('overview-stats');
        Route::get('/trend-data', [DonasiWebController::class, 'apiTrendData'])->name('trend-data');
        Route::get('/campaign-breakdown', [DonasiWebController::class, 'apiCampaignBreakdown'])->name('campaign-breakdown');
        Route::get('/payment-analytics', [DonasiWebController::class, 'apiPaymentAnalytics'])->name('payment-analytics');
        Route::get('/donor-insights', [DonasiWebController::class, 'apiDonorInsights'])->name('donor-insights');
        Route::get('/traffic-utm', [DonasiWebController::class, 'apiTrafficUtm'])->name('traffic-utm');
        Route::get('/program-packages', [DonasiWebController::class, 'apiProgramPackages'])->name('program-packages');
        Route::get('/time-patterns', [DonasiWebController::class, 'apiTimePatterns'])->name('time-patterns');
        Route::get('/diagnostic', [DonasiWebController::class, 'apiDiagnostic'])->name('diagnostic');
    });

    // ============================================
    // DATA PEGAWAI ROUTES (HRD)
    // ============================================

    // Main View
    Route::get('/data-pegawai', [DataPegawaiCrmController::class, 'index'])->name('data-pegawai.index');

    // API Endpoints
    Route::prefix('api/data-pegawai')->name('data-pegawai.api.')->group(function () {
        Route::get('/list', [DataPegawaiCrmController::class, 'apiList'])->name('list');
        Route::get('/stats', [DataPegawaiCrmController::class, 'apiStats'])->name('stats');
        Route::get('/jabatan-options', [DataPegawaiCrmController::class, 'apiJabatanOptions'])->name('jabatan-options');
        Route::get('/{id}', [DataPegawaiCrmController::class, 'apiShow'])->where('id', '[0-9]+')->name('show');
        Route::post('/', [DataPegawaiCrmController::class, 'apiStore'])->name('store');
        Route::put('/{id}', [DataPegawaiCrmController::class, 'apiUpdate'])->where('id', '[0-9]+')->name('update');
        Route::delete('/{id}', [DataPegawaiCrmController::class, 'apiDelete'])->where('id', '[0-9]+')->name('delete');
    });

    // ============================================
    // ABSENSI ROUTES (HRD)
    // ============================================

    // Main View
    Route::get('/absensi', [AbsensiCrmController::class, 'index'])->name('absensi.index');

    // API Endpoints
    Route::prefix('api/absensi')->name('absensi.api.')->group(function () {
        Route::get('/stats', [AbsensiCrmController::class, 'apiStats'])->name('stats');
        Route::get('/options', [AbsensiCrmController::class, 'apiOptions'])->name('options');

        // Ubudiyah sub-routes
        Route::get('/ubudiyah/list', [AbsensiCrmController::class, 'apiUbudiyahList'])->name('ubudiyah.list');
        Route::get('/ubudiyah/{id}', [AbsensiCrmController::class, 'apiUbudiyahShow'])->name('ubudiyah.show');
        Route::post('/ubudiyah', [AbsensiCrmController::class, 'apiUbudiyahStore'])->name('ubudiyah.store');
        Route::put('/ubudiyah/{id}', [AbsensiCrmController::class, 'apiUbudiyahUpdate'])->name('ubudiyah.update');
        Route::delete('/ubudiyah/{id}', [AbsensiCrmController::class, 'apiUbudiyahDelete'])->name('ubudiyah.delete');

        // Harian CS sub-routes
        Route::get('/harian/list', [AbsensiCrmController::class, 'apiHarianList'])->name('harian.list');
        Route::get('/harian/{id}', [AbsensiCrmController::class, 'apiHarianShow'])->name('harian.show');
        Route::post('/harian', [AbsensiCrmController::class, 'apiHarianStore'])->name('harian.store');
        Route::put('/harian/{id}', [AbsensiCrmController::class, 'apiHarianUpdate'])->name('harian.update');
        Route::delete('/harian/{id}', [AbsensiCrmController::class, 'apiHarianDelete'])->name('harian.delete');
    });

    // ============================================
    // PARTNERSHIP ROUTES
    // ============================================

    // Main View
    Route::get('/partnership', [PartnershipCrmController::class, 'index'])->name('partnership.index');

    // API Endpoints
    Route::prefix('api/partnership')->name('partnership.api.')->group(function () {
        Route::get('/list', [PartnershipCrmController::class, 'apiList'])->name('list');
        Route::get('/stats', [PartnershipCrmController::class, 'apiStats'])->name('stats');
        Route::get('/{id}', [PartnershipCrmController::class, 'apiShow'])->where('id', '[0-9]+')->name('show');
        Route::post('/', [PartnershipCrmController::class, 'apiStore'])->name('store');
        Route::put('/{id}', [PartnershipCrmController::class, 'apiUpdate'])->where('id', '[0-9]+')->name('update');
        Route::delete('/{id}', [PartnershipCrmController::class, 'apiDelete'])->where('id', '[0-9]+')->name('delete');
    });

    // ============================================
    // PENYEBARAN TOKO ROUTES
    // ============================================

    // Main View
    Route::get('/penyebaran-toko', [PenyebaranTokoCrmController::class, 'index'])->name('penyebaran-toko.index');

    // API Endpoints
    Route::prefix('api/penyebaran-toko')->name('penyebaran-toko.api.')->group(function () {
        Route::get('/list', [PenyebaranTokoCrmController::class, 'apiList'])->name('list');
        Route::get('/stats', [PenyebaranTokoCrmController::class, 'apiStats'])->name('stats');
        Route::get('/map-data', [PenyebaranTokoCrmController::class, 'apiMapData'])->name('map-data');
        Route::get('/{id}', [PenyebaranTokoCrmController::class, 'apiShow'])->where('id', '[0-9]+')->name('show');
        Route::post('/', [PenyebaranTokoCrmController::class, 'apiStore'])->name('store');
        Route::put('/{id}', [PenyebaranTokoCrmController::class, 'apiUpdate'])->where('id', '[0-9]+')->name('update');
        Route::delete('/{id}', [PenyebaranTokoCrmController::class, 'apiDelete'])->where('id', '[0-9]+')->name('delete');
    });

    // ============================================
    // MANAJEMEN USER ROUTES
    // ============================================

    // Main View
    Route::get('/manajemen-user', [ManajemenUserCrmController::class, 'index'])->name('manajemen-user.index');

    // API Endpoints
    Route::prefix('api/manajemen-user')->name('manajemen-user.api.')->group(function () {
        Route::get('/list', [ManajemenUserCrmController::class, 'apiList'])->name('list');
        Route::get('/stats', [ManajemenUserCrmController::class, 'apiStats'])->name('stats');
        Route::get('/{id}', [ManajemenUserCrmController::class, 'apiShow'])->where('id', '[0-9]+')->name('show');
        Route::post('/', [ManajemenUserCrmController::class, 'apiStore'])->name('store');
        Route::put('/{id}', [ManajemenUserCrmController::class, 'apiUpdate'])->where('id', '[0-9]+')->name('update');
        Route::delete('/{id}', [ManajemenUserCrmController::class, 'apiDelete'])->where('id', '[0-9]+')->name('delete');
    });
});

// Route::get('/api/laporans', function () {
//     $laporans = DB::table('laporans')
//         ->select(
//             'tanggal',
//             'tim',
//             'nama_cs',
//             'perolehan_jam',
//             'jml_database',
//             'jml_perolehan',
//             'nama_bank',
//             'no_rek',
//             'did',
//             'nama_donatur',
//             'nama_toko',
//             'kode_negara',
//             'no_hp',
//             'followup_wa',
//             'hasil_dari',
//             'prg_cross_selling',
//             'adsense',
//             'e_commerce',
//             'program_utama',
//             'nama_produk',
//             'zakat',
//             'wakaf',
//             'nama_platform',
//             'jenis_konten',
//             'kat_donatur',
//             'jenis_kelamin',
//             'email',
//             'sosmed_account',
//             'alamat',
//             'program',
//             'channel',
//             'fundraiser',
//             'keterangan'
//         )
//         ->orderBy('tanggal', 'desc')
//         ->get()
//         ->map(fn($item) => collect($item)->map(fn($v) => $v ?? '')->all());

//     return response()->json([
//         'success' => true,
//         'data' => $laporans,
//         'total' => $laporans->count()
//     ]);
// });

