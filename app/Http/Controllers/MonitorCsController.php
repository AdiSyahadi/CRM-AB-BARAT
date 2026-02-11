<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerolehan;
use App\Models\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorCsController extends Controller
{
    /**
     * Display the CS Monitor dashboard
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $selectedDate = $request->get('tanggal', $today->format('Y-m-d'));
        
        // Get list of Tim for filter
        $timList = CustomerService::distinct()->orderBy('team')->pluck('team')->filter()->toArray();
        
        // Initial data for the view
        $initialSummary = $this->getCsStatusSummary($selectedDate);
        $initialCsList = $this->getCsListStatus($selectedDate);
        $initialTimeline = $this->getActivityTimeline($selectedDate, 20);
        
        return view('monitor-cs.index', compact(
            'timList',
            'selectedDate',
            'initialSummary',
            'initialCsList',
            'initialTimeline'
        ));
    }
    
    /**
     * API: Get CS status summary
     */
    public function apiCsStatusSummary(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        return response()->json($this->getCsStatusSummary($date));
    }
    
    /**
     * API: Get CS list with status
     */
    public function apiCsListStatus(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $tim = $request->get('tim', 'all');
        $status = $request->get('status', 'all'); // all, sudah, belum
        $sort = $request->get('sort', 'status'); // status, perolehan, nama, tim
        
        return response()->json($this->getCsListStatus($date, $tim, $status, $sort));
    }
    
    /**
     * API: Get activity timeline
     */
    public function apiActivityTimeline(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $limit = $request->get('limit', 30);
        return response()->json($this->getActivityTimeline($date, $limit));
    }
    
    /**
     * API: Get CS detail
     */
    public function apiCsDetail(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $namaCs = $request->get('nama_cs');
        return response()->json($this->getCsDetail($date, $namaCs));
    }
    
    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================
    
    /**
     * Get CS status summary
     */
    private function getCsStatusSummary($date)
    {
        // Get all registered CS from customer_services table
        $allCs = CustomerService::select('name', 'team')->get();
        $totalCs = $allCs->count();
        
        // Get CS yang sudah laporan hari ini (dari table laporans)
        $csYangSudahLaporan = LaporanPerolehan::whereDate('tanggal', $date)
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct('nama_cs')
            ->pluck('nama_cs')
            ->toArray();
        
        $sudahLaporan = count($csYangSudahLaporan);
        
        // Hitung CS yang belum laporan
        // Cross-check dengan registered CS
        $registeredCsNames = $allCs->pluck('name')->toArray();
        
        // CS terdaftar yang sudah laporan
        $registeredSudahLaporan = array_intersect($registeredCsNames, $csYangSudahLaporan);
        
        // CS terdaftar yang belum laporan
        $registeredBelumLaporan = array_diff($registeredCsNames, $csYangSudahLaporan);
        
        // Ada juga CS yang laporan tapi tidak terdaftar (bisa jadi typo atau CS baru)
        $unregisteredCs = array_diff($csYangSudahLaporan, $registeredCsNames);
        
        $percentageSudah = $totalCs > 0 ? round((count($registeredSudahLaporan) / $totalCs) * 100, 1) : 0;
        $percentageBelum = $totalCs > 0 ? round((count($registeredBelumLaporan) / $totalCs) * 100, 1) : 0;
        
        // Summary per tim
        $perTim = [];
        $teamGroups = $allCs->groupBy('team');
        foreach ($teamGroups as $team => $members) {
            $teamCsNames = $members->pluck('name')->toArray();
            $teamSudah = count(array_intersect($teamCsNames, $csYangSudahLaporan));
            $teamBelum = count($teamCsNames) - $teamSudah;
            $perTim[] = [
                'tim' => $team ?: 'Tidak Ada Tim',
                'total' => count($teamCsNames),
                'sudah' => $teamSudah,
                'belum' => $teamBelum,
                'percentage' => count($teamCsNames) > 0 ? round(($teamSudah / count($teamCsNames)) * 100, 1) : 0,
            ];
        }
        
        // Sort per tim by percentage descending
        usort($perTim, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });
        
        return [
            'total_cs' => $totalCs,
            'sudah_laporan' => count($registeredSudahLaporan),
            'belum_laporan' => count($registeredBelumLaporan),
            'percentage_sudah' => $percentageSudah,
            'percentage_belum' => $percentageBelum,
            'unregistered_cs' => count($unregisteredCs),
            'unregistered_cs_list' => $unregisteredCs,
            'per_tim' => $perTim,
            'tanggal' => $date,
        ];
    }
    
    /**
     * Get CS list with status
     */
    private function getCsListStatus($date, $tim = 'all', $statusFilter = 'all', $sort = 'status')
    {
        // Get all registered CS
        $query = CustomerService::query();
        if ($tim !== 'all') {
            $query->where('team', $tim);
        }
        $allCs = $query->get();
        
        // Get laporan data untuk hari ini
        $laporanData = LaporanPerolehan::whereDate('tanggal', $date)
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->select(
                'nama_cs',
                'tim',
                DB::raw('SUM(jml_perolehan) as total_perolehan'),
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('MAX(created_at) as last_activity'),
                DB::raw('MIN(perolehan_jam) as first_jam'),
                DB::raw('MAX(perolehan_jam) as last_jam')
            )
            ->groupBy('nama_cs', 'tim')
            ->get()
            ->keyBy('nama_cs');
        
        $currentTime = Carbon::now();
        $result = [];
        
        foreach ($allCs as $cs) {
            $laporan = $laporanData->get($cs->name);
            $hasLaporan = $laporan !== null;
            
            // Hitung jam sejak aktivitas terakhir
            $lastActivity = $hasLaporan && $laporan->last_activity 
                ? Carbon::parse($laporan->last_activity) 
                : null;
            $hoursSinceActivity = $lastActivity 
                ? $currentTime->diffInHours($lastActivity) 
                : null;
            
            // Determine status
            // - 'sudah' = sudah ada laporan hari ini
            // - 'belum' = sama sekali belum ada laporan
            // - 'idle' = sudah laporan tapi sudah lama tidak ada aktivitas (>2 jam)
            $status = 'belum';
            $statusLabel = 'Belum Laporan';
            $statusColor = 'red';
            
            if ($hasLaporan) {
                if ($hoursSinceActivity !== null && $hoursSinceActivity >= 2) {
                    $status = 'idle';
                    $statusLabel = 'Idle ' . $hoursSinceActivity . ' jam';
                    $statusColor = 'yellow';
                } else {
                    $status = 'sudah';
                    $statusLabel = 'Aktif';
                    $statusColor = 'green';
                }
            }
            
            $result[] = [
                'id' => $cs->id,
                'nama_cs' => $cs->name,
                'tim' => $cs->team ?: '-',
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'total_transaksi' => $hasLaporan ? (int)$laporan->total_transaksi : 0,
                'total_perolehan' => $hasLaporan ? (int)$laporan->total_perolehan : 0,
                'first_jam' => $hasLaporan ? $laporan->first_jam : null,
                'last_jam' => $hasLaporan ? $laporan->last_jam : null,
                'last_activity' => $lastActivity ? $lastActivity->format('H:i') : null,
                'last_activity_ago' => $lastActivity ? $lastActivity->diffForHumans() : null,
                'hours_since_activity' => $hoursSinceActivity,
            ];
        }
        
        // Filter by status
        if ($statusFilter === 'sudah') {
            $result = array_filter($result, fn($item) => $item['status'] !== 'belum');
        } elseif ($statusFilter === 'belum') {
            $result = array_filter($result, fn($item) => $item['status'] === 'belum');
        } elseif ($statusFilter === 'idle') {
            $result = array_filter($result, fn($item) => $item['status'] === 'idle');
        }
        
        // Sort
        usort($result, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'perolehan':
                    return $b['total_perolehan'] <=> $a['total_perolehan'];
                case 'nama':
                    return strcasecmp($a['nama_cs'], $b['nama_cs']);
                case 'tim':
                    return strcasecmp($a['tim'], $b['tim']);
                case 'status':
                default:
                    // Belum first, then idle, then sudah
                    $statusOrder = ['belum' => 0, 'idle' => 1, 'sudah' => 2];
                    $orderA = $statusOrder[$a['status']] ?? 3;
                    $orderB = $statusOrder[$b['status']] ?? 3;
                    if ($orderA === $orderB) {
                        // Secondary sort by perolehan desc
                        return $b['total_perolehan'] <=> $a['total_perolehan'];
                    }
                    return $orderA <=> $orderB;
            }
        });
        
        return array_values($result);
    }
    
    /**
     * Get activity timeline
     */
    private function getActivityTimeline($date, $limit = 30)
    {
        $activities = LaporanPerolehan::whereDate('tanggal', $date)
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->select(
                'id',
                'nama_cs',
                'tim',
                'nama_donatur',
                'jml_perolehan',
                'perolehan_jam',
                'hasil_dari',
                'zakat',
                'created_at'
            )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->time_ago = Carbon::parse($item->created_at)->diffForHumans();
                $item->time_formatted = Carbon::parse($item->created_at)->format('H:i');
                return $item;
            });
        
        return $activities;
    }
    
    /**
     * Get CS detail
     */
    private function getCsDetail($date, $namaCs)
    {
        if (!$namaCs) {
            return ['error' => 'nama_cs is required'];
        }
        
        // Get CS info
        $cs = CustomerService::where('name', $namaCs)->first();
        
        // Get all laporan for this CS today
        $laporans = LaporanPerolehan::whereDate('tanggal', $date)
            ->where('nama_cs', $namaCs)
            ->select(
                'id',
                'perolehan_jam',
                'nama_donatur',
                'no_hp',
                'jml_perolehan',
                'hasil_dari',
                'zakat',
                'nama_produk',
                'nama_platform',
                'created_at'
            )
            ->orderBy('perolehan_jam')
            ->orderBy('created_at')
            ->get();
        
        // Summary
        $totalPerolehan = $laporans->sum('jml_perolehan');
        $totalTransaksi = $laporans->count();
        $avgPerTransaksi = $totalTransaksi > 0 ? round($totalPerolehan / $totalTransaksi) : 0;
        
        // Per jam breakdown
        $perJam = $laporans->groupBy('perolehan_jam')->map(function ($items, $jam) {
            return [
                'jam' => $jam,
                'transaksi' => $items->count(),
                'perolehan' => $items->sum('jml_perolehan'),
            ];
        })->values();
        
        return [
            'nama_cs' => $namaCs,
            'tim' => $cs->team ?? '-',
            'is_registered' => $cs !== null,
            'total_perolehan' => $totalPerolehan,
            'total_transaksi' => $totalTransaksi,
            'avg_per_transaksi' => $avgPerTransaksi,
            'per_jam' => $perJam,
            'laporans' => $laporans,
            'tanggal' => $date,
        ];
    }
}
