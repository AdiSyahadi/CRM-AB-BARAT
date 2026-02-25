<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== SEARCHING FOR PENYEBARAN/TOKO TABLES ===\n";
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = "Tables_in_{$dbName}";

foreach ($tables as $t) {
    $props = get_object_vars($t);
    $name = reset($props);
    if (stripos($name, 'toko') !== false || stripos($name, 'penyebaran') !== false || stripos($name, 'store') !== false || stripos($name, 'outlet') !== false) {
        echo "\n>>> FOUND TABLE: {$name}\n";
        
        $cols = Schema::getColumnListing($name);
        echo "Columns: " . implode(', ', $cols) . "\n";
        
        $count = DB::table($name)->count();
        echo "Row count: {$count}\n";
        
        if ($count > 0) {
            $samples = DB::table($name)->limit(3)->get();
            echo "Sample data (3 rows):\n";
            foreach ($samples as $s) {
                echo json_encode($s, JSON_PRETTY_PRINT) . "\n";
            }
        }
    }
}

// Also check public/penyebarantoko folder
echo "\n=== CHECK public/penyebarantoko ===\n";
$dir = __DIR__ . '/public/penyebarantoko';
if (is_dir($dir)) {
    echo "Directory exists!\n";
} else {
    echo "Directory does NOT exist\n";
}

// More detailed analysis
echo "\n=== DISTINCT STATUS ===\n";
$statuses = DB::table('penyebaran_toko')->distinct()->pluck('status')->toArray();
echo implode(', ', $statuses) . "\n";

echo "\n=== DISTINCT NAMA_CS ===\n";
$cs = DB::table('penyebaran_toko')->distinct()->pluck('nama_cs')->toArray();
echo implode(', ', $cs) . "\n";

echo "\n=== DATE RANGE ===\n";
echo "Min: " . DB::table('penyebaran_toko')->min('tanggal_registrasi') . "\n";
echo "Max: " . DB::table('penyebaran_toko')->max('tanggal_registrasi') . "\n";

echo "\n=== STATUS COUNTS ===\n";
$statusCounts = DB::table('penyebaran_toko')
    ->select('status', DB::raw('COUNT(*) as cnt'))
    ->groupBy('status')
    ->get();
foreach ($statusCounts as $sc) {
    echo "{$sc->status}: {$sc->cnt}\n";
}
