<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalisisDonaturController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        $kategori = $request->get('kategori', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Get list of teams and CS for dropdown
        $timList = DB::table('laporans')
            ->whereNotNull('tim')
            ->where('tim', '!=', '')
            ->distinct()
            ->pluck('tim')
            ->sort()
            ->values();
        
        $csList = DB::table('laporans')
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct()
            ->pluck('nama_cs')
            ->sort()
            ->values();
        
        // Get list of kategori donasi (hasil_dari)
        $kategoriList = DB::table('laporans')
            ->whereNotNull('hasil_dari')
            ->where('hasil_dari', '!=', '')
            ->distinct()
            ->pluck('hasil_dari')
            ->sort()
            ->values();

        // Get available years from database (dynamic)
        $availableYears = DB::table('laporans')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->whereNotNull('tanggal')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        // Get stats data
        $stats = $this->getStats($tahun, $tim, $cs, $kategori);
        
        // Get chart data
        $charts = $this->getChartData($tahun, $tim, $cs, $kategori);
        
        // Get table data with filters
        $donaturList = $this->getDonaturList($tahun, $status, $search, $fromDate, $toDate, $tim, $cs, $kategori);
        
        // Prepare initial table data for Alpine.js
        $initialTableData = $this->formatTableData($donaturList);

        return view('analisis-donatur.index', compact(
            'stats',
            'charts', 
            'donaturList',
            'initialTableData',
            'tahun',
            'tim',
            'cs',
            'kategori',
            'timList',
            'csList',
            'kategoriList',
            'availableYears',
            'status',
            'search',
            'fromDate',
            'toDate'
        ));
    }
    
    /**
     * Format table data for JSON response
     */
    private function formatTableData($paginator)
    {
        $data = collect($paginator->items())->map(function($item, $index) use ($paginator) {
            return [
                'no' => $paginator->firstItem() + $index,
                'nama_donatur' => $item->nama_donatur ?? '-',
                'no_hp' => $item->no_hp,
                'nama_cs' => $item->nama_cs ?? '-',
                'total_donasi' => $item->total_donasi,
                'jml_transaksi' => $item->jml_transaksi,
                'first_donation' => Carbon::parse($item->first_donation)->format('d M Y'),
                'last_donation' => Carbon::parse($item->last_donation)->format('d M Y'),
                'wa_link' => 'https://wa.me/' . preg_replace('/^0/', '62', $item->no_hp),
                'initial' => strtoupper(substr($item->nama_donatur ?? 'N', 0, 1)),
            ];
        })->values()->toArray();
        
        return [
            'data' => $data,
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'first_item' => $paginator->firstItem() ?? 0,
            'last_item' => $paginator->lastItem() ?? 0,
        ];
    }

    /**
     * Get statistics data
     */
    private function getStats($tahun, $tim = 'all', $cs = 'all', $kategori = 'all', $fromDate = null, $toDate = null)
    {
        // Handle 'all' tahun - default to current year for calculations
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;
        $hasDateRange = $fromDate && $toDate;
        
        // Base filter closure for tim, cs & kategori
        $applyTeamCsFilter = function($query) use ($tim, $cs, $kategori) {
            if ($tim !== 'all') {
                $query->where('tim', $tim);
            }
            if ($cs !== 'all') {
                $query->where('nama_cs', $cs);
            }
            if ($kategori !== 'all') {
                $query->where('hasil_dari', $kategori);
            }
            return $query;
        };

        // Helper: apply date filter (date range OR year)
        $applyDateFilter = function($query) use ($tahun, $tahunInt, $hasDateRange, $fromDate, $toDate) {
            if ($hasDateRange) {
                $query->whereBetween('tanggal', [$fromDate, $toDate]);
            } elseif ($tahun !== 'all') {
                $query->whereYear('tanggal', $tahunInt);
            }
            return $query;
        };
        
        // Total donatur unik
        $totalDonaturQuery = $applyTeamCsFilter(DB::table('laporans')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''));
        $applyDateFilter($totalDonaturQuery);
        $totalDonatur = $totalDonaturQuery->distinct()->count('no_hp');

        // Donatur aktif bulan ini
        $donaturAktifBulanIni = $applyTeamCsFilter(DB::table('laporans')
            ->whereYear('tanggal', date('Y'))
            ->whereMonth('tanggal', date('m'))
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''))
            ->distinct()
            ->count('no_hp');

        // Donatur baru tahun ini (tidak ada di tahun sebelumnya)
        $donaturTahunLalu = $applyTeamCsFilter(DB::table('laporans')
            ->whereYear('tanggal', $tahunInt - 1)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''))
            ->distinct()
            ->pluck('no_hp');

        $donaturBaru = $applyTeamCsFilter(DB::table('laporans')
            ->whereYear('tanggal', $tahunInt)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''))
            ->whereNotIn('no_hp', $donaturTahunLalu)
            ->distinct()
            ->count('no_hp');

        // Donatur hilang (ada di tahun lalu, tidak di tahun ini)
        $donaturTahunIni = $applyTeamCsFilter(DB::table('laporans')
            ->whereYear('tanggal', $tahunInt)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''))
            ->distinct()
            ->pluck('no_hp');

        $donaturHilang = $applyTeamCsFilter(DB::table('laporans')
            ->whereYear('tanggal', $tahunInt - 1)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''))
            ->whereNotIn('no_hp', $donaturTahunIni)
            ->distinct()
            ->count('no_hp');

        // Total perolehan (semua record termasuk offline tanpa no_hp)
        $totalPerolehanQuery = $applyTeamCsFilter(DB::table('laporans'));
        $applyDateFilter($totalPerolehanQuery);
        $totalPerolehan = $totalPerolehanQuery->sum('jml_perolehan');

        // Tidak aktif 30 hari
        $tidakAktifQuery = DB::table('laporans')
            ->select('no_hp')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');
        if ($tim !== 'all') {
            $tidakAktifQuery->where('tim', $tim);
        }
        if ($cs !== 'all') {
            $tidakAktifQuery->where('nama_cs', $cs);
        }
        if ($kategori !== 'all') {
            $tidakAktifQuery->where('hasil_dari', $kategori);
        }
        $tidakAktif30Hari = $tidakAktifQuery
            ->groupBy('no_hp')
            ->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')])
            ->get()
            ->count();

        // Average donasi per donatur
        $avgDonasi = $totalDonatur > 0 ? $totalPerolehan / $totalDonatur : 0;

        // Total transaksi untuk avg per transaksi
        $totalTransaksiQuery = $applyTeamCsFilter(DB::table('laporans')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''));
        $applyDateFilter($totalTransaksiQuery);
        $totalTransaksi = $totalTransaksiQuery->count();
        $avgPerTransaksi = $totalTransaksi > 0 ? $totalPerolehan / $totalTransaksi : 0;

        // Growth YoY (perbandingan dengan tahun lalu)
        $perolehanTahunLaluQuery = $applyTeamCsFilter(DB::table('laporans'));
        $perolehanTahunLaluQuery->whereYear('tanggal', $tahunInt - 1);
        $perolehanTahunLalu = $perolehanTahunLaluQuery->sum('jml_perolehan');
        
        $growthRate = $perolehanTahunLalu > 0 
            ? round((($totalPerolehan - $perolehanTahunLalu) / $perolehanTahunLalu) * 100, 1) 
            : ($totalPerolehan > 0 ? 100 : 0);

        // Repeat donor rate (donatur dengan >1 transaksi)
        $repeatDonorQuery = $applyTeamCsFilter(DB::table('laporans')
            ->select('no_hp')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', ''));
        $applyDateFilter($repeatDonorQuery);
        $repeatDonor = $repeatDonorQuery
            ->groupBy('no_hp')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
        
        $repeatDonorRate = $totalDonatur > 0 ? round(($repeatDonor / $totalDonatur) * 100, 1) : 0;

        return [
            'total_donatur' => $totalDonatur,
            'donatur_aktif_bulan_ini' => $donaturAktifBulanIni,
            'donatur_baru' => $donaturBaru,
            'donatur_hilang' => $donaturHilang,
            'total_perolehan' => $totalPerolehan,
            'tidak_aktif_30hari' => $tidakAktif30Hari,
            'avg_donasi' => $avgDonasi,
            'avg_per_transaksi' => $avgPerTransaksi,
            'total_transaksi' => $totalTransaksi,
            'growth_rate' => $growthRate,
            'perolehan_tahun_lalu' => $perolehanTahunLalu,
            'repeat_donor' => $repeatDonor,
            'repeat_donor_rate' => $repeatDonorRate,
        ];
    }

    /**
     * Get chart data
     */
    private function getChartData($tahun, $tim = 'all', $cs = 'all', $kategori = 'all')
    {
        // Handle 'all' tahun - default to current year for calculations
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;
        
        // Base filter closure for tim, cs & kategori
        $applyTeamCsFilter = function($query) use ($tim, $cs, $kategori) {
            if ($tim !== 'all') {
                $query->where('tim', $tim);
            }
            if ($cs !== 'all') {
                $query->where('nama_cs', $cs);
            }
            if ($kategori !== 'all') {
                $query->where('hasil_dari', $kategori);
            }
            return $query;
        };
        
        // 1. Trend donasi bulanan (affected by tim & cs filter)
        $trendQuery = DB::table('laporans')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jml_perolehan) as total, COUNT(DISTINCT CASE WHEN no_hp IS NOT NULL AND no_hp != \'\' THEN no_hp END) as donatur');
        
        // Apply tim & cs filter
        $applyTeamCsFilter($trendQuery);
        
        if ($tahun !== 'all') {
            $trendQuery->whereYear('tanggal', $tahunInt);
        } else {
            $trendQuery->whereYear('tanggal', $tahunInt); // Default current year for trend
        }
        
        $trendBulanan = $trendQuery->groupByRaw('MONTH(tanggal)')
            ->orderBy('bulan')
            ->get();

        // 1b. Trend tahun lalu untuk YoY comparison
        $trendTahunLaluQuery = DB::table('laporans')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jml_perolehan) as total, COUNT(DISTINCT CASE WHEN no_hp IS NOT NULL AND no_hp != \'\' THEN no_hp END) as donatur')
            ->whereYear('tanggal', $tahunInt - 1);
        $applyTeamCsFilter($trendTahunLaluQuery);
        $trendTahunLalu = $trendTahunLaluQuery->groupByRaw('MONTH(tanggal)')
            ->orderBy('bulan')
            ->get();

        // 2. Distribusi per tim (NOT affected by tim & cs filter - shows all teams)
        $timQuery = DB::table('laporans')
            ->selectRaw('tim, SUM(jml_perolehan) as total')
            ->whereNotNull('tim')
            ->where('tim', '!=', '');
        
        if ($tahun !== 'all') {
            $timQuery->whereYear('tanggal', $tahunInt);
        }
        
        $distribusiTim = $timQuery->groupBy('tim')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // 3. Top 10 donatur (affected by tim & cs filter)
        $topQuery = DB::table('laporans')
            ->selectRaw('no_hp, MAX(nama_donatur) as nama, SUM(jml_perolehan) as total, COUNT(*) as transaksi')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');
        
        // Apply tim & cs filter
        $applyTeamCsFilter($topQuery);
        
        if ($tahun !== 'all') {
            $topQuery->whereYear('tanggal', $tahunInt);
        }
        
        $topDonatur = $topQuery->groupBy('no_hp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 4. Donatur baru vs hilang per bulan (affected by tim & cs filter)
        $donaturTahunLaluQuery = DB::table('laporans')
            ->whereYear('tanggal', $tahunInt - 1)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');
        $applyTeamCsFilter($donaturTahunLaluQuery);
        $donaturTahunLalu = $donaturTahunLaluQuery->distinct()->pluck('no_hp');

        $donaturBaruPerBulanQuery = DB::table('laporans')
            ->selectRaw('MONTH(tanggal) as bulan, COUNT(DISTINCT no_hp) as jumlah')
            ->whereYear('tanggal', $tahunInt)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->whereNotIn('no_hp', $donaturTahunLalu);
        $applyTeamCsFilter($donaturBaruPerBulanQuery);
        $donaturBaruPerBulan = $donaturBaruPerBulanQuery
            ->groupByRaw('MONTH(tanggal)')
            ->orderBy('bulan')
            ->get();

        // 5. Retention rate (affected by tim & cs filter)
        $donaturTahunIniQuery = DB::table('laporans')
            ->whereYear('tanggal', $tahunInt)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');
        $applyTeamCsFilter($donaturTahunIniQuery);
        $donaturTahunIniTotal = $donaturTahunIniQuery->distinct()->count('no_hp');

        $donaturRetainedQuery = DB::table('laporans')
            ->whereYear('tanggal', $tahunInt)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->whereIn('no_hp', $donaturTahunLalu);
        $applyTeamCsFilter($donaturRetainedQuery);
        $donaturRetained = $donaturRetainedQuery->distinct()->count('no_hp');

        $retentionRate = count($donaturTahunLalu) > 0 
            ? round(($donaturRetained / count($donaturTahunLalu)) * 100, 1) 
            : 0;

        // 6. Ranking CS by perolehan (NOT affected by CS filter - shows all CS)
        $rankingCsQuery = DB::table('laporans')
            ->selectRaw('nama_cs, SUM(jml_perolehan) as total, COUNT(DISTINCT no_hp) as donatur, COUNT(*) as transaksi')
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '');
        
        if ($tahun !== 'all') {
            $rankingCsQuery->whereYear('tanggal', $tahunInt);
        }
        // Only apply tim filter, not cs filter (so we see all CS)
        if ($tim !== 'all') {
            $rankingCsQuery->where('tim', $tim);
        }
        
        $rankingCs = $rankingCsQuery->groupBy('nama_cs')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 7. Distribusi Nilai Transaksi (per transaksi, bukan kumulatif per donatur)
        $distribusiQuery = DB::table('laporans')
            ->select('jml_perolehan')
            ->whereNotNull('jml_perolehan')
            ->where('jml_perolehan', '>', 0);
        
        if ($tahun !== 'all') {
            $distribusiQuery->whereYear('tanggal', $tahunInt);
        }
        $applyTeamCsFilter($distribusiQuery);
        
        $transaksiList = $distribusiQuery->get();
        $totalTransaksi = $transaksiList->count();
        $totalNilai = $transaksiList->sum('jml_perolehan');
        $rataRata = $totalTransaksi > 0 ? round($totalNilai / $totalTransaksi) : 0;
        
        $distribusiNilai = [
            ['label' => '< 50rb', 'count' => 0, 'min' => 0, 'max' => 50000],
            ['label' => '50rb - 100rb', 'count' => 0, 'min' => 50000, 'max' => 100000],
            ['label' => '100rb - 500rb', 'count' => 0, 'min' => 100000, 'max' => 500000],
            ['label' => '500rb - 1jt', 'count' => 0, 'min' => 500000, 'max' => 1000000],
            ['label' => '> 1jt', 'count' => 0, 'min' => 1000000, 'max' => PHP_INT_MAX],
        ];
        
        foreach ($transaksiList as $trx) {
            $nilai = $trx->jml_perolehan;
            if ($nilai < 50000) {
                $distribusiNilai[0]['count']++;
            } elseif ($nilai < 100000) {
                $distribusiNilai[1]['count']++;
            } elseif ($nilai < 500000) {
                $distribusiNilai[2]['count']++;
            } elseif ($nilai < 1000000) {
                $distribusiNilai[3]['count']++;
            } else {
                $distribusiNilai[4]['count']++;
            }
        }
        
        // Hitung persentase
        foreach ($distribusiNilai as &$dist) {
            $dist['pct'] = $totalTransaksi > 0 ? round(($dist['count'] / $totalTransaksi) * 100, 1) : 0;
        }
        unset($dist);
        
        $distribusiNilaiTransaksi = [
            'data' => $distribusiNilai,
            'total_transaksi' => $totalTransaksi,
            'total_nilai' => $totalNilai,
            'rata_rata' => $rataRata
        ];

        // 8. Repeat vs One-time Donatur
        $repeatQuery = DB::table('laporans')
            ->selectRaw('no_hp, COUNT(*) as trx_count')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');
        
        if ($tahun !== 'all') {
            $repeatQuery->whereYear('tanggal', $tahunInt);
        }
        $applyTeamCsFilter($repeatQuery);
        
        $donaturTrxCounts = $repeatQuery->groupBy('no_hp')->get();
        
        $oneTime = $donaturTrxCounts->filter(fn($d) => $d->trx_count == 1)->count();
        $repeat = $donaturTrxCounts->filter(fn($d) => $d->trx_count > 1)->count();
        
        $repeatVsOnetime = [
            ['label' => 'One-time (1x)', 'count' => $oneTime],
            ['label' => 'Repeat (>1x)', 'count' => $repeat],
        ];

        // 9. Trend Perolehan Hari Jumat (Rata-rata per Jumat per Bulan)
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Data Jumat tahun ini
        $trendJumatTahunIni = [];
        $totalJumatTahunIni = 0;
        $totalPerolehanJumatTahunIni = 0;
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Hitung jumlah hari Jumat dalam bulan ini
            $jumlahJumat = $this->countFridaysInMonth($tahunInt, $bulan);
            
            // Query perolehan hari Jumat
            $queryJumat = DB::table('laporans')
                ->whereYear('tanggal', $tahunInt)
                ->whereMonth('tanggal', $bulan)
                ->whereRaw('DAYOFWEEK(tanggal) = 6'); // 6 = Friday in MySQL
            
            $applyTeamCsFilter($queryJumat);
            
            $totalBulan = $queryJumat->sum('jml_perolehan') ?? 0;
            $rataRata = $jumlahJumat > 0 ? round($totalBulan / $jumlahJumat) : 0;
            
            $trendJumatTahunIni[] = [
                'bulan' => $namaBulan[$bulan - 1],
                'total' => $totalBulan,
                'jumlah_jumat' => $jumlahJumat,
                'rata_rata' => $rataRata
            ];
            
            $totalJumatTahunIni += $jumlahJumat;
            $totalPerolehanJumatTahunIni += $totalBulan;
        }
        
        // Data Jumat tahun lalu
        $tahunLaluJumat = $tahunInt - 1;
        $trendJumatTahunLalu = [];
        $totalJumatTahunLalu = 0;
        $totalPerolehanJumatTahunLalu = 0;
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $jumlahJumat = $this->countFridaysInMonth($tahunLaluJumat, $bulan);
            
            $queryJumatLalu = DB::table('laporans')
                ->whereYear('tanggal', $tahunLaluJumat)
                ->whereMonth('tanggal', $bulan)
                ->whereRaw('DAYOFWEEK(tanggal) = 6');
            
            $applyTeamCsFilter($queryJumatLalu);
            
            $totalBulan = $queryJumatLalu->sum('jml_perolehan') ?? 0;
            $rataRata = $jumlahJumat > 0 ? round($totalBulan / $jumlahJumat) : 0;
            
            $trendJumatTahunLalu[] = [
                'bulan' => $namaBulan[$bulan - 1],
                'total' => $totalBulan,
                'jumlah_jumat' => $jumlahJumat,
                'rata_rata' => $rataRata
            ];
            
            $totalJumatTahunLalu += $jumlahJumat;
            $totalPerolehanJumatTahunLalu += $totalBulan;
        }
        
        $trendJumat = [
            'tahun_ini' => $trendJumatTahunIni,
            'tahun_lalu' => $trendJumatTahunLalu,
            'tahun_ini_label' => $tahunInt,
            'tahun_lalu_label' => $tahunLaluJumat,
            'summary' => [
                'total_jumat_tahun_ini' => $totalJumatTahunIni,
                'total_perolehan_tahun_ini' => $totalPerolehanJumatTahunIni,
                'rata_rata_tahun_ini' => $totalJumatTahunIni > 0 ? round($totalPerolehanJumatTahunIni / $totalJumatTahunIni) : 0,
                'total_jumat_tahun_lalu' => $totalJumatTahunLalu,
                'total_perolehan_tahun_lalu' => $totalPerolehanJumatTahunLalu,
                'rata_rata_tahun_lalu' => $totalJumatTahunLalu > 0 ? round($totalPerolehanJumatTahunLalu / $totalJumatTahunLalu) : 0,
            ]
        ];

        // 10. Performa per Hari dalam Seminggu (YoY comparison)
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $namaHariShort = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        
        // Data tahun ini
        $performaHarianTahunIni = [];
        $totalPerolehanTahunIni = 0;
        $totalHariTahunIni = 0;
        $maxValueTahunIni = 0;
        
        for ($hari = 0; $hari <= 6; $hari++) {
            // MySQL DAYOFWEEK: 1=Sunday, 2=Monday, ..., 7=Saturday
            $dayOfWeek = $hari + 1;
            
            $queryPerforma = DB::table('laporans')
                ->whereYear('tanggal', $tahunInt)
                ->whereRaw('DAYOFWEEK(tanggal) = ?', [$dayOfWeek]);
            
            $applyTeamCsFilter($queryPerforma);
            
            $totalPerolehan = $queryPerforma->sum('jml_perolehan') ?? 0;
            
            // Hitung jumlah hari tersebut dalam setahun
            $jumlahHari = 0;
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $jumlahHari += $this->countDayOfWeekInMonth($tahunInt, $bulan, $hari);
            }
            
            $rataRata = $jumlahHari > 0 ? round($totalPerolehan / $jumlahHari) : 0;
            
            $performaHarianTahunIni[] = [
                'hari' => $namaHariShort[$hari],
                'hari_full' => $namaHari[$hari],
                'total' => $totalPerolehan,
                'jumlah_hari' => $jumlahHari,
                'rata_rata' => $rataRata
            ];
            
            $totalPerolehanTahunIni += $totalPerolehan;
            $totalHariTahunIni += $jumlahHari;
            if ($rataRata > $maxValueTahunIni) $maxValueTahunIni = $rataRata;
        }
        
        // Data tahun lalu
        $performaHarianTahunLalu = [];
        $totalPerolehanTahunLalu = 0;
        $totalHariTahunLalu = 0;
        $maxValueTahunLalu = 0;
        
        for ($hari = 0; $hari <= 6; $hari++) {
            $dayOfWeek = $hari + 1;
            
            $queryPerformaLalu = DB::table('laporans')
                ->whereYear('tanggal', $tahunInt - 1)
                ->whereRaw('DAYOFWEEK(tanggal) = ?', [$dayOfWeek]);
            
            $applyTeamCsFilter($queryPerformaLalu);
            
            $totalPerolehan = $queryPerformaLalu->sum('jml_perolehan') ?? 0;
            
            // Hitung jumlah hari tersebut dalam setahun lalu
            $jumlahHari = 0;
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $jumlahHari += $this->countDayOfWeekInMonth($tahunInt - 1, $bulan, $hari);
            }
            
            $rataRata = $jumlahHari > 0 ? round($totalPerolehan / $jumlahHari) : 0;
            
            $performaHarianTahunLalu[] = [
                'hari' => $namaHariShort[$hari],
                'hari_full' => $namaHari[$hari],
                'total' => $totalPerolehan,
                'jumlah_hari' => $jumlahHari,
                'rata_rata' => $rataRata
            ];
            
            $totalPerolehanTahunLalu += $totalPerolehan;
            $totalHariTahunLalu += $jumlahHari;
            if ($rataRata > $maxValueTahunLalu) $maxValueTahunLalu = $rataRata;
        }
        
        // Find best day
        $bestDayTahunIni = collect($performaHarianTahunIni)->sortByDesc('rata_rata')->first();
        $bestDayTahunLalu = collect($performaHarianTahunLalu)->sortByDesc('rata_rata')->first();
        
        $performaHarian = [
            'tahun_ini' => $performaHarianTahunIni,
            'tahun_lalu' => $performaHarianTahunLalu,
            'tahun_ini_label' => $tahunInt,
            'tahun_lalu_label' => $tahunInt - 1,
            'summary' => [
                'total_perolehan_tahun_ini' => $totalPerolehanTahunIni,
                'rata_rata_tahun_ini' => $totalHariTahunIni > 0 ? round($totalPerolehanTahunIni / $totalHariTahunIni) : 0,
                'best_day_tahun_ini' => $bestDayTahunIni['hari_full'] ?? '-',
                'best_day_avg_tahun_ini' => $bestDayTahunIni['rata_rata'] ?? 0,
                'total_perolehan_tahun_lalu' => $totalPerolehanTahunLalu,
                'rata_rata_tahun_lalu' => $totalHariTahunLalu > 0 ? round($totalPerolehanTahunLalu / $totalHariTahunLalu) : 0,
                'best_day_tahun_lalu' => $bestDayTahunLalu['hari_full'] ?? '-',
                'best_day_avg_tahun_lalu' => $bestDayTahunLalu['rata_rata'] ?? 0,
            ]
        ];

        return [
            'trend_bulanan' => $trendBulanan,
            'trend_tahun_lalu' => $trendTahunLalu,
            'distribusi_tim' => $distribusiTim,
            'ranking_cs' => $rankingCs,
            'top_donatur' => $topDonatur,
            'donatur_baru_per_bulan' => $donaturBaruPerBulan,
            'retention_rate' => $retentionRate,
            'donatur_retained' => $donaturRetained,
            'donatur_tahun_lalu' => count($donaturTahunLalu),
            'distribusi_nilai' => $distribusiNilaiTransaksi,
            'repeat_vs_onetime' => $repeatVsOnetime,
            'trend_jumat' => $trendJumat,
            'performa_harian' => $performaHarian,
        ];
    }
    
    /**
     * Count number of specific day of week in a given month
     * $dayOfWeek: 0=Sunday, 1=Monday, ..., 6=Saturday
     */
    private function countDayOfWeekInMonth($year, $month, $dayOfWeek)
    {
        $count = 0;
        $date = Carbon::createFromDate($year, $month, 1);
        $lastDay = $date->copy()->endOfMonth()->day;
        
        for ($day = 1; $day <= $lastDay; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day);
            if ($currentDate->dayOfWeek === $dayOfWeek) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Count number of Fridays in a given month
     */
    private function countFridaysInMonth($year, $month)
    {
        $count = 0;
        $date = Carbon::createFromDate($year, $month, 1);
        $lastDay = $date->copy()->endOfMonth()->day;
        
        for ($day = 1; $day <= $lastDay; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day);
            if ($currentDate->isFriday()) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get donatur list with filters
     */
    private function getDonaturList($tahun, $status, $search, $fromDate, $toDate, $tim = 'all', $cs = 'all', $kategori = 'all')
    {
        // Handle 'all' tahun
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;
        
        $query = DB::table('laporans')
            ->selectRaw('no_hp, MAX(nama_donatur) as nama_donatur, MAX(nama_cs) as nama_cs, SUM(jml_perolehan) as total_donasi, COUNT(*) as jml_transaksi, MIN(tanggal) as first_donation, MAX(tanggal) as last_donation')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        // Apply tim, cs & kategori filter
        if ($tim !== 'all') {
            $query->where('tim', $tim);
        }
        if ($cs !== 'all') {
            $query->where('nama_cs', $cs);
        }
        if ($kategori !== 'all') {
            $query->where('hasil_dari', $kategori);
        }

        // Filter by status
        switch ($status) {
            case 'baru':
                $donaturTahunLalu = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt)
                    ->whereNotIn('no_hp', $donaturTahunLalu);
                break;

            case 'hilang':
                $donaturTahunIni = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt)
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotIn('no_hp', $donaturTahunIni);
                break;

            case 'tidak_aktif':
                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')]);
                break;

            case 'aktif':
                $query->havingRaw('MAX(tanggal) >= ?', [now()->subDays(30)->format('Y-m-d')]);
                break;

            default:
                // Hanya terapkan filter tahun jika TIDAK ada date range
                if ($tahun && $tahun != 'all' && !$fromDate && !$toDate) {
                    $query->whereYear('tanggal', $tahunInt);
                }
                break;
        }

        // Filter by date range (prioritas lebih tinggi dari filter tahun)
        if ($fromDate) {
            $query->whereDate('tanggal', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('tanggal', '<=', $toDate);
        }

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_hp', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%");
            });
        }

        return $query->groupBy('no_hp')
            ->orderByDesc('total_donasi')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Export to Excel
     */
    public function export(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        $kategori = $request->get('kategori', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        // Handle 'all' tahun
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;

        $query = DB::table('laporans')
            ->selectRaw('no_hp, MAX(nama_donatur) as nama_donatur, MAX(tim) as tim, MAX(nama_cs) as nama_cs, MAX(hasil_dari) as kategori, SUM(jml_perolehan) as total_donasi, COUNT(*) as jml_transaksi, MIN(tanggal) as first_donation, MAX(tanggal) as last_donation')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        // Apply tim, cs & kategori filter
        if ($tim !== 'all') {
            $query->where('tim', $tim);
        }
        if ($cs !== 'all') {
            $query->where('nama_cs', $cs);
        }
        if ($kategori !== 'all') {
            $query->where('hasil_dari', $kategori);
        }

        // Apply status filter
        switch ($status) {
            case 'baru':
                $donaturTahunLalu = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotNull('no_hp')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt)
                    ->whereNotIn('no_hp', $donaturTahunLalu);
                break;
            case 'hilang':
                $donaturTahunIni = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt)
                    ->whereNotNull('no_hp')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotIn('no_hp', $donaturTahunIni);
                break;
            case 'tidak_aktif':
                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')]);
                break;
            case 'aktif':
                $query->havingRaw('MAX(tanggal) >= ?', [now()->subDays(30)->format('Y-m-d')]);
                break;
            default:
                // Hanya terapkan filter tahun jika TIDAK ada date range
                if ($tahun && $tahun != 'all' && !$fromDate && !$toDate) {
                    $query->whereYear('tanggal', $tahunInt);
                }
        }

        // Filter by date range (prioritas lebih tinggi dari filter tahun)
        if ($fromDate) {
            $query->whereDate('tanggal', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('tanggal', '<=', $toDate);
        }

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_hp', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%");
            });
        }

        $data = $query->groupBy('no_hp')->orderByDesc('total_donasi')->limit(10000)->get();

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers - tambah kolom Tim dan CS
        $headers = ['No', 'No HP', 'Nama Donatur', 'Tim', 'CS', 'Total Donasi', 'Jumlah Transaksi', 'Donasi Pertama', 'Donasi Terakhir'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $header);
        }
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($data as $i => $d) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValueExplicit("B{$row}", $d->no_hp, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $d->nama_donatur);
            $sheet->setCellValue("D{$row}", $d->tim);
            $sheet->setCellValue("E{$row}", $d->nama_cs);
            $sheet->setCellValue("F{$row}", $d->total_donasi);
            $sheet->setCellValue("G{$row}", $d->jml_transaksi);
            $sheet->setCellValue("H{$row}", $d->first_donation);
            $sheet->setCellValue("I{$row}", $d->last_donation);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Build filename based on active filters
        $filterParts = [$tahun];
        if ($tim !== 'all') $filterParts[] = $tim;
        if ($cs !== 'all') $filterParts[] = str_replace(' ', '_', $cs);
        if ($status !== 'all') $filterParts[] = $status;
        $filterStr = implode('_', $filterParts);

        $fileName = "analisis_donatur_{$filterStr}_" . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path("app/public/{$fileName}");
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export to PDF Report
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        
        // Handle 'all' tahun
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;
        
        // Get stats and chart data
        $stats = $this->getStats($tahun, $tim, $cs);
        $charts = $this->getChartData($tahun, $tim, $cs);
        
        // Generate QuickChart URLs for charts
        $chartUrls = $this->generateQuickChartUrls($charts, $tahunInt);
        
        // Get top donatur for table
        $topDonatur = DB::table('laporans')
            ->selectRaw('no_hp, MAX(nama_donatur) as nama, SUM(jml_perolehan) as total, COUNT(*) as transaksi')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->when($tahun !== 'all', fn($q) => $q->whereYear('tanggal', $tahunInt))
            ->when($tim !== 'all', fn($q) => $q->where('tim', $tim))
            ->when($cs !== 'all', fn($q) => $q->where('nama_cs', $cs))
            ->groupBy('no_hp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        // Prepare data for PDF
        $data = [
            'tahun' => $tahunInt,
            'tahun_lalu' => $tahunInt - 1,
            'tim' => $tim,
            'cs' => $cs,
            'stats' => $stats,
            'charts' => $charts,
            'chartUrls' => $chartUrls,
            'topDonatur' => $topDonatur,
            'generatedAt' => Carbon::now()->format('d F Y H:i'),
            'logoUrl' => 'https://lazalbahjah.org/wp-content/uploads/2024/03/11.png',
        ];
        
        $pdf = Pdf::loadView('analisis-donatur.pdf-report', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $fileName = "Laporan_Analisis_Donatur_{$tahunInt}_" . date('Ymd_His') . '.pdf';
        
        return $pdf->download($fileName);
    }
    
    /**
     * Generate QuickChart URLs for PDF
     */
    private function generateQuickChartUrls($charts, $tahunInt)
    {
        $baseUrl = 'https://quickchart.io/chart?c=';
        $urls = [];
        
        // 1. Trend Bulanan Chart (Bar + Line YoY)
        $trendLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $trendDataIni = array_fill(0, 12, 0);
        $trendDataLalu = array_fill(0, 12, 0);
        
        foreach ($charts['trend_bulanan'] as $d) {
            $trendDataIni[$d->bulan - 1] = $d->total;
        }
        foreach ($charts['trend_tahun_lalu'] as $d) {
            $trendDataLalu[$d->bulan - 1] = $d->total;
        }
        
        $trendConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $trendLabels,
                'datasets' => [
                    [
                        'type' => 'bar',
                        'label' => (string)($tahunInt - 1),
                        'data' => $trendDataLalu,
                        'backgroundColor' => 'rgba(209, 213, 219, 0.6)',
                        'order' => 2
                    ],
                    [
                        'type' => 'line',
                        'label' => (string)$tahunInt,
                        'data' => $trendDataIni,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'order' => 1
                    ]
                ]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
                'scales' => ['y' => ['beginAtZero' => true]]
            ]
        ];
        $urls['trend'] = $baseUrl . urlencode(json_encode($trendConfig)) . '&w=600&h=300&bkg=white';
        
        // 2. Distribusi Tim Chart (Doughnut)
        $timLabels = [];
        $timData = [];
        foreach ($charts['distribusi_tim'] as $d) {
            $timLabels[] = $d->tim;
            $timData[] = $d->total;
        }
        
        $timConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $timLabels,
                'datasets' => [[
                    'data' => $timData,
                    'backgroundColor' => ['#10B981', '#34D399', '#6EE7B7', '#A7F3D0', '#D1FAE5', '#ECFDF5']
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'right']]
            ]
        ];
        $urls['tim'] = $baseUrl . urlencode(json_encode($timConfig)) . '&w=400&h=250&bkg=white';
        
        // 3. Performa Harian Chart
        $harianLabels = [];
        $harianDataIni = [];
        $harianDataLalu = [];
        
        foreach ($charts['performa_harian']['tahun_ini'] as $d) {
            $harianLabels[] = $d['hari'];
            $harianDataIni[] = $d['rata_rata'];
        }
        foreach ($charts['performa_harian']['tahun_lalu'] as $d) {
            $harianDataLalu[] = $d['rata_rata'];
        }
        
        $harianConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $harianLabels,
                'datasets' => [
                    [
                        'type' => 'bar',
                        'label' => (string)($tahunInt - 1),
                        'data' => $harianDataLalu,
                        'backgroundColor' => 'rgba(209, 213, 219, 0.6)',
                        'order' => 2
                    ],
                    [
                        'type' => 'line',
                        'label' => (string)$tahunInt,
                        'data' => $harianDataIni,
                        'borderColor' => '#8B5CF6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'order' => 1
                    ]
                ]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
                'scales' => ['y' => ['beginAtZero' => true]]
            ]
        ];
        $urls['harian'] = $baseUrl . urlencode(json_encode($harianConfig)) . '&w=500&h=280&bkg=white';
        
        // 4. Repeat vs One-time (Pie)
        $repeatData = $charts['repeat_vs_onetime'];
        $repeatConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => array_column($repeatData, 'label'),
                'datasets' => [[
                    'data' => array_column($repeatData, 'count'),
                    'backgroundColor' => ['#9CA3AF', '#10B981']
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']]
            ]
        ];
        $urls['repeat'] = $baseUrl . urlencode(json_encode($repeatConfig)) . '&w=300&h=250&bkg=white';
        
        // 5. Top 10 Donatur (Horizontal Bar)
        $topLabels = [];
        $topData = [];
        foreach ($charts['top_donatur'] as $d) {
            $nama = $d->nama ?? 'Unknown';
            $topLabels[] = strlen($nama) > 15 ? substr($nama, 0, 15) . '...' : $nama;
            $topData[] = $d->total;
        }
        
        $topConfig = [
            'type' => 'horizontalBar',
            'data' => [
                'labels' => $topLabels,
                'datasets' => [[
                    'label' => 'Total Donasi',
                    'data' => $topData,
                    'backgroundColor' => '#10B981'
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['x' => ['beginAtZero' => true]]
            ]
        ];
        $urls['topDonatur'] = $baseUrl . urlencode(json_encode($topConfig)) . '&w=500&h=300&bkg=white';
        
        return $urls;
    }

    /**
     * API endpoint for chart data (AJAX)
     */
    public function chartData(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        $kategori = $request->get('kategori', 'all');
        
        // Get chart data
        $charts = $this->getChartData($tahun, $tim, $cs, $kategori);
        
        // Get stats data for dynamic update
        $stats = $this->getStats($tahun, $tim, $cs, $kategori);
        
        return response()->json([
            'charts' => $charts,
            'stats' => $stats
        ]);
    }

    /**
     * API endpoint for stats only (AJAX) - used by header date range filter
     */
    public function statsData(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        $kategori = $request->get('kategori', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        $stats = $this->getStats($tahun, $tim, $cs, $kategori, $fromDate, $toDate);
        
        return response()->json(['stats' => $stats]);
    }

    /**
     * API endpoint for donatur list (AJAX pagination)
     */
    public function donaturList(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tim = $request->get('tim', 'all');
        $cs = $request->get('cs', 'all');
        $kategori = $request->get('kategori', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        // Handle 'all' tahun
        $tahunInt = ($tahun === 'all') ? (int) date('Y') : (int) $tahun;
        
        $query = DB::table('laporans')
            ->selectRaw('no_hp, MAX(nama_donatur) as nama_donatur, SUM(jml_perolehan) as total_donasi, COUNT(*) as jml_transaksi, MIN(tanggal) as first_donation, MAX(tanggal) as last_donation')
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        // Apply tim, cs & kategori filter
        if ($tim !== 'all') {
            $query->where('tim', $tim);
        }
        if ($cs !== 'all') {
            $query->where('nama_cs', $cs);
        }
        if ($kategori !== 'all') {
            $query->where('hasil_dari', $kategori);
        }

        // Filter by status
        switch ($status) {
            case 'baru':
                $donaturTahunLalu = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt)
                    ->whereNotIn('no_hp', $donaturTahunLalu);
                break;

            case 'hilang':
                $donaturTahunIni = DB::table('laporans')
                    ->whereYear('tanggal', $tahunInt)
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->distinct()
                    ->pluck('no_hp');
                $query->whereYear('tanggal', $tahunInt - 1)
                    ->whereNotIn('no_hp', $donaturTahunIni);
                break;

            case 'tidak_aktif':
                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')]);
                break;

            case 'aktif':
                $query->havingRaw('MAX(tanggal) >= ?', [now()->subDays(30)->format('Y-m-d')]);
                break;

            default:
                // Hanya terapkan filter tahun jika TIDAK ada date range
                if ($tahun && $tahun != 'all' && !$fromDate && !$toDate) {
                    $query->whereYear('tanggal', $tahunInt);
                }
                break;
        }

        // Filter by date range (prioritas lebih tinggi dari filter tahun)
        if ($fromDate) {
            $query->whereDate('tanggal', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('tanggal', '<=', $toDate);
        }

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_hp', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%");
            });
        }

        $result = $query->groupBy('no_hp')
            ->orderByDesc('total_donasi')
            ->paginate($perPage, ['*'], 'page', $page);

        // Format data for JSON response
        $data = collect($result->items())->map(function($item, $index) use ($result) {
            return [
                'no' => $result->firstItem() + $index,
                'nama_donatur' => $item->nama_donatur ?? '-',
                'no_hp' => $item->no_hp,
                'total_donasi' => $item->total_donasi,
                'jml_transaksi' => $item->jml_transaksi,
                'first_donation' => \Carbon\Carbon::parse($item->first_donation)->format('d M Y'),
                'last_donation' => \Carbon\Carbon::parse($item->last_donation)->format('d M Y'),
                'wa_link' => 'https://wa.me/' . preg_replace('/^0/', '62', $item->no_hp),
                'initial' => strtoupper(substr($item->nama_donatur ?? 'N', 0, 1)),
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
            'first_item' => $result->firstItem() ?? 0,
            'last_item' => $result->lastItem() ?? 0,
        ]);
    }
}
