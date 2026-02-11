<?php
// Test script untuk verifikasi data analisis donatur

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI DATA ANALISIS DONATUR ===\n\n";

// 1. Total donatur unik
$totalDonatur = DB::table('laporans')
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->distinct()
    ->count('no_hp');
echo "1. TOTAL DONATUR UNIK: $totalDonatur\n\n";

// 2. Top 5 Donatur (semua tahun)
echo "2. TOP 5 DONATUR (Semua Tahun):\n";
$top5 = DB::table('laporans')
    ->select('no_hp', DB::raw('MAX(nama_donatur) as nama_donatur'), DB::raw('SUM(jml_perolehan) as total_donasi'), DB::raw('COUNT(*) as jml_transaksi'))
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->groupBy('no_hp')
    ->orderByDesc('total_donasi')
    ->limit(5)
    ->get();
foreach ($top5 as $i => $d) {
    echo "   " . ($i+1) . ". {$d->nama_donatur} - Rp " . number_format($d->total_donasi) . " ({$d->jml_transaksi} trx)\n";
}

// 3. Top 5 Donatur 2025
echo "\n3. TOP 5 DONATUR TAHUN 2025:\n";
$top5_2025 = DB::table('laporans')
    ->select('no_hp', DB::raw('MAX(nama_donatur) as nama_donatur'), DB::raw('SUM(jml_perolehan) as total_donasi'), DB::raw('COUNT(*) as jml_transaksi'))
    ->whereYear('tanggal', 2025)
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->groupBy('no_hp')
    ->orderByDesc('total_donasi')
    ->limit(5)
    ->get();
$count2025 = DB::table('laporans')->whereYear('tanggal', 2025)->whereNotNull('no_hp')->where('no_hp', '!=', '')->distinct()->count('no_hp');
echo "   Total donatur 2025: $count2025\n";
foreach ($top5_2025 as $i => $d) {
    echo "   " . ($i+1) . ". {$d->nama_donatur} - Rp " . number_format($d->total_donasi) . " ({$d->jml_transaksi} trx)\n";
}

// 4. Donatur Baru 2025
echo "\n4. DONATUR BARU 2025 (tidak ada di 2024):\n";
$donatur2024 = DB::table('laporans')
    ->whereYear('tanggal', 2024)
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->distinct()
    ->pluck('no_hp');

$donaturBaru2025 = DB::table('laporans')
    ->select('no_hp', DB::raw('MAX(nama_donatur) as nama_donatur'), DB::raw('SUM(jml_perolehan) as total_donasi'), DB::raw('COUNT(*) as jml_transaksi'))
    ->whereYear('tanggal', 2025)
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->whereNotIn('no_hp', $donatur2024)
    ->groupBy('no_hp')
    ->orderByDesc('total_donasi')
    ->limit(5)
    ->get();
$countBaru = DB::table('laporans')->whereYear('tanggal', 2025)->whereNotNull('no_hp')->where('no_hp', '!=', '')->whereNotIn('no_hp', $donatur2024)->distinct()->count('no_hp');
echo "   Total donatur baru 2025: $countBaru\n";
foreach ($donaturBaru2025 as $i => $d) {
    echo "   " . ($i+1) . ". {$d->nama_donatur} - Rp " . number_format($d->total_donasi) . " ({$d->jml_transaksi} trx)\n";
}

// 5. Donatur Hilang (2024 tapi tidak di 2025)
echo "\n5. DONATUR HILANG (ada di 2024, tidak di 2025):\n";
$donatur2025 = DB::table('laporans')
    ->whereYear('tanggal', 2025)
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->distinct()
    ->pluck('no_hp');

$donaturHilang = DB::table('laporans')
    ->select('no_hp', DB::raw('MAX(nama_donatur) as nama_donatur'), DB::raw('SUM(jml_perolehan) as total_donasi'), DB::raw('COUNT(*) as jml_transaksi'), DB::raw('MAX(tanggal) as last_donation'))
    ->whereYear('tanggal', 2024)
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->whereNotIn('no_hp', $donatur2025)
    ->groupBy('no_hp')
    ->orderByDesc('total_donasi')
    ->limit(5)
    ->get();
$countHilang = DB::table('laporans')->whereYear('tanggal', 2024)->whereNotNull('no_hp')->where('no_hp', '!=', '')->whereNotIn('no_hp', $donatur2025)->distinct()->count('no_hp');
echo "   Total donatur hilang: $countHilang\n";
foreach ($donaturHilang as $i => $d) {
    echo "   " . ($i+1) . ". {$d->nama_donatur} - Rp " . number_format($d->total_donasi) . " (last: {$d->last_donation})\n";
}

// 6. Tidak Aktif 30 hari
echo "\n6. TIDAK AKTIF 30 HARI:\n";
$cutoff30 = now()->subDays(30)->format('Y-m-d');
$tidakAktif30 = DB::table('laporans')
    ->select('no_hp', DB::raw('MAX(nama_donatur) as nama_donatur'), DB::raw('SUM(jml_perolehan) as total_donasi'), DB::raw('MAX(tanggal) as last_donation'))
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->groupBy('no_hp')
    ->havingRaw('MAX(tanggal) < ?', [$cutoff30])
    ->orderByDesc('total_donasi')
    ->limit(5)
    ->get();
$count30 = DB::table('laporans')
    ->select('no_hp')
    ->whereNotNull('no_hp')
    ->where('no_hp', '!=', '')
    ->groupBy('no_hp')
    ->havingRaw('MAX(tanggal) < ?', [$cutoff30])
    ->get()
    ->count();
echo "   Total tidak aktif 30 hari: $count30\n";
foreach ($tidakAktif30 as $i => $d) {
    echo "   " . ($i+1) . ". {$d->nama_donatur} - Rp " . number_format($d->total_donasi) . " (last: {$d->last_donation})\n";
}

echo "\n=== SELESAI ===\n";
