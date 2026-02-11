<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerService;
use App\Models\LaporanPerolehan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformaCsController extends Controller
{
    /**
     * Main view untuk Performa CS
     */
    public function index()
    {
        // Get list tim untuk filter
        $teams = CustomerService::select('team')
            ->distinct()
            ->whereNotNull('team')
            ->orderBy('team')
            ->pluck('team');
        
        // Get list CS untuk filter
        $csList = CustomerService::select('id', 'name', 'team')
            ->orderBy('name')
            ->get();
        
        return view('performa-cs.index', [
            'teams' => $teams,
            'csList' => $csList,
        ]);
    }

    /**
     * API: Overview Summary
     * Total perolehan, donatur baru, donatur repeat, avg/hari, active CS
     */
    public function apiOverviewSummary(Request $request)
    {
        $periode = $request->get('periode', 'bulan_ini'); // bulan_ini, minggu_ini, hari_ini
        $tim = $request->get('tim', 'all');
        
        // Determine date range based on periode
        $dateRange = $this->getDateRange($periode);
        $prevDateRange = $this->getPreviousDateRange($periode);
        
        // Build base query
        $query = LaporanPerolehan::query();
        
        if ($tim !== 'all') {
            $query->where('tim', $tim);
        }
        
        // Current period stats
        $currentStats = $this->getPeriodStats(clone $query, $dateRange['start'], $dateRange['end']);
        
        // Previous period stats for comparison
        $prevStats = $this->getPeriodStats(clone $query, $prevDateRange['start'], $prevDateRange['end']);
        
        // Active CS count
        $activeCs = LaporanPerolehan::whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->when($tim !== 'all', fn($q) => $q->where('tim', $tim))
            ->distinct('nama_cs')
            ->count('nama_cs');
        
        $totalCs = CustomerService::when($tim !== 'all', fn($q) => $q->where('team', $tim))->count();
        
        // Calculate days in period for avg
        $daysInPeriod = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $avgPerDay = $daysInPeriod > 0 ? round($currentStats['total_laporan'] / $daysInPeriod, 1) : 0;
        
        // Growth calculations
        $perolehanGrowth = $prevStats['total_perolehan'] > 0 
            ? round((($currentStats['total_perolehan'] - $prevStats['total_perolehan']) / $prevStats['total_perolehan']) * 100, 1) 
            : 0;
        
        $donaturGrowth = $prevStats['total_donatur'] > 0 
            ? round((($currentStats['total_donatur'] - $prevStats['total_donatur']) / $prevStats['total_donatur']) * 100, 1) 
            : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_perolehan' => $currentStats['total_perolehan'],
                'total_perolehan_formatted' => $this->formatRupiah($currentStats['total_perolehan']),
                'perolehan_growth' => $perolehanGrowth,
                'total_donatur' => $currentStats['total_donatur'],
                'donatur_growth' => $donaturGrowth,
                'donatur_baru' => $currentStats['donatur_baru'],
                'donatur_repeat' => $currentStats['donatur_repeat'],
                'avg_laporan_per_day' => $avgPerDay,
                'active_cs' => $activeCs,
                'total_cs' => $totalCs,
                'periode_label' => $this->getPeriodeLabel($periode),
                'date_range' => [
                    'start' => $dateRange['start']->format('Y-m-d'),
                    'end' => $dateRange['end']->format('Y-m-d'),
                ],
            ]
        ]);
    }

    /**
     * API: Head-to-Head Comparison
     */
    public function apiH2hComparison(Request $request)
    {
        $mode = $request->get('mode', 'bulanan'); // harian, mingguan, bulanan
        $periode1 = $request->get('periode1'); // format depends on mode
        $periode2 = $request->get('periode2');
        $tim = $request->get('tim', 'all');
        $viewBy = $request->get('view_by', 'cs'); // cs atau tim
        
        // Parse periode based on mode
        $range1 = $this->parsePeriode($mode, $periode1);
        $range2 = $this->parsePeriode($mode, $periode2);
        
        // Get comparison data
        if ($viewBy === 'tim') {
            $data = $this->getH2hByTim($range1, $range2, $tim);
        } else {
            $data = $this->getH2hByCs($range1, $range2, $tim);
        }
        
        // Calculate totals
        $totalP1 = collect($data)->sum('periode1_value');
        $totalP2 = collect($data)->sum('periode2_value');
        $totalDiff = $totalP1 - $totalP2;
        $avgGrowth = count($data) > 0 ? round(collect($data)->avg('growth'), 1) : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'comparison' => $data,
                'summary' => [
                    'total_periode1' => $totalP1,
                    'total_periode1_formatted' => $this->formatRupiah($totalP1),
                    'total_periode2' => $totalP2,
                    'total_periode2_formatted' => $this->formatRupiah($totalP2),
                    'total_diff' => $totalDiff,
                    'total_diff_formatted' => ($totalDiff >= 0 ? '+' : '') . $this->formatRupiah($totalDiff),
                    'avg_growth' => $avgGrowth,
                ],
                'labels' => [
                    'periode1' => $this->formatPeriodeLabel($mode, $periode1),
                    'periode2' => $this->formatPeriodeLabel($mode, $periode2),
                ],
            ]
        ]);
    }

    /**
     * API: Leaderboard
     */
    public function apiLeaderboard(Request $request)
    {
        $type = $request->get('type', 'top_earners'); // top_earners, most_improved, most_productive, consistency
        $periode = $request->get('periode', 'bulan_ini');
        $tim = $request->get('tim', 'all');
        $limit = $request->get('limit', 10);
        
        $dateRange = $this->getDateRange($periode);
        $prevDateRange = $this->getPreviousDateRange($periode);
        
        switch ($type) {
            case 'top_earners':
                $data = $this->getTopEarners($dateRange, $tim, $limit);
                break;
            case 'most_improved':
                $data = $this->getMostImproved($dateRange, $prevDateRange, $tim, $limit);
                break;
            case 'most_productive':
                $data = $this->getMostProductive($dateRange, $tim, $limit);
                break;
            case 'consistency':
                $data = $this->getConsistencyStars($dateRange, $tim, $limit);
                break;
            default:
                $data = [];
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'type' => $type,
            'periode_label' => $this->getPeriodeLabel($periode),
        ]);
    }

    /**
     * API: CS List with Stats
     */
    public function apiCsList(Request $request)
    {
        $tim = $request->get('tim', 'all');
        $search = $request->get('search', '');
        $periode = $request->get('periode', 'bulan_ini');
        
        $dateRange = $this->getDateRange($periode);
        
        // Get all CS
        $csQuery = CustomerService::query();
        
        if ($tim !== 'all') {
            $csQuery->where('team', $tim);
        }
        
        if (!empty($search)) {
            $csQuery->where('name', 'like', "%{$search}%");
        }
        
        $csList = $csQuery->orderBy('name')->get();
        
        // Get stats for each CS
        $result = [];
        foreach ($csList as $cs) {
            $stats = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('
                    COUNT(*) as total_laporan,
                    COALESCE(SUM(jml_perolehan), 0) as total_perolehan,
                    COUNT(DISTINCT nama_donatur) as total_donatur
                ')
                ->first();
            
            $result[] = [
                'id' => $cs->id,
                'name' => $cs->name,
                'team' => $cs->team,
                'total_laporan' => $stats->total_laporan ?? 0,
                'total_perolehan' => $stats->total_perolehan ?? 0,
                'total_perolehan_formatted' => $this->formatRupiah($stats->total_perolehan ?? 0),
                'total_donatur' => $stats->total_donatur ?? 0,
            ];
        }
        
        // Sort by total_perolehan desc
        usort($result, fn($a, $b) => $b['total_perolehan'] <=> $a['total_perolehan']);
        
        return response()->json([
            'success' => true,
            'data' => $result,
            'total' => count($result),
        ]);
    }

    /**
     * API: CS Detail
     */
    public function apiCsDetail(Request $request)
    {
        $csId = $request->get('cs_id');
        $csName = $request->get('cs_name');
        $periode = $request->get('periode', 'bulan_ini');
        
        $dateRange = $this->getDateRange($periode);
        
        // Get CS info
        $cs = null;
        if ($csId) {
            $cs = CustomerService::find($csId);
            $csName = $cs->name ?? $csName;
        }
        
        if (!$csName) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }
        
        // Get summary stats
        $summary = LaporanPerolehan::where('nama_cs', $csName)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as total_laporan,
                COALESCE(SUM(jml_perolehan), 0) as total_perolehan,
                COUNT(DISTINCT nama_donatur) as total_donatur,
                COUNT(DISTINCT tanggal) as active_days
            ')
            ->first();
        
        // Get trend data (weekly for current month)
        $trend = LaporanPerolehan::where('nama_cs', $csName)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                YEARWEEK(tanggal, 1) as week,
                MIN(tanggal) as week_start,
                COALESCE(SUM(jml_perolehan), 0) as total
            ')
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        
        // Get program breakdown
        $programBreakdown = LaporanPerolehan::where('nama_cs', $csName)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->selectRaw('
                program,
                COUNT(*) as count,
                COALESCE(SUM(jml_perolehan), 0) as total
            ')
            ->groupBy('program')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        // Get recent activity
        $recentActivity = LaporanPerolehan::where('nama_cs', $csName)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['tanggal', 'nama_donatur', 'jml_perolehan', 'program', 'created_at']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'cs' => [
                    'id' => $cs->id ?? null,
                    'name' => $csName,
                    'team' => $cs->team ?? null,
                ],
                'summary' => [
                    'total_laporan' => $summary->total_laporan ?? 0,
                    'total_perolehan' => $summary->total_perolehan ?? 0,
                    'total_perolehan_formatted' => $this->formatRupiah($summary->total_perolehan ?? 0),
                    'total_donatur' => $summary->total_donatur ?? 0,
                    'active_days' => $summary->active_days ?? 0,
                ],
                'trend' => $trend,
                'program_breakdown' => $programBreakdown,
                'recent_activity' => $recentActivity,
            ]
        ]);
    }

    /**
     * API: Insights & Alerts
     */
    public function apiInsightsAlerts(Request $request)
    {
        $tim = $request->get('tim', 'all');
        
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Get all CS
        $allCs = CustomerService::when($tim !== 'all', fn($q) => $q->where('team', $tim))->get();
        
        $alerts = [];
        $insights = [];
        
        // 1. CS Inactive (tidak laporan >3 hari)
        $inactiveCs = [];
        foreach ($allCs as $cs) {
            $lastReport = LaporanPerolehan::where('nama_cs', $cs->name)
                ->orderByDesc('tanggal')
                ->first();
            
            if (!$lastReport || Carbon::parse($lastReport->tanggal)->diffInDays($today) > 3) {
                $inactiveCs[] = [
                    'name' => $cs->name,
                    'team' => $cs->team,
                    'last_report' => $lastReport ? Carbon::parse($lastReport->tanggal)->format('d M Y') : 'Tidak ada',
                    'days_inactive' => $lastReport ? Carbon::parse($lastReport->tanggal)->diffInDays($today) : 999,
                ];
            }
        }
        
        if (count($inactiveCs) > 0) {
            $alerts[] = [
                'type' => 'inactive',
                'severity' => 'danger',
                'title' => count($inactiveCs) . ' CS tidak laporan >3 hari',
                'data' => array_slice($inactiveCs, 0, 5),
            ];
        }
        
        // 2. Performance Drop (penurunan >20% vs bulan lalu)
        $performanceDrop = [];
        foreach ($allCs as $cs) {
            $thisMonthTotal = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$thisMonth, $today])
                ->sum('jml_perolehan') ?? 0;
            
            $lastMonthTotal = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$lastMonth, $lastMonthEnd])
                ->sum('jml_perolehan') ?? 0;
            
            if ($lastMonthTotal > 0) {
                $change = (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100;
                if ($change < -20) {
                    $performanceDrop[] = [
                        'name' => $cs->name,
                        'team' => $cs->team,
                        'this_month' => $this->formatRupiah($thisMonthTotal),
                        'last_month' => $this->formatRupiah($lastMonthTotal),
                        'change' => round($change, 1),
                    ];
                }
            }
        }
        
        if (count($performanceDrop) > 0) {
            usort($performanceDrop, fn($a, $b) => $a['change'] <=> $b['change']);
            $alerts[] = [
                'type' => 'performance_drop',
                'severity' => 'warning',
                'title' => count($performanceDrop) . ' CS performa turun >20%',
                'data' => array_slice($performanceDrop, 0, 5),
            ];
        }
        
        // 3. Rising Stars (CS dengan growth >30%)
        $risingStars = [];
        foreach ($allCs as $cs) {
            $thisMonthTotal = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$thisMonth, $today])
                ->sum('jml_perolehan') ?? 0;
            
            $lastMonthTotal = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$lastMonth, $lastMonthEnd])
                ->sum('jml_perolehan') ?? 0;
            
            if ($lastMonthTotal > 0 && $thisMonthTotal > $lastMonthTotal) {
                $change = (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100;
                if ($change > 30) {
                    $risingStars[] = [
                        'name' => $cs->name,
                        'team' => $cs->team,
                        'this_month' => $this->formatRupiah($thisMonthTotal),
                        'change' => round($change, 1),
                    ];
                }
            }
        }
        
        if (count($risingStars) > 0) {
            usort($risingStars, fn($a, $b) => $b['change'] <=> $a['change']);
            $alerts[] = [
                'type' => 'rising_star',
                'severity' => 'success',
                'title' => count($risingStars) . ' Rising Star bulan ini',
                'data' => array_slice($risingStars, 0, 5),
            ];
        }
        
        // Best Practices Insights
        // Best channel
        $bestChannel = LaporanPerolehan::whereBetween('tanggal', [$thisMonth, $today])
            ->whereNotNull('channel')
            ->where('channel', '!=', '')
            ->selectRaw('channel, COUNT(*) as count, SUM(jml_perolehan) as total')
            ->groupBy('channel')
            ->orderByDesc('total')
            ->first();
        
        if ($bestChannel) {
            $insights[] = [
                'type' => 'best_channel',
                'icon' => '📱',
                'text' => "Channel terbaik: {$bestChannel->channel} (" . $this->formatRupiah($bestChannel->total) . ")",
            ];
        }
        
        // Best program
        $bestProgram = LaporanPerolehan::whereBetween('tanggal', [$thisMonth, $today])
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->selectRaw('program, COUNT(*) as count, SUM(jml_perolehan) as total, AVG(jml_perolehan) as avg')
            ->groupBy('program')
            ->orderByDesc('total')
            ->first();
        
        if ($bestProgram) {
            $insights[] = [
                'type' => 'best_program',
                'icon' => '🎯',
                'text' => "Program terlaris: {$bestProgram->program} (Avg " . $this->formatRupiah($bestProgram->avg) . ")",
            ];
        }
        
        // Best day of week
        $bestDay = LaporanPerolehan::whereBetween('tanggal', [$thisMonth, $today])
            ->selectRaw('DAYNAME(tanggal) as day_name, DAYOFWEEK(tanggal) as day_num, SUM(jml_perolehan) as total')
            ->groupBy('day_name', 'day_num')
            ->orderByDesc('total')
            ->first();
        
        if ($bestDay) {
            $dayNames = ['', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $dayName = $dayNames[$bestDay->day_num] ?? $bestDay->day_name;
            $insights[] = [
                'type' => 'best_day',
                'icon' => '📅',
                'text' => "Hari terbaik: {$dayName} (" . $this->formatRupiah($bestDay->total) . ")",
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
                'insights' => $insights,
            ]
        ]);
    }

    /**
     * API: Chart Data untuk H2H
     */
    public function apiChartData(Request $request)
    {
        $mode = $request->get('mode', 'bulanan');
        $periode1 = $request->get('periode1');
        $periode2 = $request->get('periode2');
        $tim = $request->get('tim', 'all');
        $viewBy = $request->get('view_by', 'cs');
        
        $range1 = $this->parsePeriode($mode, $periode1);
        $range2 = $this->parsePeriode($mode, $periode2);
        
        if ($viewBy === 'tim') {
            $data = $this->getH2hByTim($range1, $range2, $tim);
        } else {
            $data = $this->getH2hByCs($range1, $range2, $tim);
        }
        
        // Format for Chart.js
        $labels = collect($data)->pluck('name')->toArray();
        $dataset1 = collect($data)->pluck('periode1_value')->toArray();
        $dataset2 = collect($data)->pluck('periode2_value')->toArray();
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->formatPeriodeLabel($mode, $periode1),
                        'data' => $dataset1,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    ],
                    [
                        'label' => $this->formatPeriodeLabel($mode, $periode2),
                        'data' => $dataset2,
                        'backgroundColor' => 'rgba(107, 114, 128, 0.6)',
                    ],
                ],
            ]
        ]);
    }

    /**
     * API: Export Data
     */
    public function apiExport(Request $request)
    {
        $mode = $request->get('mode', 'bulanan');
        $periode1 = $request->get('periode1');
        $periode2 = $request->get('periode2');
        $tim = $request->get('tim', 'all');
        $viewBy = $request->get('view_by', 'cs');
        
        $range1 = $this->parsePeriode($mode, $periode1);
        $range2 = $this->parsePeriode($mode, $periode2);
        
        if ($viewBy === 'tim') {
            $data = $this->getH2hByTim($range1, $range2, $tim);
        } else {
            $data = $this->getH2hByCs($range1, $range2, $tim);
        }
        
        $label1 = $this->formatPeriodeLabel($mode, $periode1);
        $label2 = $this->formatPeriodeLabel($mode, $periode2);
        
        // Generate CSV
        $csv = "Nama," . ($viewBy === 'cs' ? 'Tim,' : '') . "{$label1},{$label2},Selisih,Growth (%)\n";
        
        foreach ($data as $row) {
            $csv .= $row['name'] . ',';
            if ($viewBy === 'cs') {
                $csv .= ($row['team'] ?? '-') . ',';
            }
            $csv .= $row['periode1_value'] . ',';
            $csv .= $row['periode2_value'] . ',';
            $csv .= $row['diff'] . ',';
            $csv .= $row['growth'] . "\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="perbandingan_cs_' . date('Y-m-d') . '.csv"');
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    private function getDateRange($periode)
    {
        $today = Carbon::today();
        
        switch ($periode) {
            case 'hari_ini':
                return ['start' => $today, 'end' => $today];
            case 'minggu_ini':
                return ['start' => $today->copy()->startOfWeek(), 'end' => $today];
            case 'bulan_ini':
            default:
                return ['start' => $today->copy()->startOfMonth(), 'end' => $today];
        }
    }

    private function getPreviousDateRange($periode)
    {
        $today = Carbon::today();
        
        switch ($periode) {
            case 'hari_ini':
                $yesterday = $today->copy()->subDay();
                return ['start' => $yesterday, 'end' => $yesterday];
            case 'minggu_ini':
                $lastWeekStart = $today->copy()->subWeek()->startOfWeek();
                $lastWeekEnd = $today->copy()->subWeek()->endOfWeek();
                return ['start' => $lastWeekStart, 'end' => $lastWeekEnd];
            case 'bulan_ini':
            default:
                $lastMonthStart = $today->copy()->subMonth()->startOfMonth();
                $lastMonthEnd = $today->copy()->subMonth()->endOfMonth();
                return ['start' => $lastMonthStart, 'end' => $lastMonthEnd];
        }
    }

    private function getPeriodStats($query, $start, $end)
    {
        $stats = $query->whereBetween('tanggal', [$start, $end])
            ->selectRaw('
                COUNT(*) as total_laporan,
                COALESCE(SUM(jml_perolehan), 0) as total_perolehan,
                COUNT(DISTINCT nama_donatur) as total_donatur
            ')
            ->first();
        
        // Calculate donatur baru vs repeat (simplified - based on first appearance)
        $donaturBaru = $query->whereBetween('tanggal', [$start, $end])
            ->whereRaw('nama_donatur NOT IN (SELECT DISTINCT nama_donatur FROM laporans WHERE tanggal < ?)', [$start])
            ->distinct('nama_donatur')
            ->count('nama_donatur');
        
        return [
            'total_laporan' => $stats->total_laporan ?? 0,
            'total_perolehan' => $stats->total_perolehan ?? 0,
            'total_donatur' => $stats->total_donatur ?? 0,
            'donatur_baru' => $donaturBaru,
            'donatur_repeat' => ($stats->total_donatur ?? 0) - $donaturBaru,
        ];
    }

    private function parsePeriode($mode, $periode)
    {
        if (!$periode) {
            // Default to current period
            $today = Carbon::today();
            switch ($mode) {
                case 'harian':
                    return ['start' => $today, 'end' => $today];
                case 'mingguan':
                    return ['start' => $today->copy()->startOfWeek(), 'end' => $today->copy()->endOfWeek()];
                case 'bulanan':
                default:
                    return ['start' => $today->copy()->startOfMonth(), 'end' => $today->copy()->endOfMonth()];
            }
        }
        
        switch ($mode) {
            case 'harian':
                $date = Carbon::parse($periode);
                return ['start' => $date, 'end' => $date];
            case 'mingguan':
                // Format: 2025-W49
                if (preg_match('/(\d{4})-W(\d{1,2})/', $periode, $matches)) {
                    $year = $matches[1];
                    $week = $matches[2];
                    $date = Carbon::now()->setISODate($year, $week);
                    return ['start' => $date->copy()->startOfWeek(), 'end' => $date->copy()->endOfWeek()];
                }
                break;
            case 'bulanan':
            default:
                // Format: 2025-11
                $date = Carbon::parse($periode . '-01');
                return ['start' => $date->copy()->startOfMonth(), 'end' => $date->copy()->endOfMonth()];
        }
        
        return ['start' => Carbon::today(), 'end' => Carbon::today()];
    }

    private function getH2hByCs($range1, $range2, $tim)
    {
        $result = [];
        
        // Get all CS
        $csQuery = CustomerService::query();
        if ($tim !== 'all') {
            $csQuery->where('team', $tim);
        }
        $csList = $csQuery->get();
        
        foreach ($csList as $cs) {
            $p1 = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$range1['start'], $range1['end']])
                ->sum('jml_perolehan') ?? 0;
            
            $p2 = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$range2['start'], $range2['end']])
                ->sum('jml_perolehan') ?? 0;
            
            $diff = $p1 - $p2;
            $growth = $p2 > 0 ? round((($p1 - $p2) / $p2) * 100, 1) : ($p1 > 0 ? 100 : 0);
            
            $result[] = [
                'id' => $cs->id,
                'name' => $cs->name,
                'team' => $cs->team,
                'periode1_value' => $p1,
                'periode1_formatted' => $this->formatRupiah($p1),
                'periode2_value' => $p2,
                'periode2_formatted' => $this->formatRupiah($p2),
                'diff' => $diff,
                'diff_formatted' => ($diff >= 0 ? '+' : '') . $this->formatRupiah($diff),
                'growth' => $growth,
                'status' => $growth > 10 ? 'up' : ($growth < -10 ? 'down' : 'stable'),
            ];
        }
        
        // Sort by periode1_value desc
        usort($result, fn($a, $b) => $b['periode1_value'] <=> $a['periode1_value']);
        
        return $result;
    }

    private function getH2hByTim($range1, $range2, $tim)
    {
        $result = [];
        
        // Get all teams
        $teams = CustomerService::select('team')
            ->distinct()
            ->whereNotNull('team')
            ->when($tim !== 'all', fn($q) => $q->where('team', $tim))
            ->pluck('team');
        
        foreach ($teams as $team) {
            $p1 = LaporanPerolehan::where('tim', $team)
                ->whereBetween('tanggal', [$range1['start'], $range1['end']])
                ->sum('jml_perolehan') ?? 0;
            
            $p2 = LaporanPerolehan::where('tim', $team)
                ->whereBetween('tanggal', [$range2['start'], $range2['end']])
                ->sum('jml_perolehan') ?? 0;
            
            $diff = $p1 - $p2;
            $growth = $p2 > 0 ? round((($p1 - $p2) / $p2) * 100, 1) : ($p1 > 0 ? 100 : 0);
            
            $result[] = [
                'name' => $team,
                'periode1_value' => $p1,
                'periode1_formatted' => $this->formatRupiah($p1),
                'periode2_value' => $p2,
                'periode2_formatted' => $this->formatRupiah($p2),
                'diff' => $diff,
                'diff_formatted' => ($diff >= 0 ? '+' : '') . $this->formatRupiah($diff),
                'growth' => $growth,
                'status' => $growth > 10 ? 'up' : ($growth < -10 ? 'down' : 'stable'),
            ];
        }
        
        usort($result, fn($a, $b) => $b['periode1_value'] <=> $a['periode1_value']);
        
        return $result;
    }

    private function getTopEarners($dateRange, $tim, $limit)
    {
        return LaporanPerolehan::whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->when($tim !== 'all', fn($q) => $q->where('tim', $tim))
            ->selectRaw('nama_cs as name, tim as team, SUM(jml_perolehan) as total')
            ->groupBy('nama_cs', 'tim')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($item, $index) => [
                'rank' => $index + 1,
                'name' => $item->name,
                'team' => $item->team,
                'value' => $item->total,
                'value_formatted' => $this->formatRupiah($item->total),
            ]);
    }

    private function getMostImproved($dateRange, $prevDateRange, $tim, $limit)
    {
        $result = [];
        
        $csQuery = CustomerService::query();
        if ($tim !== 'all') {
            $csQuery->where('team', $tim);
        }
        $csList = $csQuery->get();
        
        foreach ($csList as $cs) {
            $current = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
                ->sum('jml_perolehan') ?? 0;
            
            $prev = LaporanPerolehan::where('nama_cs', $cs->name)
                ->whereBetween('tanggal', [$prevDateRange['start'], $prevDateRange['end']])
                ->sum('jml_perolehan') ?? 0;
            
            if ($prev > 0) {
                $growth = round((($current - $prev) / $prev) * 100, 1);
                $result[] = [
                    'name' => $cs->name,
                    'team' => $cs->team,
                    'current' => $current,
                    'prev' => $prev,
                    'growth' => $growth,
                    'value' => $growth,
                    'value_formatted' => ($growth >= 0 ? '+' : '') . $growth . '%',
                ];
            }
        }
        
        usort($result, fn($a, $b) => $b['growth'] <=> $a['growth']);
        
        return array_slice(array_map(fn($item, $index) => array_merge($item, ['rank' => $index + 1]), $result, array_keys($result)), 0, $limit);
    }

    private function getMostProductive($dateRange, $tim, $limit)
    {
        return LaporanPerolehan::whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->when($tim !== 'all', fn($q) => $q->where('tim', $tim))
            ->selectRaw('nama_cs as name, tim as team, COUNT(*) as total')
            ->groupBy('nama_cs', 'tim')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($item, $index) => [
                'rank' => $index + 1,
                'name' => $item->name,
                'team' => $item->team,
                'value' => $item->total,
                'value_formatted' => $item->total . ' laporan',
            ]);
    }

    private function getConsistencyStars($dateRange, $tim, $limit)
    {
        $totalDays = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        
        return LaporanPerolehan::whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->when($tim !== 'all', fn($q) => $q->where('tim', $tim))
            ->selectRaw('nama_cs as name, tim as team, COUNT(DISTINCT tanggal) as active_days')
            ->groupBy('nama_cs', 'tim')
            ->orderByDesc('active_days')
            ->limit($limit)
            ->get()
            ->map(fn($item, $index) => [
                'rank' => $index + 1,
                'name' => $item->name,
                'team' => $item->team,
                'value' => $item->active_days,
                'value_formatted' => $item->active_days . '/' . $totalDays . ' hari',
                'percentage' => round(($item->active_days / $totalDays) * 100, 0),
            ]);
    }

    private function formatRupiah($amount)
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
        } elseif ($amount >= 1000) {
            return 'Rp ' . number_format($amount / 1000, 0, ',', '.') . ' rb';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function getPeriodeLabel($periode)
    {
        switch ($periode) {
            case 'hari_ini': return 'Hari Ini';
            case 'minggu_ini': return 'Minggu Ini';
            case 'bulan_ini': return 'Bulan Ini';
            default: return $periode;
        }
    }

    private function formatPeriodeLabel($mode, $periode)
    {
        if (!$periode) return '-';
        
        switch ($mode) {
            case 'harian':
                return Carbon::parse($periode)->format('d M Y');
            case 'mingguan':
                if (preg_match('/(\d{4})-W(\d{1,2})/', $periode, $matches)) {
                    return "Minggu {$matches[2]}, {$matches[1]}";
                }
                return $periode;
            case 'bulanan':
            default:
                return Carbon::parse($periode . '-01')->format('F Y');
        }
    }
}
