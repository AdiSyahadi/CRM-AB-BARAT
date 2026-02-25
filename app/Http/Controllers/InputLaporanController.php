<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LaporanPerolehan;
use App\Models\Donatur;
use Carbon\Carbon;

class InputLaporanController extends Controller
{
    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    /**
     * Main Input Laporan page — data table + form
     */
    public function index()
    {
        return view('input-laporan.index');
    }

    // ========================================================================
    // API ENDPOINTS
    // ========================================================================

    /**
     * Paginated list with search & filter
     */
    public function apiList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = $request->get('search', '');
        $tim     = $request->get('tim', 'all');
        $tanggal = $request->get('tanggal', '');
        $sort    = $request->get('sort', 'created_at');
        $order   = $request->get('order', 'desc');

        $allowed = ['tanggal', 'tim', 'nama_cs', 'jml_perolehan', 'created_at', 'hasil_dari'];
        if (!in_array($sort, $allowed)) $sort = 'created_at';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = LaporanPerolehan::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_cs', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%")
                  ->orWhere('did', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tim && $tim !== 'all') {
            $query->where('tim', $tim);
        }

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Dropdown options for the form
     */
    public function apiOptions()
    {
        $timList = DB::table('laporans')
            ->distinct()->whereNotNull('tim')->where('tim', '!=', '')
            ->orderBy('tim')->pluck('tim');

        $csNames = DB::table('laporans')
            ->select('nama_cs', 'tim')
            ->distinct()
            ->whereNotNull('nama_cs')->where('nama_cs', '!=', '')
            ->orderBy('nama_cs')
            ->get();

        return response()->json([
            'tim' => $timList,
            'cs_names' => $csNames,
            'perolehan_jam' => [
                '08:00-09:00 WIB', '09:00-10:00 WIB', '10:00-11:00 WIB',
                '11:00-12:00 WIB', '12:00-13:00 WIB', '13:00-14:00 WIB',
                '14:00-15:00 WIB', '15:00-16:00 WIB', '16:00-17:00 WIB',
                '17:00-24:00 WIB',
            ],
            'hasil_dari' => [
                'Cross Selling', 'Program Utama', 'Zakat', 'Platform',
                'Program Ramadhan', 'Wakaf', 'Infaq', 'Produk', 'Partnership',
            ],
            'prg_cross_selling' => [
                'Palestina', 'Produk', 'Program AB Barat', 'Program Cabang',
                'Qurban', 'Umroh/Haji', 'Wakaf AB Barat', 'Wakaf Cabang',
                'Program Ramadhan', 'Cross Selling',
            ],
            'followup_wa' => ['Harian', 'Jumat', 'Subuh'],
            'kat_donatur' => ['Retail', 'Community', 'Corporate'],
            'jenis_kelamin' => ['Laki-Laki', 'Perempuan'],
            'nama_bank' => ['Tidak Ada', 'Bank Syariah Indonesia'],
            'channel' => ['Tokopedia', 'Shopee'],
            'e_commerce' => ['Shopee', 'Tokopedia'],
            'nama_platform' => [
                'KITABISA', 'AMALSHOLEH', 'Bantu Bersama', 'SEDEKAH ONLINE',
                'AMAL SOLEH', 'KITA BISA', 'Sharing Happyness', 'DONASI ONLINE',
            ],
        ]);
    }

    /**
     * Store new laporan
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'       => 'required|date',
            'tim'           => 'required|string|max:100',
            'nama_cs'       => 'required|string|max:255',
            'perolehan_jam' => 'required|string|max:50',
        ], [
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'tim.required'           => 'Tim wajib dipilih.',
            'nama_cs.required'       => 'Nama CS wajib dipilih.',
            'perolehan_jam.required' => 'Jam perolehan wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $laporan = LaporanPerolehan::create($this->sanitizeInput($request->all()));

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan.',
            'data'    => $laporan,
        ], 201);
    }

    /**
     * Get single laporan for edit
     */
    public function apiShow($id)
    {
        $laporan = LaporanPerolehan::findOrFail($id);
        return response()->json(['data' => $laporan]);
    }

