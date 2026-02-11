<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerolehan;
use App\Models\Donatur;
use App\Models\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanPerolehanCrmController extends Controller
{
    /**
     * Display the main CRM dashboard for Laporan Perolehan
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $selectedDate = $request->get('tanggal', $today->format('Y-m-d'));
        
        // Get list of CS for filter
        $csList = CustomerService::orderBy('name')->pluck('name')->toArray();
        
        // Get list of Tim for filter
        $timList = CustomerService::distinct()->orderBy('team')->pluck('team')->filter()->toArray();
        
        // Initial data for the view
        $initialStats = $this->getTodayStats($selectedDate);
        $initialHourly = $this->getHourlyBreakdown($selectedDate);
        $initialLeaderboard = $this->getCsLeaderboard($selectedDate);
        $initialLiveFeed = $this->getLiveFeed($selectedDate, 20);
        
        return view('laporan-perolehan.index', compact(
            'csList',
            'timList',
            'selectedDate',
            'initialStats',
            'initialHourly',
            'initialLeaderboard',
            'initialLiveFeed'
        ));
    }
    
    /**
     * API: Get today's stats
     */
    public function apiTodayStats(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getTodayStats($date));
    }
    
    /**
     * API: Get hourly breakdown
     */
    public function apiHourlyBreakdown(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getHourlyBreakdown($date));
    }
    
    /**
     * API: Get CS Leaderboard
     */
    public function apiCsLeaderboard(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $tim = $request->get('tim', 'all');
        return response()->json($this->getCsLeaderboard($date, $tim));
    }
    
    /**
     * API: Get Source Breakdown
     */
    public function apiSourceBreakdown(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getSourceBreakdown($date));
    }
    
    /**
     * API: Get Team Breakdown
     */
    public function apiTeamBreakdown(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getTeamBreakdown($date));
    }
    
    /**
     * API: Get Trend Comparison
     */
    public function apiTrendComparison(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getTrendComparison($date));
    }
    
    /**
     * API: Get Live Feed (recent transactions)
     */
    public function apiLiveFeed(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $limit = $request->get('limit', 20);
        return response()->json($this->getLiveFeed($date, $limit));
    }
    
    /**
     * API: Export to Excel
     */
    public function apiExport(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        
        // Get all data for the date
        $data = LaporanPerolehan::whereDate('tanggal', $date)
            ->select(
                'tanggal',
                'perolehan_jam',
                'nama_cs',
                'tim',
                'nama_donatur',
                'no_hp',
                'jml_perolehan',
                'hasil_dari',
                'program_utama',
                'zakat',
                'prg_cross_selling',
                'nama_produk',
                'nama_platform',
                'kat_donatur',
                'jml_database'
            )
            ->orderBy('perolehan_jam')
            ->orderBy('nama_cs')
            ->get();
        
        // Generate CSV
        $filename = 'laporan_perolehan_' . $date . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'Tanggal',
                'Jam Perolehan',
                'Nama CS',
                'Tim',
                'Nama Donatur',
                'No HP',
                'Jumlah Perolehan',
                'Hasil Dari',
                'Program Utama',
                'Zakat',
                'Cross Selling',
                'Nama Produk',
                'Platform',
                'Kategori Donatur',
                'Jml Database'
            ]);
            
            // Data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->tanggal,
                    $row->perolehan_jam,
                    $row->nama_cs,
                    $row->tim,
                    $row->nama_donatur,
                    $row->no_hp,
                    $row->jml_perolehan,
                    $row->hasil_dari,
                    $row->program_utama,
                    $row->zakat,
                    $row->prg_cross_selling,
                    $row->nama_produk,
                    $row->nama_platform,
                    $row->kat_donatur,
                    $row->jml_database
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================
    
    /**
     * Get today's statistics
     */
    private function getTodayStats($date)
    {
        $query = LaporanPerolehan::whereDate('tanggal', $date);
        
        $totalPerolehan = (clone $query)->sum('jml_perolehan') ?? 0;
        $totalTransaksi = (clone $query)->count();
        $avgPerTransaksi = $totalTransaksi > 0 ? round($totalPerolehan / $totalTransaksi) : 0;
        $donaturUnik = (clone $query)->whereNotNull('no_hp')->where('no_hp', '!=', '')->distinct('no_hp')->count('no_hp');
        
        // Comparison with yesterday
        $yesterday = Carbon::parse($date)->subDay()->format('Y-m-d');
        $yesterdayTotal = LaporanPerolehan::whereDate('tanggal', $yesterday)->sum('jml_perolehan') ?? 0;
        $yesterdayTransaksi = LaporanPerolehan::whereDate('tanggal', $yesterday)->count();
        
        $growthPerolehan = $yesterdayTotal > 0 ? round((($totalPerolehan - $yesterdayTotal) / $yesterdayTotal) * 100, 1) : 0;
        $growthTransaksi = $yesterdayTransaksi > 0 ? round((($totalTransaksi - $yesterdayTransaksi) / $yesterdayTransaksi) * 100, 1) : 0;
        
        // Active CS count
        $activeCs = (clone $query)->distinct('nama_cs')->count('nama_cs');
        
        return [
            'total_perolehan' => $totalPerolehan,
            'total_transaksi' => $totalTransaksi,
            'avg_per_transaksi' => $avgPerTransaksi,
            'donatur_unik' => $donaturUnik,
            'active_cs' => $activeCs,
            'growth_perolehan' => $growthPerolehan,
            'growth_transaksi' => $growthTransaksi,
            'yesterday_total' => $yesterdayTotal,
            'yesterday_transaksi' => $yesterdayTransaksi,
            'tanggal' => $date,
        ];
    }
    
    /**
     * Get hourly breakdown data
     */
    private function getHourlyBreakdown($date)
    {
        $hourlySlots = [
            '08.00-09.00 WIB',
            '09.00-10.00 WIB',
            '10.00-11.00 WIB',
            '11.00-12.00 WIB',
            '12.00-13.00 WIB',
            '13.00-14.00 WIB',
            '14.00-15.00 WIB',
            '15.00-16.00 WIB',
            '16.00-17.00 WIB',
            '17.00-24.00 WIB',
        ];
        
        $data = LaporanPerolehan::whereDate('tanggal', $date)
            ->select('perolehan_jam', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->groupBy('perolehan_jam')
            ->get()
            ->keyBy('perolehan_jam');
        
        // Yesterday data for comparison
        $yesterday = Carbon::parse($date)->subDay()->format('Y-m-d');
        $yesterdayData = LaporanPerolehan::whereDate('tanggal', $yesterday)
            ->select('perolehan_jam', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->groupBy('perolehan_jam')
            ->get()
            ->keyBy('perolehan_jam');
        
        $result = [];
        $peakHour = null;
        $peakAmount = 0;
        
        foreach ($hourlySlots as $slot) {
            $todayTotal = $data->get($slot)?->total ?? 0;
            $todayTrx = $data->get($slot)?->transaksi ?? 0;
            $yesterdayTotal = $yesterdayData->get($slot)?->total ?? 0;
            
            $result[] = [
                'jam' => $slot,
                'jam_short' => str_replace(['.00-', '.00 WIB', ' WIB'], ['-', '', ''], $slot),
                'total' => $todayTotal,
                'transaksi' => $todayTrx,
                'yesterday' => $yesterdayTotal,
            ];
            
            if ($todayTotal > $peakAmount) {
                $peakAmount = $todayTotal;
                $peakHour = $slot;
            }
        }
        
        return [
            'data' => $result,
            'peak_hour' => $peakHour,
            'peak_amount' => $peakAmount,
        ];
    }
    
    /**
     * Get CS Leaderboard
     */
    private function getCsLeaderboard($date, $tim = 'all')
    {
        $query = LaporanPerolehan::whereDate('tanggal', $date);
        
        if ($tim !== 'all') {
            $query->where('tim', $tim);
        }
        
        $leaderboard = $query->select(
                'nama_cs',
                'tim',
                DB::raw('SUM(jml_perolehan) as total_perolehan'),
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('AVG(jml_perolehan) as avg_transaksi'),
                DB::raw('SUM(jml_database) as total_database'),
                DB::raw('COUNT(DISTINCT no_hp) as donatur_unik')
            )
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->groupBy('nama_cs', 'tim')
            ->orderByDesc('total_perolehan')
            ->limit(15)
            ->get()
            ->map(function ($item, $index) {
                $item->rank = $index + 1;
                $item->avg_transaksi = round($item->avg_transaksi);
                $item->conversion_rate = $item->total_database > 0 
                    ? round(($item->total_transaksi / $item->total_database) * 100, 1) 
                    : 0;
                return $item;
            });
        
        return $leaderboard;
    }
    
    /**
     * Get Source Breakdown
     */
    private function getSourceBreakdown($date)
    {
        $query = LaporanPerolehan::whereDate('tanggal', $date);
        
        // By hasil_dari
        $byHasilDari = (clone $query)
            ->select('hasil_dari', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('hasil_dari')
            ->where('hasil_dari', '!=', '')
            ->groupBy('hasil_dari')
            ->orderByDesc('total')
            ->get();
        
        // By program_utama (Subuh, Jumat, Harian)
        $byProgramUtama = (clone $query)
            ->select('program_utama', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('program_utama')
            ->where('program_utama', '!=', '')
            ->groupBy('program_utama')
            ->orderByDesc('total')
            ->get();
        
        // By zakat type
        $byZakat = (clone $query)
            ->select('zakat', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('zakat')
            ->where('zakat', '!=', '')
            ->groupBy('zakat')
            ->orderByDesc('total')
            ->get();
        
        // By cross selling
        $byCrossSelling = (clone $query)
            ->select('prg_cross_selling', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('prg_cross_selling')
            ->where('prg_cross_selling', '!=', '')
            ->groupBy('prg_cross_selling')
            ->orderByDesc('total')
            ->get();
        
        // By platform
        $byPlatform = (clone $query)
            ->select('nama_platform', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('nama_platform')
            ->where('nama_platform', '!=', '')
            ->groupBy('nama_platform')
            ->orderByDesc('total')
            ->get();
        
        // By produk
        $byProduk = (clone $query)
            ->select('nama_produk', 
                DB::raw('SUM(jml_perolehan) as total'),
                DB::raw('COUNT(*) as transaksi')
            )
            ->whereNotNull('nama_produk')
            ->where('nama_produk', '!=', '')
            ->groupBy('nama_produk')
            ->orderByDesc('total')
            ->get();
        
        return [
            'hasil_dari' => $byHasilDari,
            'program_utama' => $byProgramUtama,
            'zakat' => $byZakat,
            'cross_selling' => $byCrossSelling,
            'platform' => $byPlatform,
            'produk' => $byProduk,
        ];
    }
    
    /**
     * Get Team Breakdown
     */
    private function getTeamBreakdown($date)
    {
        $teams = LaporanPerolehan::whereDate('tanggal', $date)
            ->select(
                'tim',
                DB::raw('SUM(jml_perolehan) as total_perolehan'),
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('AVG(jml_perolehan) as avg_transaksi'),
                DB::raw('COUNT(DISTINCT nama_cs) as active_cs'),
                DB::raw('COUNT(DISTINCT no_hp) as donatur_unik')
            )
            ->whereNotNull('tim')
            ->where('tim', '!=', '')
            ->groupBy('tim')
            ->orderByDesc('total_perolehan')
            ->get()
            ->map(function ($item) {
                $item->avg_transaksi = round($item->avg_transaksi);
                return $item;
            });
        
        return $teams;
    }
    
    /**
     * Get Trend Comparison
     */
    private function getTrendComparison($date)
    {
        $currentDate = Carbon::parse($date);
        $yesterday = $currentDate->copy()->subDay();
        $lastWeekSameDay = $currentDate->copy()->subWeek();
        $monthStart = $currentDate->copy()->startOfMonth();
        $lastMonthStart = $currentDate->copy()->subMonth()->startOfMonth();
        $lastMonthSameDay = $currentDate->copy()->subMonth();
        
        // Today
        $todayTotal = LaporanPerolehan::whereDate('tanggal', $currentDate)->sum('jml_perolehan') ?? 0;
        $todayTrx = LaporanPerolehan::whereDate('tanggal', $currentDate)->count();
        
        // Yesterday
        $yesterdayTotal = LaporanPerolehan::whereDate('tanggal', $yesterday)->sum('jml_perolehan') ?? 0;
        $yesterdayTrx = LaporanPerolehan::whereDate('tanggal', $yesterday)->count();
        
        // Last week same day
        $lastWeekTotal = LaporanPerolehan::whereDate('tanggal', $lastWeekSameDay)->sum('jml_perolehan') ?? 0;
        $lastWeekTrx = LaporanPerolehan::whereDate('tanggal', $lastWeekSameDay)->count();
        
        // MTD (Month to Date)
        $mtdTotal = LaporanPerolehan::whereBetween('tanggal', [$monthStart, $currentDate])->sum('jml_perolehan') ?? 0;
        $mtdTrx = LaporanPerolehan::whereBetween('tanggal', [$monthStart, $currentDate])->count();
        
        // Last Month same period
        $lastMtdTotal = LaporanPerolehan::whereBetween('tanggal', [$lastMonthStart, $lastMonthSameDay])->sum('jml_perolehan') ?? 0;
        $lastMtdTrx = LaporanPerolehan::whereBetween('tanggal', [$lastMonthStart, $lastMonthSameDay])->count();
        
        return [
            'today' => [
                'total' => $todayTotal,
                'transaksi' => $todayTrx,
                'label' => 'Hari Ini',
            ],
            'yesterday' => [
                'total' => $yesterdayTotal,
                'transaksi' => $yesterdayTrx,
                'label' => 'Kemarin',
                'growth' => $yesterdayTotal > 0 ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1) : 0,
            ],
            'last_week' => [
                'total' => $lastWeekTotal,
                'transaksi' => $lastWeekTrx,
                'label' => $lastWeekSameDay->translatedFormat('l') . ' Lalu',
                'growth' => $lastWeekTotal > 0 ? round((($todayTotal - $lastWeekTotal) / $lastWeekTotal) * 100, 1) : 0,
            ],
            'mtd' => [
                'total' => $mtdTotal,
                'transaksi' => $mtdTrx,
                'label' => 'MTD ' . $currentDate->translatedFormat('F'),
            ],
            'last_mtd' => [
                'total' => $lastMtdTotal,
                'transaksi' => $lastMtdTrx,
                'label' => 'MTD ' . $lastMonthStart->translatedFormat('F'),
                'growth' => $lastMtdTotal > 0 ? round((($mtdTotal - $lastMtdTotal) / $lastMtdTotal) * 100, 1) : 0,
            ],
        ];
    }
    
    /**
     * Get Live Feed (recent transactions)
     */
    private function getLiveFeed($date, $limit = 20)
    {
        $transactions = LaporanPerolehan::whereDate('tanggal', $date)
            ->select(
                'id',
                'tanggal',
                'created_at',
                'perolehan_jam',
                'nama_cs',
                'tim',
                'nama_donatur',
                'no_hp',
                'jml_perolehan',
                'hasil_dari',
                'program_utama',
                'zakat',
                'prg_cross_selling',
                'nama_produk',
                'nama_platform',
                'kat_donatur'
            )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                // Determine source label
                $source = $item->hasil_dari;
                if (!$source) {
                    if ($item->zakat) $source = 'Zakat: ' . $item->zakat;
                    elseif ($item->prg_cross_selling) $source = $item->prg_cross_selling;
                    elseif ($item->nama_produk) $source = 'Produk: ' . $item->nama_produk;
                    elseif ($item->nama_platform) $source = $item->nama_platform;
                    else $source = 'Lainnya';
                }
                
                $item->source_label = $source;
                $item->time_ago = Carbon::parse($item->created_at)->diffForHumans();
                $item->initial = strtoupper(substr($item->nama_donatur ?? 'D', 0, 1));
                
                return $item;
            });
        
        return $transactions;
    }
}
