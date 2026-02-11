<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\KwitansiV2Controller;
use App\Http\Controllers\AnalisisDonaturController;
use App\Http\Controllers\DonaturCrmController;
use App\Http\Controllers\LaporanPerolehanCrmController;
use App\Http\Controllers\MonitorCsController;
use App\Http\Controllers\PerformaCsController;
use App\Http\Controllers\AuthController;
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

