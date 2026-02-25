<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DataPegawai;
use Carbon\Carbon;

class DataPegawaiCrmController extends Controller
{
    // ========================================================================
    // JABATAN MAPPING (no lookup table exists)
    // ========================================================================

    private const JABATAN_MAP = [
        1 => 'Staff',
        2 => 'Supervisor',
        3 => 'Manager',
    ];

    private function jabatanLabel(?int $id): string
    {
        return self::JABATAN_MAP[$id] ?? ($id ? "Jabatan #{$id}" : '-');
    }

    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    public function index()
    {
        return view('pegawai.index', [
            'jabatanOptions' => self::JABATAN_MAP,
        ]);
    }

    // ========================================================================
    // API ENDPOINTS
    // ========================================================================

    /**
     * Paginated list with search, filter & sort
     */
    public function apiList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = (string) $request->get('search', '');
        $gender  = (string) $request->get('jenis_kelamin', '');
        $jabatan = $request->get('jabatan');
        $sort    = (string) $request->get('sort', 'nama_pegawai');
        $order   = (string) $request->get('order', 'asc');

        $allowed = ['nama_pegawai', 'tanggal_lahir', 'jenis_kelamin', 'no_telepon', 'id_jabatan', 'tanggal_masuk', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'nama_pegawai';
        if (!in_array($order, ['asc', 'desc'])) $order = 'asc';

        $query = DataPegawai::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pegawai', 'like', "%{$search}%")
                  ->orWhere('tempat_lahir', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        if ($gender && in_array($gender, ['L', 'P'])) {
            $query->where('jenis_kelamin', $gender);
        }

        if ($jabatan !== null && $jabatan !== '') {
            $query->where('id_jabatan', (int) $jabatan);
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $jabatanMap = self::JABATAN_MAP;

        $data->getCollection()->transform(function ($item) use ($jabatanMap) {
            $item->jabatan_label    = $jabatanMap[$item->id_jabatan] ?? ($item->id_jabatan ? "Jabatan #{$item->id_jabatan}" : '-');
            $item->tanggal_lahir_fmt = $item->tanggal_lahir
                ? Carbon::parse($item->tanggal_lahir)->format('d M Y')
                : '-';
            $item->tanggal_masuk_fmt = $item->tanggal_masuk
                ? Carbon::parse($item->tanggal_masuk)->format('d M Y')
                : '-';
            $item->masa_kerja = $item->tanggal_masuk
                ? Carbon::parse($item->tanggal_masuk)->diffForHumans(now(), ['parts' => 2, 'short' => true])
                : '-';
            $item->gender_label = $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Stats for dashboard cards
     */
    public function apiStats()
    {
        $total     = DataPegawai::count();
        $lakilaki  = DataPegawai::where('jenis_kelamin', 'L')->count();
        $perempuan = DataPegawai::where('jenis_kelamin', 'P')->count();

        // Masa kerja rata-rata
        $avgDays = DataPegawai::whereNotNull('tanggal_masuk')
            ->selectRaw('AVG(DATEDIFF(CURDATE(), tanggal_masuk)) as avg_days')
            ->value('avg_days');

        $avgYears = $avgDays ? round($avgDays / 365, 1) : 0;

        return response()->json([
            'total'           => $total,
            'laki_laki'       => $lakilaki,
            'perempuan'       => $perempuan,
            'avg_masa_kerja'  => $avgYears . ' tahun',
        ]);
    }

    /**
     * Get single pegawai
     */
    public function apiShow(int $id)
    {
        $pegawai = DataPegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $pegawai->jabatan_label = $this->jabatanLabel($pegawai->id_jabatan);

        return response()->json($pegawai);
    }

    /**
     * Store new pegawai
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pegawai'  => 'required|string|max:100',
            'tempat_lahir'  => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'no_telepon'    => 'nullable|string|max:15',
            'id_jabatan'    => 'nullable|integer',
            'tanggal_masuk' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pegawai = DataPegawai::create($validator->validated());

        return response()->json([
            'message' => 'Data pegawai berhasil ditambahkan',
            'data'    => $pegawai,
        ], 201);
    }

    /**
     * Update pegawai
     */
    public function apiUpdate(Request $request, int $id)
    {
        $pegawai = DataPegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_pegawai'  => 'required|string|max:100',
            'tempat_lahir'  => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'no_telepon'    => 'nullable|string|max:15',
            'id_jabatan'    => 'nullable|integer',
            'tanggal_masuk' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pegawai->update($validator->validated());

        return response()->json([
            'message' => 'Data pegawai berhasil diperbarui',
            'data'    => $pegawai->fresh(),
        ]);
    }

    /**
     * Delete pegawai
     */
    public function apiDelete(int $id)
    {
        $pegawai = DataPegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $pegawai->delete();

        return response()->json(['message' => 'Data pegawai berhasil dihapus']);
    }

    /**
     * Jabatan options (for dropdown)
     */
    public function apiJabatanOptions()
    {
        return response()->json(
            collect(self::JABATAN_MAP)->map(fn ($label, $id) => [
                'id'    => $id,
                'label' => $label,
            ])->values()
        );
    }
}
