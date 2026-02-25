<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerServiceController extends Controller
{
    // ========================================================================
    // PAGE VIEWS
    // ========================================================================

    /**
     * Daftar CS — Directory with overview stats
     */
    public function daftarIndex()
    {
        $teams = DB::table('customer_services')->distinct()->orderBy('team')->pluck('team');
        return view('customer-service.daftar', compact('teams'));
    }

    /**
     * Performa Bulanan — Monthly performance pivot table
     */
    public function performaBulananIndex()
    {
        $teams = DB::table('customer_services')->distinct()->orderBy('team')->pluck('team');
        $years = DB::table('laporans')
            ->selectRaw('DISTINCT YEAR(tanggal) as yr')
            ->whereRaw('YEAR(tanggal) >= 2024')
            ->orderByDesc('yr')
            ->pluck('yr');
        return view('customer-service.performa-bulanan', compact('teams', 'years'));
    }

    // ========================================================================
    // DAFTAR CS  — API ENDPOINTS
    // ========================================================================

    /**
     * List all CS with stats
     */
    public function apiCsList(Request $request)
    {
        $team = $request->get('team', 'all');
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'name'); // name, team, total_perolehan, total_laporan
        $order = $request->get('order', 'asc');
        $periode = $request->get('periode', 'bulan_ini'); // bulan_ini, bulan_lalu, 3_bulan, tahun_ini

        [$startDate, $endDate] = $this->resolvePeriod($periode);

        $query = DB::table('customer_services');

        if ($team !== 'all') {
            $query->where('team', $team);
        }
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $csList = $query->orderBy('name')->get();

        // Get stats for each CS in one query
        $statsQuery = DB::table('laporans')
            ->select(
                'nama_cs',
                DB::raw('COUNT(*) as total_laporan'),
                DB::raw('SUM(COALESCE(jml_perolehan, 0)) as total_perolehan'),
                DB::raw('COUNT(DISTINCT tanggal) as hari_aktif'),
                DB::raw('MAX(tanggal) as last_active'),
                DB::raw('COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur')
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('nama_cs');

        $statsMap = $statsQuery->get()->keyBy('nama_cs');

        // Get previous period for comparison
        $prevPeriod = $this->getPreviousPeriod($periode, $startDate, $endDate);
        $prevStats = DB::table('laporans')
            ->select(
                'nama_cs',
                DB::raw('SUM(COALESCE(jml_perolehan, 0)) as total_perolehan')
            )
            ->whereBetween('tanggal', [$prevPeriod['start'], $prevPeriod['end']])
            ->groupBy('nama_cs')
            ->get()
            ->keyBy('nama_cs');

        // Get attendance stats
        $absenStats = DB::table('absen_cs')
            ->select(
                'nama_cs',
                DB::raw('COUNT(DISTINCT tanggal) as total_hadir'),
                DB::raw("COUNT(DISTINCT CASE WHEN status_kehadiran = 'Hadir' THEN tanggal END) as hari_hadir")
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('nama_cs')
            ->get()
            ->keyBy('nama_cs');

        $result = $csList->map(function ($cs) use ($statsMap, $prevStats, $absenStats) {
            $stats = $statsMap->get($cs->name);
            $prev = $prevStats->get($cs->name);
            $absen = $absenStats->get($cs->name);

            $totalPerolehan = $stats?->total_perolehan ?? 0;
            $prevPerolehan = $prev?->total_perolehan ?? 0;
            $growth = $prevPerolehan > 0 ? round(($totalPerolehan - $prevPerolehan) / $prevPerolehan * 100, 1) : 0;

            return [
                'id' => $cs->id,
                'name' => $cs->name,
                'team' => $cs->team,
                'total_perolehan' => (int) $totalPerolehan,
                'total_perolehan_formatted' => 'Rp ' . number_format($totalPerolehan, 0, ',', '.'),
                'total_laporan' => (int) ($stats?->total_laporan ?? 0),
                'total_donatur' => (int) ($stats?->total_donatur ?? 0),
                'hari_aktif' => (int) ($stats?->hari_aktif ?? 0),
                'last_active' => $stats?->last_active ?? null,
                'last_active_formatted' => $stats?->last_active ? Carbon::parse($stats->last_active)->translatedFormat('d M Y') : '-',
                'growth' => $growth,
                'hari_hadir' => (int) ($absen?->hari_hadir ?? 0),
            ];
        });

        // Sort
        $sorted = match ($sort) {
            'total_perolehan' => $order === 'desc' ? $result->sortByDesc('total_perolehan') : $result->sortBy('total_perolehan'),
            'total_laporan' => $order === 'desc' ? $result->sortByDesc('total_laporan') : $result->sortBy('total_laporan'),
            'team' => $order === 'desc' ? $result->sortByDesc('team') : $result->sortBy('team'),
            'growth' => $order === 'desc' ? $result->sortByDesc('growth') : $result->sortBy('growth'),
            default => $order === 'desc' ? $result->sortByDesc('name') : $result->sortBy('name'),
        };

        return response()->json([
            'success' => true,
            'data' => $sorted->values(),
        ]);
    }

    /**
     * Overview summary stats
     */
    public function apiOverviewStats(Request $request)
    {
        $periode = $request->get('periode', 'bulan_ini');
        [$startDate, $endDate] = $this->resolvePeriod($periode);
        $prevPeriod = $this->getPreviousPeriod($periode, $startDate, $endDate);

        // Current period
        $current = DB::table('laporans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT nama_cs) as active_cs,
                SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                COUNT(*) as total_laporan,
                COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur,
                AVG(COALESCE(jml_perolehan, 0)) as avg_perolehan
            ')
            ->first();

        // Previous period
        $prev = DB::table('laporans')
            ->whereBetween('tanggal', [$prevPeriod['start'], $prevPeriod['end']])
            ->selectRaw('
                COUNT(DISTINCT nama_cs) as active_cs,
                SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                COUNT(*) as total_laporan,
                COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur
            ')
            ->first();

        $totalCs = DB::table('customer_services')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_cs' => $totalCs,
                'active_cs' => (int) $current->active_cs,
                'total_perolehan' => (int) $current->total_perolehan,
                'total_perolehan_formatted' => 'Rp ' . number_format($current->total_perolehan, 0, ',', '.'),
                'total_laporan' => (int) $current->total_laporan,
                'total_donatur' => (int) $current->total_donatur,
                'avg_perolehan' => (int) $current->avg_perolehan,
                'avg_perolehan_formatted' => 'Rp ' . number_format($current->avg_perolehan, 0, ',', '.'),
                'growth' => [
                    'perolehan' => $this->calcGrowth($current->total_perolehan, $prev->total_perolehan),
                    'laporan' => $this->calcGrowth($current->total_laporan, $prev->total_laporan),
                    'donatur' => $this->calcGrowth($current->total_donatur, $prev->total_donatur),
                ],
                'team_breakdown' => $this->getTeamBreakdown($startDate, $endDate),
            ],
        ]);
    }

    /**
     * Detail single CS
     */
    public function apiCsDetail(Request $request)
    {
        $csId = $request->get('id');
        $periode = $request->get('periode', 'bulan_ini');
        [$startDate, $endDate] = $this->resolvePeriod($periode);

        $cs = DB::table('customer_services')->where('id', $csId)->first();
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // Performance stats
        $stats = DB::table('laporans')
            ->where('nama_cs', $cs->name)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_laporan,
                SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                COUNT(DISTINCT tanggal) as hari_aktif,
                COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur,
                MAX(tanggal) as last_active
            ')
            ->first();

        // Daily trend
        $dailyTrend = DB::table('laporans')
            ->where('nama_cs', $cs->name)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('tanggal')
            ->selectRaw('tanggal, SUM(COALESCE(jml_perolehan, 0)) as total, COUNT(*) as cnt')
            ->orderBy('tanggal')
            ->get();

        // Program breakdown
        $programs = DB::table('laporans')
            ->where('nama_cs', $cs->name)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('hasil_dari')
            ->where('hasil_dari', '!=', '')
            ->groupBy('hasil_dari')
            ->selectRaw('hasil_dari as label, SUM(COALESCE(jml_perolehan, 0)) as total, COUNT(*) as cnt')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Channel breakdown
        $channels = DB::table('laporans')
            ->where('nama_cs', $cs->name)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('channel')
            ->where('channel', '!=', '')
            ->groupBy('channel')
            ->selectRaw('channel as label, SUM(COALESCE(jml_perolehan, 0)) as total, COUNT(*) as cnt')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Recent activity
        $recentActivity = DB::table('laporans')
            ->where('nama_cs', $cs->name)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('jml_perolehan', '>', 0)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(15)
            ->select('tanggal', 'nama_donatur', 'jml_perolehan', 'hasil_dari', 'channel', 'program', 'perolehan_jam')
            ->get()
            ->map(function ($r) {
                $r->jml_perolehan_formatted = 'Rp ' . number_format($r->jml_perolehan, 0, ',', '.');
                return $r;
            });

        // Attendance info
        $absensi = DB::table('absen_cs')
            ->whereRaw("LOWER(nama_cs) LIKE ?", ['%' . strtolower(explode(' (', $cs->name)[0]) . '%'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw("
                COUNT(DISTINCT tanggal) as total_hari,
                COUNT(DISTINCT CASE WHEN status_kehadiran = 'Hadir' THEN tanggal END) as hadir,
                COUNT(DISTINCT CASE WHEN tipe_absen = 'Masuk' THEN tanggal END) as absen_masuk,
                COUNT(DISTINCT CASE WHEN tipe_absen = 'Pulang' THEN tanggal END) as absen_pulang
            ")
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'cs' => [
                    'id' => $cs->id,
                    'name' => $cs->name,
                    'team' => $cs->team,
                ],
                'summary' => [
                    'total_perolehan' => (int) ($stats->total_perolehan ?? 0),
                    'total_perolehan_formatted' => 'Rp ' . number_format($stats->total_perolehan ?? 0, 0, ',', '.'),
                    'total_laporan' => (int) ($stats->total_laporan ?? 0),
                    'total_donatur' => (int) ($stats->total_donatur ?? 0),
                    'hari_aktif' => (int) ($stats->hari_aktif ?? 0),
                    'last_active' => $stats->last_active ? Carbon::parse($stats->last_active)->translatedFormat('d M Y') : '-',
                ],
                'absensi' => [
                    'total_hari' => (int) ($absensi->total_hari ?? 0),
                    'hadir' => (int) ($absensi->hadir ?? 0),
                    'absen_masuk' => (int) ($absensi->absen_masuk ?? 0),
                    'absen_pulang' => (int) ($absensi->absen_pulang ?? 0),
                ],
                'daily_trend' => $dailyTrend,
                'programs' => $programs,
                'channels' => $channels,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }

    /**
     * CRUD: Store new CS
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'team' => 'required|string|max:255',
        ]);

        $id = DB::table('customer_services')->insertGetId([
            'name' => $request->name,
            'team' => $request->team,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id, 'message' => 'CS berhasil ditambahkan']);
    }

    /**
     * CRUD: Update CS
     */
    public function apiUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'team' => 'required|string|max:255',
        ]);

        $affected = DB::table('customer_services')->where('id', $id)->update([
            'name' => $request->name,
            'team' => $request->team,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => $affected > 0, 'message' => $affected > 0 ? 'CS berhasil diupdate' : 'CS tidak ditemukan']);
    }

    /**
     * CRUD: Delete CS
     */
    public function apiDelete($id)
    {
        $affected = DB::table('customer_services')->where('id', $id)->delete();
        return response()->json(['success' => $affected > 0, 'message' => $affected > 0 ? 'CS berhasil dihapus' : 'CS tidak ditemukan']);
    }

    // ========================================================================
    // PERFORMA BULANAN — API ENDPOINTS
    // ========================================================================

    /**
     * Monthly pivot table data
     */
    public function apiMonthlyPivot(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $team = $request->get('team', 'all');
        $mode = $request->get('mode', 'cs'); // cs | team

        $query = DB::table('laporans')
            ->whereYear('tanggal', $year);

        if ($team !== 'all' && $mode === 'cs') {
            $query->where('tim', $team);
        }

        if ($mode === 'team') {
            // Group by team
            $data = $query
                ->select('tim as name')
                ->selectRaw('
                    SUM(CASE WHEN MONTH(tanggal) = 1 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jan,
                    SUM(CASE WHEN MONTH(tanggal) = 2 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as feb,
                    SUM(CASE WHEN MONTH(tanggal) = 3 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mar,
                    SUM(CASE WHEN MONTH(tanggal) = 4 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as apr,
                    SUM(CASE WHEN MONTH(tanggal) = 5 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mei,
                    SUM(CASE WHEN MONTH(tanggal) = 6 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jun,
                    SUM(CASE WHEN MONTH(tanggal) = 7 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jul,
                    SUM(CASE WHEN MONTH(tanggal) = 8 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as agu,
                    SUM(CASE WHEN MONTH(tanggal) = 9 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as sep,
                    SUM(CASE WHEN MONTH(tanggal) = 10 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as okt,
                    SUM(CASE WHEN MONTH(tanggal) = 11 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as nov,
                    SUM(CASE WHEN MONTH(tanggal) = 12 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as des,
                    SUM(COALESCE(jml_perolehan, 0)) as total,
                    COUNT(*) as total_laporan,
                    COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur
                ')
                ->groupBy('tim')
                ->orderByDesc(DB::raw('SUM(COALESCE(jml_perolehan, 0))'))
                ->get();
        } else {
            // Group by CS name
            $data = $query
                ->select('nama_cs as name', 'tim as team')
                ->selectRaw('
                    SUM(CASE WHEN MONTH(tanggal) = 1 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jan,
                    SUM(CASE WHEN MONTH(tanggal) = 2 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as feb,
                    SUM(CASE WHEN MONTH(tanggal) = 3 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mar,
                    SUM(CASE WHEN MONTH(tanggal) = 4 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as apr,
                    SUM(CASE WHEN MONTH(tanggal) = 5 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mei,
                    SUM(CASE WHEN MONTH(tanggal) = 6 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jun,
                    SUM(CASE WHEN MONTH(tanggal) = 7 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jul,
                    SUM(CASE WHEN MONTH(tanggal) = 8 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as agu,
                    SUM(CASE WHEN MONTH(tanggal) = 9 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as sep,
                    SUM(CASE WHEN MONTH(tanggal) = 10 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as okt,
                    SUM(CASE WHEN MONTH(tanggal) = 11 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as nov,
                    SUM(CASE WHEN MONTH(tanggal) = 12 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as des,
                    SUM(COALESCE(jml_perolehan, 0)) as total,
                    COUNT(*) as total_laporan,
                    COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur
                ')
                ->groupBy('nama_cs', 'tim')
                ->orderByDesc(DB::raw('SUM(COALESCE(jml_perolehan, 0))'))
                ->get();
        }

        // Grand total
        $grandTotal = DB::table('laporans')
            ->whereYear('tanggal', $year)
            ->when($team !== 'all' && $mode === 'cs', fn($q) => $q->where('tim', $team))
            ->selectRaw('
                SUM(CASE WHEN MONTH(tanggal) = 1 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jan,
                SUM(CASE WHEN MONTH(tanggal) = 2 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as feb,
                SUM(CASE WHEN MONTH(tanggal) = 3 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mar,
                SUM(CASE WHEN MONTH(tanggal) = 4 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as apr,
                SUM(CASE WHEN MONTH(tanggal) = 5 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as mei,
                SUM(CASE WHEN MONTH(tanggal) = 6 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jun,
                SUM(CASE WHEN MONTH(tanggal) = 7 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as jul,
                SUM(CASE WHEN MONTH(tanggal) = 8 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as agu,
                SUM(CASE WHEN MONTH(tanggal) = 9 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as sep,
                SUM(CASE WHEN MONTH(tanggal) = 10 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as okt,
                SUM(CASE WHEN MONTH(tanggal) = 11 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as nov,
                SUM(CASE WHEN MONTH(tanggal) = 12 THEN COALESCE(jml_perolehan, 0) ELSE 0 END) as des,
                SUM(COALESCE(jml_perolehan, 0)) as total
            ')
            ->first();

        $months = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
        $formattedData = $data->map(function ($row) use ($months) {
            $r = (array) $row;
            foreach ($months as $m) {
                $r[$m] = (int) $r[$m];
            }
            $r['total'] = (int) $r['total'];
            $r['total_laporan'] = (int) $r['total_laporan'];
            $r['total_donatur'] = (int) $r['total_donatur'];
            return $r;
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'grand_total' => $grandTotal,
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ]);
    }

    /**
     * Monthly trend chart data (line chart per month for year)
     */
    public function apiMonthlyTrend(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $team = $request->get('team', 'all');
        $compareYear = $request->get('compare_year');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $getData = function ($yr) use ($team) {
            return DB::table('laporans')
                ->whereYear('tanggal', $yr)
                ->when($team !== 'all', fn($q) => $q->where('tim', $team))
                ->selectRaw('
                    MONTH(tanggal) as bulan,
                    SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                    COUNT(*) as total_laporan,
                    COUNT(DISTINCT nama_cs) as active_cs,
                    COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur
                ')
                ->groupByRaw('MONTH(tanggal)')
                ->orderByRaw('MONTH(tanggal)')
                ->get()
                ->keyBy('bulan');
        };

        $currentData = $getData($year);
        $compareData = $compareYear ? $getData($compareYear) : null;

        $result = [
            'labels' => $months,
            'current' => [
                'year' => $year,
                'perolehan' => [],
                'laporan' => [],
                'donatur' => [],
            ],
        ];

        for ($i = 1; $i <= 12; $i++) {
            $d = $currentData->get($i);
            $result['current']['perolehan'][] = (int) ($d->total_perolehan ?? 0);
            $result['current']['laporan'][] = (int) ($d->total_laporan ?? 0);
            $result['current']['donatur'][] = (int) ($d->total_donatur ?? 0);
        }

        if ($compareData) {
            $result['compare'] = [
                'year' => (int) $compareYear,
                'perolehan' => [],
                'laporan' => [],
                'donatur' => [],
            ];
            for ($i = 1; $i <= 12; $i++) {
                $d = $compareData->get($i);
                $result['compare']['perolehan'][] = (int) ($d->total_perolehan ?? 0);
                $result['compare']['laporan'][] = (int) ($d->total_laporan ?? 0);
                $result['compare']['donatur'][] = (int) ($d->total_donatur ?? 0);
            }
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Top performers per month
     */
    public function apiTopPerformers(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $team = $request->get('team', 'all');
        $limit = (int) $request->get('limit', 10);

        $data = DB::table('laporans')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->when($team !== 'all', fn($q) => $q->where('tim', $team))
            ->select('nama_cs', 'tim')
            ->selectRaw('
                SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                COUNT(*) as total_laporan,
                COUNT(DISTINCT tanggal) as hari_aktif,
                COUNT(DISTINCT CASE WHEN jml_perolehan > 0 THEN nama_donatur END) as total_donatur
            ')
            ->groupBy('nama_cs', 'tim')
            ->orderByDesc(DB::raw('SUM(COALESCE(jml_perolehan, 0))'))
            ->limit($limit)
            ->get()
            ->map(function ($r, $i) {
                $r->rank = $i + 1;
                $r->total_perolehan = (int) $r->total_perolehan;
                $r->total_perolehan_formatted = 'Rp ' . number_format($r->total_perolehan, 0, ',', '.');
                $r->total_laporan = (int) $r->total_laporan;
                $r->hari_aktif = (int) $r->hari_aktif;
                $r->total_donatur = (int) $r->total_donatur;
                return $r;
            });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Export monthly data to CSV
     */
    public function apiExportMonthly(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $team = $request->get('team', 'all');
        $mode = $request->get('mode', 'cs');

        // Reuse pivot logic
        $request->merge(['year' => $year, 'team' => $team, 'mode' => $mode]);
        $pivotResponse = $this->apiMonthlyPivot($request);
        $pivotData = json_decode($pivotResponse->getContent(), true);

        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $filename = "performa_bulanan_{$mode}_{$year}" . ($team !== 'all' ? "_{$team}" : '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pivotData, $months, $mode) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            // Header row
            $header = $mode === 'team' ? ['Tim'] : ['Nama CS', 'Tim'];
            $header = array_merge($header, $months, ['Total', 'Laporan', 'Donatur']);
            fputcsv($file, $header);

            foreach ($pivotData['data'] as $row) {
                $line = $mode === 'team' ? [$row['name']] : [$row['name'], $row['team'] ?? ''];
                $monthKeys = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
                foreach ($monthKeys as $m) $line[] = $row[$m];
                $line[] = $row['total'];
                $line[] = $row['total_laporan'];
                $line[] = $row['total_donatur'];
                fputcsv($file, $line);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    private function resolvePeriod(string $periode): array
    {
        return match ($periode) {
            'bulan_lalu' => [
                Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                Carbon::now()->subMonth()->endOfMonth()->toDateString(),
            ],
            '3_bulan' => [
                Carbon::now()->subMonths(3)->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ],
            'tahun_ini' => [
                Carbon::now()->startOfYear()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ],
            default => [ // bulan_ini
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfDay()->toDateString(),
            ],
        };
    }

    private function getPreviousPeriod(string $periode, string $start, string $end): array
    {
        $diff = Carbon::parse($start)->diffInDays(Carbon::parse($end));
        return [
            'start' => Carbon::parse($start)->subDays($diff + 1)->toDateString(),
            'end' => Carbon::parse($start)->subDay()->toDateString(),
        ];
    }

    private function calcGrowth($current, $previous): float
    {
        if (!$previous || $previous == 0) return 0;
        return round(($current - $previous) / $previous * 100, 1);
    }

    private function getTeamBreakdown(string $startDate, string $endDate): array
    {
        return DB::table('laporans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('tim')
            ->selectRaw('
                tim as team,
                SUM(COALESCE(jml_perolehan, 0)) as total_perolehan,
                COUNT(DISTINCT nama_cs) as active_cs,
                COUNT(*) as total_laporan
            ')
            ->orderByDesc(DB::raw('SUM(COALESCE(jml_perolehan, 0))'))
            ->get()
            ->map(function ($r) {
                $r->total_perolehan = (int) $r->total_perolehan;
                $r->total_perolehan_formatted = 'Rp ' . number_format($r->total_perolehan, 0, ',', '.');
                $r->active_cs = (int) $r->active_cs;
                $r->total_laporan = (int) $r->total_laporan;
                return $r;
            })
            ->toArray();
    }
}