    /**
     * Update existing laporan
     */
    public function apiUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'       => 'required|date',
            'tim'           => 'required|string|max:100',
            'nama_cs'       => 'required|string|max:255',
            'perolehan_jam' => 'required|string|max:50',
        ], [
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'tim.required'           => 'Tim wajib dipilih.',
            'nama_cs.required'       => 'Nama CS wajib dipilih.',
            'perolehan_jam.required' => 'Jam perolehan wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $laporan = LaporanPerolehan::findOrFail($id);
        $laporan->update($this->sanitizeInput($request->all()));

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui.',
            'data'    => $laporan->fresh(),
        ]);
    }

    /**
     * Delete laporan
     */
    public function apiDelete($id)
    {
        $laporan = LaporanPerolehan::findOrFail($id);
        $laporan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }

    /**
     * Bulk delete
     */
    public function apiBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 422);
        }

        $count = LaporanPerolehan::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} laporan berhasil dihapus.",
        ]);
    }

    /**
     * Summary stats for the page header
     */
    public function apiStats(Request $request)
    {
        $tanggal = $request->get('tanggal', '');

        $query = DB::table('laporans');
        $prevQuery = DB::table('laporans');

        if ($tanggal) {
            // Filter by specific date — compare with previous day
            $query->whereDate('tanggal', $tanggal);
            $prevQuery->whereDate('tanggal', Carbon::parse($tanggal)->subDay()->toDateString());
        } else {
            // No date filter — show today vs yesterday for growth,
            // but total stats = all time
            $todayStr = Carbon::today()->toDateString();
            $prevQuery->whereDate('tanggal', Carbon::today()->subDay()->toDateString());
        }

        $stats = $query
            ->selectRaw('COUNT(*) as total_laporan, COALESCE(SUM(jml_perolehan),0) as total_perolehan, COALESCE(SUM(jml_database),0) as total_database, COUNT(DISTINCT nama_cs) as total_cs')
            ->first();

        $prev = $prevQuery
            ->selectRaw('COUNT(*) as total_laporan, COALESCE(SUM(jml_perolehan),0) as total_perolehan')
            ->first();

        return response()->json([
            'total_laporan'     => (int) $stats->total_laporan,
            'total_perolehan'   => (int) $stats->total_perolehan,
            'total_database'    => (int) $stats->total_database,
            'total_cs'          => (int) $stats->total_cs,
            'growth_laporan'    => $tanggal ? $this->calcGrowth($stats->total_laporan, $prev->total_laporan) : null,
            'growth_perolehan'  => $tanggal ? $this->calcGrowth($stats->total_perolehan, $prev->total_perolehan) : null,
        ]);
    }

    /**
     * Lookup donatur by phone number
     * GET /api/input-laporan/lookup-donatur?no_hp=...
     */
    public function apiLookupDonatur(Request $request)
    {
        $noHp = $request->get('no_hp');
        
        if (!$noHp || strlen($noHp) < 8) {
            return response()->json(['found' => false]);
        }

        $donatur = Donatur::where('no_hp', 'LIKE', '%' . $noHp . '%')
            ->select([
                'did', 'nama_donatur', 'kat_donatur', 'jenis_kelamin',
                'kode_negara', 'no_hp', 'email', 'sosmed_account', 'alamat'
            ])
            ->first();

        if (!$donatur) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'donatur' => $donatur
        ]);
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    /**
     * Sanitize & pick only fillable fields
     */
    private function sanitizeInput(array $input): array
    {
        $fillable = [
            'tanggal', 'tim', 'nama_cs', 'perolehan_jam', 'jml_database',
            'jml_perolehan', 'nama_bank', 'no_rek', 'did', 'nama_donatur',
            'nama_toko', 'kode_negara', 'no_hp', 'followup_wa', 'hasil_dari',
            'prg_cross_selling', 'adsense', 'e_commerce', 'program_utama',
            'nama_produk', 'zakat', 'wakaf', 'nama_platform', 'jenis_konten',
            'kat_donatur', 'jenis_kelamin', 'email', 'sosmed_account', 'alamat',
            'program', 'channel', 'fundraiser', 'keterangan',
        ];

        $clean = [];
        foreach ($fillable as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                $clean[$field] = is_string($value) ? trim($value) : $value;
                if ($clean[$field] === '') {
                    $clean[$field] = null;
                }
            }
        }

        return $clean;
    }

    /**
     * Calculate growth percentage
     */
    private function calcGrowth($current, $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
