<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\CustomerService;
use App\Models\DonaturNote;
use App\Models\DonaturActivityLog;
use App\Models\LaporanPerolehan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonaturCrmController extends Controller
{
    // ============================================
    // VIEW METHODS
    // ============================================

    /**
     * Display the main Donatur CRM page
     */
    public function index(Request $request)
    {
        // Get list of CS for dropdown (from customer_services table)
        $csList = CustomerService::orderBy('name')
            ->pluck('name')
            ->values();
        
        // Get list of kategori donatur
        $kategoriList = DB::table('donaturs')
            ->whereNotNull('kat_donatur')
            ->where('kat_donatur', '!=', '')
            ->distinct()
            ->pluck('kat_donatur')
            ->sort()
            ->values();

        // Get list of kode_donatur
        $kodeDonaturList = DB::table('donaturs')
            ->whereNotNull('kode_donatur')
            ->where('kode_donatur', '!=', '')
            ->distinct()
            ->pluck('kode_donatur')
            ->sort()
            ->values();

        return view('donatur.index', compact(
            'csList',
            'kategoriList',
            'kodeDonaturList'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return redirect()->route('donatur.index', ['action' => 'create']);
    }

    /**
     * Show single donatur detail
     */
    public function show($id)
    {
        return redirect()->route('donatur.index', ['id' => $id]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        return redirect()->route('donatur.index', ['id' => $id, 'action' => 'edit']);
    }

    // ============================================
    // API METHODS - CRUD
    // ============================================

    /**
     * Get paginated list of donatur with filters
     */
    public function apiIndex(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        $search = $request->get('search', '');
        $kategori = $request->get('kat_donatur', '');
        $cs = $request->get('nama_cs', '');
        $segment = $request->get('segment', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Build base query - no JOIN for pagination
        $query = Donatur::query();

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_donatur', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('did', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($kategori) {
            $query->where('kat_donatur', $kategori);
        }

        // Apply CS filter
        if ($cs) {
            $query->where('nama_cs', $cs);
        }

        // Apply segment filter
        if ($segment) {
            $phones = $this->getSegmentPhones($segment);
            $query->whereIn('no_hp', $phones);
        }

        // Apply sorting (only direct column sorting, metrics sorted after fetch)
        $validSortColumns = ['created_at', 'nama_donatur', 'tanggal_registrasi'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get paginated results
        $result = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Get phone numbers from current page
        $phones = collect($result->items())->pluck('no_hp')->filter()->unique()->toArray();
        
        // Get metrics for all phones in one query
        $metrics = DB::table('laporans')
            ->whereIn('no_hp', $phones)
            ->groupBy('no_hp')
            ->selectRaw('
                no_hp,
                COALESCE(SUM(jml_perolehan), 0) as lifetime_value,
                COALESCE(COUNT(id), 0) as frequency,
                MAX(tanggal) as last_donation,
                MIN(tanggal) as first_donation
            ')
            ->get()
            ->keyBy('no_hp');

        // Format data with engagement score
        $data = collect($result->items())->map(function($item) use ($metrics) {
            $m = $metrics[$item->no_hp] ?? null;
            $item->lifetime_value = $m->lifetime_value ?? 0;
            $item->frequency = $m->frequency ?? 0;
            $item->last_donation = $m->last_donation ?? null;
            $item->first_donation = $m->first_donation ?? null;
            return $this->formatDonaturData($item);
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

    /**
     * Get single donatur detail with full metrics
     */
    public function apiShow($id)
    {
        // First get the donatur
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }
        
        // Get metrics separately
        $metrics = DB::table('laporans')
            ->where('no_hp', $donatur->no_hp)
            ->selectRaw('
                COALESCE(SUM(jml_perolehan), 0) as lifetime_value,
                COALESCE(COUNT(id), 0) as frequency,
                MAX(tanggal) as last_donation,
                MIN(tanggal) as first_donation,
                COALESCE(AVG(jml_perolehan), 0) as avg_donation
            ')
            ->first();
        
        // Merge metrics to donatur
        $donatur->lifetime_value = $metrics->lifetime_value ?? 0;
        $donatur->frequency = $metrics->frequency ?? 0;
        $donatur->last_donation = $metrics->last_donation;
        $donatur->first_donation = $metrics->first_donation;
        $donatur->avg_donation = $metrics->avg_donation ?? 0;

        // Log view activity (optional - can be disabled if too noisy)
        // DonaturActivityLog::log($id, 'viewed', 'Detail donatur dilihat');

        return response()->json($this->formatDonaturData($donatur, true));
    }

    /**
     * Store new donatur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_cs' => 'nullable|string|max:255',
            'kat_donatur' => 'nullable|string|max:50',
            'kode_donatur' => 'nullable|string|max:50',
            'kode_negara' => 'nullable|string|max:10',
            'no_hp' => 'required|string|max:20',
            'tanggal_registrasi' => 'required|date',
            'nama_donatur' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'sosmed_account' => 'nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'channel' => 'nullable|string|max:255',
            'fundraiser' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Set default values for fields that cannot be null in database
        $validated['kat_donatur'] = $validated['kat_donatur'] ?? '';
        $validated['kode_donatur'] = $validated['kode_donatur'] ?? '';
        $validated['kode_negara'] = $validated['kode_negara'] ?? '';

        // Check for duplicate no_hp
        $existing = Donatur::where('no_hp', $validated['no_hp'])->first();
        if ($existing) {
            return response()->json([
                'error' => 'No HP sudah terdaftar',
                'existing' => [
                    'id' => $existing->id,
                    'nama' => $existing->nama_donatur,
                    'did' => $existing->did
                ]
            ], 422);
        }

        $donatur = Donatur::create($validated);

        // Reload to get DID (auto-generated)
        $donatur->refresh();

        // Log activity
        DonaturActivityLog::log(
            $donatur->id,
            'created',
            'Donatur baru ditambahkan: ' . $donatur->nama_donatur,
            ['did' => $donatur->did, 'no_hp' => $donatur->no_hp]
        );

        return response()->json([
            'message' => 'Donatur berhasil ditambahkan',
            'data' => $donatur
        ], 201);
    }

    /**
     * Update existing donatur
     */
    public function update(Request $request, $id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama_cs' => 'nullable|string|max:255',
            'kat_donatur' => 'nullable|string|max:50',
            'kode_donatur' => 'nullable|string|max:50',
            'kode_negara' => 'nullable|string|max:10',
            'no_hp' => 'required|string|max:20',
            'tanggal_registrasi' => 'required|date',
            'nama_donatur' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'sosmed_account' => 'nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'channel' => 'nullable|string|max:255',
            'fundraiser' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Set default values for fields that cannot be null in database
        $validated['kat_donatur'] = $validated['kat_donatur'] ?? '';
        $validated['kode_donatur'] = $validated['kode_donatur'] ?? '';
        $validated['kode_negara'] = $validated['kode_negara'] ?? '';

        // Check for duplicate no_hp (exclude current donatur)
        $existing = Donatur::where('no_hp', $validated['no_hp'])
            ->where('id', '!=', $id)
            ->first();
        if ($existing) {
            return response()->json([
                'error' => 'No HP sudah digunakan donatur lain',
                'existing' => [
                    'id' => $existing->id,
                    'nama' => $existing->nama_donatur,
                    'did' => $existing->did
                ]
            ], 422);
        }

        // Store old data for logging
        $oldData = $donatur->toArray();

        $donatur->update($validated);

        // Log activity
        DonaturActivityLog::log(
            $donatur->id,
            'updated',
            'Data donatur diupdate',
            ['old' => $oldData, 'new' => $validated]
        );

        return response()->json([
            'message' => 'Donatur berhasil diupdate',
            'data' => $donatur
        ]);
    }

    /**
     * Delete donatur
     */
    public function destroy($id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $donatur->delete();

        return response()->json([
            'message' => 'Donatur berhasil dihapus'
        ]);
    }

    // ============================================
    // API METHODS - CRM STATS & SEGMENTS
    // ============================================

    /**
     * Get CRM dashboard stats
     */
    public function stats(Request $request)
    {
        // Total donatur di database donaturs
        $totalDonatur = Donatur::count();

        // Donatur baru bulan ini (registrasi)
        $donaturBaru = Donatur::whereMonth('tanggal_registrasi', now()->month)
            ->whereYear('tanggal_registrasi', now()->year)
            ->count();

        // Donatur aktif (donasi dalam 30 hari terakhir)
        $donaturAktif = DB::table('donaturs')
            ->join('laporans', 'donaturs.no_hp', '=', 'laporans.no_hp')
            ->where('laporans.tanggal', '>=', now()->subDays(30))
            ->distinct('donaturs.id')
            ->count('donaturs.id');

        // At Risk (donasi terakhir 60-90 hari lalu)
        $atRiskPhones = $this->getSegmentPhones('at_risk');
        $atRisk = count($atRiskPhones);

        // Churned (donasi terakhir >90 hari lalu)
        $churnedPhones = $this->getSegmentPhones('churned');
        $churned = count($churnedPhones);

        // VIP (total >=10jt OR frequency >=10)
        $vipPhones = $this->getSegmentPhones('vip');
        $vip = count($vipPhones);

        // Growth bulan ini vs bulan lalu
        $donaturBaruBulanLalu = Donatur::whereMonth('tanggal_registrasi', now()->subMonth()->month)
            ->whereYear('tanggal_registrasi', now()->subMonth()->year)
            ->count();
        
        $growthRate = $donaturBaruBulanLalu > 0 
            ? round((($donaturBaru - $donaturBaruBulanLalu) / $donaturBaruBulanLalu) * 100, 1)
            : ($donaturBaru > 0 ? 100 : 0);

        return response()->json([
            'total_donatur' => $totalDonatur,
            'donatur_baru' => $donaturBaru,
            'donatur_aktif' => $donaturAktif,
            'at_risk' => $atRisk,
            'churned' => $churned,
            'vip' => $vip,
            'growth_rate' => $growthRate,
        ]);
    }

    /**
     * Get segment counts
     */
    public function segments(Request $request)
    {
        $segments = [
            'vip' => [
                'name' => 'VIP',
                'description' => 'Total ≥10jt atau ≥10 transaksi',
                'count' => count($this->getSegmentPhones('vip')),
                'color' => 'yellow',
                'icon' => 'star',
            ],
            'loyal' => [
                'name' => 'Loyal',
                'description' => '≥3 transaksi dalam 6 bulan',
                'count' => count($this->getSegmentPhones('loyal')),
                'color' => 'green',
                'icon' => 'heart',
            ],
            'new' => [
                'name' => 'Baru',
                'description' => 'Registrasi <30 hari',
                'count' => count($this->getSegmentPhones('new')),
                'color' => 'blue',
                'icon' => 'sparkles',
            ],
            'one_time' => [
                'name' => 'One-Time',
                'description' => 'Hanya 1 transaksi',
                'count' => count($this->getSegmentPhones('one_time')),
                'color' => 'gray',
                'icon' => 'user',
            ],
            'at_risk' => [
                'name' => 'At Risk',
                'description' => 'Tidak donasi 60-90 hari',
                'count' => count($this->getSegmentPhones('at_risk')),
                'color' => 'orange',
                'icon' => 'exclamation-triangle',
            ],
            'churned' => [
                'name' => 'Churned',
                'description' => 'Tidak donasi >90 hari',
                'count' => count($this->getSegmentPhones('churned')),
                'color' => 'red',
                'icon' => 'x-circle',
            ],
            'never_donated' => [
                'name' => 'Belum Donasi',
                'description' => 'Tidak ada record donasi',
                'count' => count($this->getSegmentPhones('never_donated')),
                'color' => 'slate',
                'icon' => 'question-mark-circle',
            ],
        ];

        return response()->json($segments);
    }

    // ============================================
    // API METHODS - HISTORY & NOTES
    // ============================================

    /**
     * Get donation history for a donatur
     */
    public function history($id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $history = DB::table('laporans')
            ->where('no_hp', $donatur->no_hp)
            ->orderBy('tanggal', 'desc')
            ->select(
                'id',
                'tanggal',
                'jml_perolehan',
                'nama_cs',
                'tim',
                'hasil_dari',
                'program_utama',
                'nama_produk',
                'channel',
                'keterangan'
            )
            ->limit(100)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => Carbon::parse($item->tanggal)->format('d M Y'),
                    'tanggal_raw' => $item->tanggal,
                    'jumlah' => $item->jml_perolehan,
                    'jumlah_formatted' => 'Rp ' . number_format($item->jml_perolehan, 0, ',', '.'),
                    'cs' => $item->nama_cs,
                    'tim' => $item->tim,
                    'kategori' => $item->hasil_dari,
                    'program' => $item->program_utama,
                    'produk' => $item->nama_produk,
                    'channel' => $item->channel,
                    'keterangan' => $item->keterangan,
                ];
            });

        return response()->json([
            'donatur_id' => $id,
            'donatur_name' => $donatur->nama_donatur,
            'total_records' => $history->count(),
            'data' => $history
        ]);
    }

    /**
     * Add note to donatur
     */
    public function addNote(Request $request, $id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        // Create note
        $note = DonaturNote::create([
            'donatur_id' => $id,
            'user_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        // Log activity
        DonaturActivityLog::log(
            $id,
            'note_added',
            'Catatan baru ditambahkan',
            ['note_id' => $note->id, 'preview' => substr($validated['note'], 0, 50)]
        );

        return response()->json([
            'message' => 'Catatan berhasil ditambahkan',
            'data' => [
                'id' => $note->id,
                'note' => $note->note,
                'created_at' => $note->created_at->format('d M Y H:i'),
                'user' => $note->user ? $note->user->name : 'System'
            ]
        ], 201);
    }

    /**
     * Get notes for a donatur
     */
    public function getNotes($id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $notes = DonaturNote::where('donatur_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'created_at' => $note->created_at->format('d M Y H:i'),
                    'user' => $note->user ? $note->user->name : 'System'
                ];
            });

        return response()->json([
            'donatur_id' => $id,
            'total' => $notes->count(),
            'data' => $notes
        ]);
    }

    /**
     * Get activity logs for a donatur
     */
    public function getActivityLogs($id)
    {
        $donatur = Donatur::find($id);
        
        if (!$donatur) {
            return response()->json(['error' => 'Donatur tidak ditemukan'], 404);
        }

        $logs = DonaturActivityLog::where('donatur_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $log->created_at->format('d M Y H:i'),
                    'user' => $log->user ? $log->user->name : 'System',
                    'icon' => $this->getActivityIcon($log->action)
                ];
            });

        return response()->json([
            'donatur_id' => $id,
            'total' => $logs->count(),
            'data' => $logs
        ]);
    }

    /**
     * Get icon for activity type
     */
    private function getActivityIcon($action)
    {
        $icons = [
            'created' => 'bi-plus-circle',
            'updated' => 'bi-pencil',
            'viewed' => 'bi-eye',
            'note_added' => 'bi-chat-dots',
            'deleted' => 'bi-trash',
        ];
        return $icons[$action] ?? 'bi-circle';
    }

    // ============================================
    // API METHODS - SMART ALERTS
    // ============================================

    /**
     * Get smart alerts for the dashboard
     */
    public function alerts()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        // Alert 1: VIP at Risk (VIP tidak donasi >30 hari)
        $vipPhones = DB::table('laporans')
            ->select('no_hp')
            ->groupBy('no_hp')
            ->havingRaw('SUM(jml_perolehan) >= 10000000 OR COUNT(*) >= 10')
            ->pluck('no_hp');

        $vipAtRiskCount = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereIn('d.no_hp', $vipPhones)
            ->where('l.last_donation', '<', $thirtyDaysAgo)
            ->count();

        // Alert 2: At Risk (last donation 60-90 hari)
        $atRiskCount = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereNotNull('l.last_donation')
            ->whereBetween('l.last_donation', [$ninetyDaysAgo, $sixtyDaysAgo])
            ->count();

        // Alert 3: New donors (registrasi <7 hari)
        $newDonorsCount = DB::table('donaturs')
            ->where('tanggal_registrasi', '>=', $sevenDaysAgo)
            ->count();

        $totalAlerts = $vipAtRiskCount + $atRiskCount + $newDonorsCount;

        return response()->json([
            'total' => $totalAlerts,
            'alerts' => [
                [
                    'id' => 'vip_at_risk',
                    'label' => 'VIP At Risk',
                    'description' => 'VIP tidak donasi >30 hari',
                    'count' => $vipAtRiskCount,
                    'color' => 'red',
                    'icon' => 'bi-exclamation-triangle',
                    'priority' => 'high',
                    'action' => 'filter_segment',
                    'action_value' => 'vip'
                ],
                [
                    'id' => 'at_risk',
                    'label' => 'At Risk',
                    'description' => 'Perlu follow-up segera',
                    'count' => $atRiskCount,
                    'color' => 'orange',
                    'icon' => 'bi-clock-history',
                    'priority' => 'medium',
                    'action' => 'filter_segment',
                    'action_value' => 'at_risk'
                ],
                [
                    'id' => 'new_welcome',
                    'label' => 'New Welcome',
                    'description' => 'Donatur baru perlu di-welcome',
                    'count' => $newDonorsCount,
                    'color' => 'yellow',
                    'icon' => 'bi-person-plus',
                    'priority' => 'low',
                    'action' => 'filter_segment',
                    'action_value' => 'new'
                ]
            ]
        ]);
    }

    // ============================================
    // API METHODS - FOLLOW-UP CENTER
    // ============================================

    /**
     * Get follow-up tasks for today
     */
    public function followUpTasks()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        
        // Get VIP phones
        $vipPhones = DB::table('laporans')
            ->select('no_hp')
            ->groupBy('no_hp')
            ->havingRaw('SUM(jml_perolehan) >= 10000000 OR COUNT(*) >= 10')
            ->pluck('no_hp');

        // Count VIP at risk
        $vipAtRiskCount = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereIn('d.no_hp', $vipPhones)
            ->where('l.last_donation', '<', $thirtyDaysAgo)
            ->count();

        // Count At Risk
        $atRiskCount = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereNotNull('l.last_donation')
            ->whereBetween('l.last_donation', [$ninetyDaysAgo, $sixtyDaysAgo])
            ->count();

        // Count New donors
        $newDonorsCount = DB::table('donaturs')
            ->where('tanggal_registrasi', '>=', $sevenDaysAgo)
            ->count();
        
        // Get actual data (limited for display)
        $vipAtRisk = $this->getVipAtRiskList(10);
        $atRiskList = $this->getAtRiskList(10);
        $newDonors = $this->getNewDonorsList(10);

        return response()->json([
            'high_priority' => [
                'label' => 'VIP At Risk',
                'description' => 'VIP belum donasi >30 hari',
                'count' => $vipAtRiskCount,
                'color' => 'red',
                'data' => $vipAtRisk
            ],
            'medium_priority' => [
                'label' => 'At Risk',
                'description' => 'Donatur perlu follow-up',
                'count' => $atRiskCount,
                'color' => 'orange',
                'data' => $atRiskList
            ],
            'low_priority' => [
                'label' => 'New Welcome',
                'description' => 'Donatur baru perlu di-welcome',
                'count' => $newDonorsCount,
                'color' => 'yellow',
                'data' => $newDonors
            ],
            'total_tasks' => $vipAtRiskCount + $atRiskCount + $newDonorsCount
        ]);
    }

    /**
     * Get VIP donors who haven't donated in >30 days
     */
    private function getVipAtRiskList($limit = 10)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        // Get VIP phones (total >=10jt OR trx >=10)
        $vipPhones = DB::table('laporans')
            ->select('no_hp')
            ->groupBy('no_hp')
            ->havingRaw('SUM(jml_perolehan) >= 10000000 OR COUNT(*) >= 10')
            ->pluck('no_hp');

        // Get VIP with last donation >30 days
        $vipAtRisk = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation, SUM(jml_perolehan) as lifetime_value, COUNT(*) as frequency FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereIn('d.no_hp', $vipPhones)
            ->where('l.last_donation', '<', $thirtyDaysAgo)
            ->select(
                'd.id',
                'd.nama_donatur',
                'd.no_hp',
                'd.nama_cs',
                'd.kat_donatur',
                'l.last_donation',
                'l.lifetime_value',
                'l.frequency'
            )
            ->orderBy('l.lifetime_value', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $daysSince = (int) Carbon::parse($item->last_donation)->diffInDays(Carbon::now());
                return [
                    'id' => $item->id,
                    'nama' => $item->nama_donatur,
                    'no_hp' => $item->no_hp,
                    'nama_cs' => $item->nama_cs,
                    'kategori' => $item->kat_donatur,
                    'last_donation' => Carbon::parse($item->last_donation)->format('d M Y'),
                    'days_since' => $daysSince,
                    'lifetime_value' => $item->lifetime_value,
                    'lifetime_value_formatted' => 'Rp ' . number_format($item->lifetime_value, 0, ',', '.'),
                    'frequency' => $item->frequency,
                    'priority' => 'high',
                    'reason' => "VIP tidak donasi {$daysSince} hari",
                    'wa_link' => 'https://wa.me/62' . ltrim($item->no_hp, '0')
                ];
            });

        return $vipAtRisk->toArray();
    }

    /**
     * Get At Risk donors (last donation 60-90 days)
     */
    private function getAtRiskList($limit = 10)
    {
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        $atRisk = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, MAX(tanggal) as last_donation, SUM(jml_perolehan) as lifetime_value, COUNT(*) as frequency FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->whereNotNull('l.last_donation')
            ->whereBetween('l.last_donation', [$ninetyDaysAgo, $sixtyDaysAgo])
            ->select(
                'd.id',
                'd.nama_donatur',
                'd.no_hp',
                'd.nama_cs',
                'd.kat_donatur',
                'l.last_donation',
                'l.lifetime_value',
                'l.frequency'
            )
            ->orderBy('l.last_donation', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $daysSince = (int) Carbon::parse($item->last_donation)->diffInDays(Carbon::now());
                return [
                    'id' => $item->id,
                    'nama' => $item->nama_donatur,
                    'no_hp' => $item->no_hp,
                    'nama_cs' => $item->nama_cs,
                    'kategori' => $item->kat_donatur,
                    'last_donation' => Carbon::parse($item->last_donation)->format('d M Y'),
                    'days_since' => $daysSince,
                    'lifetime_value' => $item->lifetime_value,
                    'lifetime_value_formatted' => 'Rp ' . number_format($item->lifetime_value ?? 0, 0, ',', '.'),
                    'frequency' => $item->frequency ?? 0,
                    'priority' => 'medium',
                    'reason' => "Tidak donasi {$daysSince} hari",
                    'wa_link' => 'https://wa.me/62' . ltrim($item->no_hp, '0')
                ];
            });

        return $atRisk->toArray();
    }

    /**
     * Get new donors for welcome (registered <7 days)
     */
    private function getNewDonorsList($limit = 10)
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $newDonors = DB::table('donaturs as d')
            ->leftJoin(DB::raw('(SELECT no_hp, SUM(jml_perolehan) as lifetime_value, COUNT(*) as frequency FROM laporans GROUP BY no_hp) as l'), 'd.no_hp', '=', 'l.no_hp')
            ->where('d.tanggal_registrasi', '>=', $sevenDaysAgo)
            ->select(
                'd.id',
                'd.nama_donatur',
                'd.no_hp',
                'd.nama_cs',
                'd.kat_donatur',
                'd.tanggal_registrasi',
                'l.lifetime_value',
                'l.frequency'
            )
            ->orderBy('d.tanggal_registrasi', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $daysSince = (int) Carbon::parse($item->tanggal_registrasi)->diffInDays(Carbon::now());
                return [
                    'id' => $item->id,
                    'nama' => $item->nama_donatur,
                    'no_hp' => $item->no_hp,
                    'nama_cs' => $item->nama_cs,
                    'kategori' => $item->kat_donatur,
                    'registered' => Carbon::parse($item->tanggal_registrasi)->format('d M Y'),
                    'days_since' => $daysSince,
                    'lifetime_value' => $item->lifetime_value ?? 0,
                    'lifetime_value_formatted' => 'Rp ' . number_format($item->lifetime_value ?? 0, 0, ',', '.'),
                    'frequency' => $item->frequency ?? 0,
                    'priority' => 'low',
                    'reason' => "Donatur baru ({$daysSince} hari)",
                    'wa_link' => 'https://wa.me/62' . ltrim($item->no_hp, '0')
                ];
            });

        return $newDonors->toArray();
    }

    // ============================================
    // API METHODS - UTILITY
    // ============================================

    /**
     * Check if phone number exists
     */
    public function checkPhone($phone)
    {
        $donatur = Donatur::where('no_hp', $phone)->first();

        if ($donatur) {
            return response()->json([
                'exists' => true,
                'data' => [
                    'id' => $donatur->id,
                    'nama' => $donatur->nama_donatur,
                    'did' => $donatur->did,
                    'tanggal_registrasi' => $donatur->tanggal_registrasi
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    // ============================================
    // API METHODS - BULK ACTIONS
    // ============================================

    /**
     * Bulk delete donatur
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->get('ids', []);
        
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada donatur yang dipilih'], 422);
        }

        $deleted = Donatur::whereIn('id', $ids)->delete();

        return response()->json([
            'message' => "{$deleted} donatur berhasil dihapus",
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Bulk assign CS to donatur
     */
    public function bulkAssign(Request $request)
    {
        $ids = $request->get('ids', []);
        $namaCs = $request->get('nama_cs', '');
        
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada donatur yang dipilih'], 422);
        }

        if (empty($namaCs)) {
            return response()->json(['error' => 'Nama CS harus diisi'], 422);
        }

        $updated = Donatur::whereIn('id', $ids)->update(['nama_cs' => $namaCs]);

        return response()->json([
            'message' => "{$updated} donatur berhasil di-assign ke {$namaCs}",
            'updated_count' => $updated
        ]);
    }

    /**
     * Export donatur to Excel
     */
    public function export(Request $request)
    {
        $ids = $request->get('ids', []);
        $search = $request->get('search', '');
        $kategori = $request->get('kat_donatur', '');
        $cs = $request->get('nama_cs', '');
        $segment = $request->get('segment', '');

        // Build base query for donaturs
        $query = Donatur::query();

        // If specific IDs selected
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            // Apply filters
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_donatur', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
                });
            }
            if ($kategori) {
                $query->where('kat_donatur', $kategori);
            }
            if ($cs) {
                $query->where('nama_cs', $cs);
            }
            if ($segment) {
                $phones = $this->getSegmentPhones($segment);
                $query->whereIn('no_hp', $phones);
            }
        }

        $donaturs = $query->limit(10000)->get();
        
        // Get all unique phone numbers
        $phones = $donaturs->pluck('no_hp')->filter()->unique()->toArray();
        
        // Get metrics for all phones in one query
        $metrics = DB::table('laporans')
            ->whereIn('no_hp', $phones)
            ->groupBy('no_hp')
            ->selectRaw('
                no_hp,
                COALESCE(SUM(jml_perolehan), 0) as lifetime_value,
                COALESCE(COUNT(id), 0) as frequency,
                MAX(tanggal) as last_donation
            ')
            ->get()
            ->keyBy('no_hp');

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['No', 'DID', 'Nama Donatur', 'No HP', 'Email', 'Kategori', 'CS', 'Tanggal Registrasi', 'Lifetime Value', 'Frequency', 'Last Donation', 'Alamat'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $header);
        }
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($donaturs as $i => $d) {
            $m = $metrics[$d->no_hp] ?? null;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $d->did ?? '-');
            $sheet->setCellValue("C{$row}", $d->nama_donatur ?? '-');
            $sheet->setCellValueExplicit("D{$row}", $d->no_hp, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", $d->email ?? '-');
            $sheet->setCellValue("F{$row}", $d->kat_donatur ?? '-');
            $sheet->setCellValue("G{$row}", $d->nama_cs ?? '-');
            $sheet->setCellValue("H{$row}", $d->tanggal_registrasi ?? '-');
            $sheet->setCellValue("I{$row}", $m->lifetime_value ?? 0);
            $sheet->setCellValue("J{$row}", $m->frequency ?? 0);
            $sheet->setCellValue("K{$row}", $m->last_donation ?? '-');
            $sheet->setCellValue("L{$row}", $d->alamat ?? '-');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "donatur_export_" . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path("app/public/{$fileName}");
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get list of phone numbers for a segment
     */
    private function getSegmentPhones($segment)
    {
        switch ($segment) {
            case 'vip':
                // Total >=10jt OR frequency >=10
                return DB::table('laporans')
                    ->select('no_hp')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->groupBy('no_hp')
                    ->havingRaw('SUM(jml_perolehan) >= 10000000 OR COUNT(*) >= 10')
                    ->pluck('no_hp')
                    ->toArray();

            case 'loyal':
                // >=3 transactions in last 6 months
                return DB::table('laporans')
                    ->select('no_hp')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->where('tanggal', '>=', now()->subMonths(6))
                    ->groupBy('no_hp')
                    ->havingRaw('COUNT(*) >= 3')
                    ->pluck('no_hp')
                    ->toArray();

            case 'new':
                // Registered <30 days ago
                return Donatur::where('tanggal_registrasi', '>=', now()->subDays(30))
                    ->pluck('no_hp')
                    ->toArray();

            case 'one_time':
                // Exactly 1 transaction
                return DB::table('laporans')
                    ->select('no_hp')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->groupBy('no_hp')
                    ->havingRaw('COUNT(*) = 1')
                    ->pluck('no_hp')
                    ->toArray();

            case 'at_risk':
                // Last donation 60-90 days ago
                return DB::table('laporans')
                    ->select('no_hp')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->groupBy('no_hp')
                    ->havingRaw('MAX(tanggal) BETWEEN ? AND ?', [
                        now()->subDays(90)->format('Y-m-d'),
                        now()->subDays(60)->format('Y-m-d')
                    ])
                    ->pluck('no_hp')
                    ->toArray();

            case 'churned':
                // Last donation >90 days ago
                return DB::table('laporans')
                    ->select('no_hp')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->groupBy('no_hp')
                    ->havingRaw('MAX(tanggal) < ?', [now()->subDays(90)->format('Y-m-d')])
                    ->pluck('no_hp')
                    ->toArray();

            case 'never_donated':
                // No record in laporans
                $donatedPhones = DB::table('laporans')
                    ->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->distinct()
                    ->pluck('no_hp')
                    ->toArray();
                
                return Donatur::whereNotIn('no_hp', $donatedPhones)
                    ->pluck('no_hp')
                    ->toArray();

            default:
                return [];
        }
    }

    /**
     * Apply segment filter to query
     */
    private function applySegmentFilter($query, $segment)
    {
        $phones = $this->getSegmentPhones($segment);
        
        if (!empty($phones)) {
            $query->whereIn('donaturs.no_hp', $phones);
        } else {
            // No results if segment is empty
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Calculate engagement score for a donatur
     */
    private function calculateEngagementScore($donatur)
    {
        // Recency Score (30%)
        $lastDonation = $donatur->last_donation ? Carbon::parse($donatur->last_donation) : null;
        $daysSinceLast = $lastDonation ? (int) now()->diffInDays($lastDonation) : 999;
        
        if ($daysSinceLast <= 7) $recencyScore = 100;
        elseif ($daysSinceLast <= 30) $recencyScore = 80;
        elseif ($daysSinceLast <= 60) $recencyScore = 60;
        elseif ($daysSinceLast <= 90) $recencyScore = 40;
        elseif ($daysSinceLast <= 180) $recencyScore = 20;
        else $recencyScore = 0;

        // Frequency Score (30%)
        $frequency = $donatur->frequency ?? 0;
        
        if ($frequency >= 10) $frequencyScore = 100;
        elseif ($frequency >= 7) $frequencyScore = 80;
        elseif ($frequency >= 4) $frequencyScore = 60;
        elseif ($frequency >= 2) $frequencyScore = 40;
        elseif ($frequency >= 1) $frequencyScore = 20;
        else $frequencyScore = 0;

        // Monetary Score (25%)
        $ltv = $donatur->lifetime_value ?? 0;
        
        if ($ltv >= 10000000) $monetaryScore = 100;
        elseif ($ltv >= 5000000) $monetaryScore = 80;
        elseif ($ltv >= 1000000) $monetaryScore = 60;
        elseif ($ltv >= 500000) $monetaryScore = 40;
        elseif ($ltv >= 100000) $monetaryScore = 20;
        else $monetaryScore = 0;

        // Tenure Score (15%)
        $registrationDate = $donatur->tanggal_registrasi ? Carbon::parse($donatur->tanggal_registrasi) : now();
        $tenureMonths = now()->diffInMonths($registrationDate);
        
        if ($tenureMonths >= 24) $tenureScore = 100;
        elseif ($tenureMonths >= 12) $tenureScore = 80;
        elseif ($tenureMonths >= 6) $tenureScore = 60;
        elseif ($tenureMonths >= 3) $tenureScore = 40;
        elseif ($tenureMonths >= 1) $tenureScore = 20;
        else $tenureScore = 0;

        // Weighted average
        $score = round(
            ($recencyScore * 0.30) +
            ($frequencyScore * 0.30) +
            ($monetaryScore * 0.25) +
            ($tenureScore * 0.15)
        );

        return [
            'score' => $score,
            'label' => $score >= 80 ? 'Hot' : ($score >= 50 ? 'Warm' : 'Cold'),
            'color' => $score >= 80 ? 'green' : ($score >= 50 ? 'yellow' : 'red'),
            'breakdown' => [
                'recency' => $recencyScore,
                'frequency' => $frequencyScore,
                'monetary' => $monetaryScore,
                'tenure' => $tenureScore,
            ]
        ];
    }

    /**
     * Determine segment for a donatur
     */
    private function determineSegment($donatur)
    {
        $ltv = $donatur->lifetime_value ?? 0;
        $frequency = $donatur->frequency ?? 0;
        $lastDonation = $donatur->last_donation ? Carbon::parse($donatur->last_donation) : null;
        $registrationDate = $donatur->tanggal_registrasi ? Carbon::parse($donatur->tanggal_registrasi) : null;
        
        // Check segments in priority order
        if ($ltv >= 10000000 || $frequency >= 10) {
            return ['key' => 'vip', 'name' => 'VIP', 'color' => 'yellow'];
        }

        if ($frequency == 0) {
            return ['key' => 'never_donated', 'name' => 'Belum Donasi', 'color' => 'slate'];
        }

        if ($registrationDate && (int) now()->diffInDays($registrationDate) < 30) {
            return ['key' => 'new', 'name' => 'Baru', 'color' => 'blue'];
        }

        if ($lastDonation) {
            $daysSince = (int) now()->diffInDays($lastDonation);
            
            if ($daysSince > 90) {
                return ['key' => 'churned', 'name' => 'Churned', 'color' => 'red'];
            }
            
            if ($daysSince >= 60) {
                return ['key' => 'at_risk', 'name' => 'At Risk', 'color' => 'orange'];
            }
        }

        // Check loyal (3+ transactions in 6 months)
        $recentFrequency = DB::table('laporans')
            ->where('no_hp', $donatur->no_hp)
            ->where('tanggal', '>=', now()->subMonths(6))
            ->count();
        
        if ($recentFrequency >= 3) {
            return ['key' => 'loyal', 'name' => 'Loyal', 'color' => 'green'];
        }

        if ($frequency == 1) {
            return ['key' => 'one_time', 'name' => 'One-Time', 'color' => 'gray'];
        }

        return ['key' => 'regular', 'name' => 'Regular', 'color' => 'slate'];
    }

    /**
     * Format donatur data for API response
     */
    private function formatDonaturData($donatur, $includeDetails = false)
    {
        $engagementScore = $this->calculateEngagementScore($donatur);
        $segment = $this->determineSegment($donatur);

        $data = [
            'id' => $donatur->id,
            'did' => $donatur->did,
            'nama_donatur' => $donatur->nama_donatur,
            'nama_panggilan' => $donatur->nama_panggilan,
            'no_hp' => $donatur->no_hp,
            'email' => $donatur->email,
            'kat_donatur' => $donatur->kat_donatur,
            'kode_donatur' => $donatur->kode_donatur,
            'nama_cs' => $donatur->nama_cs,
            'tanggal_registrasi' => $donatur->tanggal_registrasi,
            'tanggal_registrasi_formatted' => $donatur->tanggal_registrasi 
                ? Carbon::parse($donatur->tanggal_registrasi)->format('d M Y') 
                : '-',
            
            // Metrics
            'lifetime_value' => (int) $donatur->lifetime_value,
            'lifetime_value_formatted' => 'Rp ' . number_format($donatur->lifetime_value ?? 0, 0, ',', '.'),
            'frequency' => (int) $donatur->frequency,
            'last_donation' => $donatur->last_donation,
            'last_donation_formatted' => $donatur->last_donation 
                ? Carbon::parse($donatur->last_donation)->format('d M Y') 
                : 'Belum donasi',
            'first_donation' => $donatur->first_donation ?? null,
            'first_donation_formatted' => $donatur->first_donation 
                ? Carbon::parse($donatur->first_donation)->format('d M Y') 
                : '-',
            
            // CRM Data
            'engagement_score' => $engagementScore,
            'segment' => $segment,
            
            // UI Helpers
            'initial' => strtoupper(substr($donatur->nama_donatur ?? 'N', 0, 1)),
            'wa_link' => 'https://wa.me/' . preg_replace('/^0/', '62', $donatur->no_hp ?? ''),
        ];

        // Include additional details if requested
        if ($includeDetails) {
            $data['alamat'] = $donatur->alamat;
            $data['jenis_kelamin'] = $donatur->jenis_kelamin;
            $data['sosmed_account'] = $donatur->sosmed_account;
            $data['program'] = $donatur->program;
            $data['channel'] = $donatur->channel;
            $data['fundraiser'] = $donatur->fundraiser;
            $data['keterangan'] = $donatur->keterangan;
            $data['kode_negara'] = $donatur->kode_negara;
            $data['avg_donation'] = isset($donatur->avg_donation) 
                ? 'Rp ' . number_format($donatur->avg_donation, 0, ',', '.') 
                : '-';
            $data['created_at'] = $donatur->created_at;
            $data['updated_at'] = $donatur->updated_at;
        }

        return $data;
    }
}
