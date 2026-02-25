<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Absensi;
use App\Models\AbsenCs;
use Carbon\Carbon;

class AbsensiCrmController extends Controller
{
    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    public function index()
    {
        return view('absensi.index');
    }

    // ========================================================================
    // STATS
    // ========================================================================

    /**
     * Combined stats for both tables
     */
    public function apiStats(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->toDateString());

        // Absensi ubudiyah stats
        $ubudiyahToday     = Absensi::whereDate('tanggal', $tanggal)->count();
        $ubudiyahTotal     = Absensi::count();
        $tahajudToday      = Absensi::whereDate('tanggal', $tanggal)->where('ubudiyah', 'Shalat Tahajud')->count();
        $dhuhaToday        = Absensi::whereDate('tanggal', $tanggal)->where('ubudiyah', 'Shalat Dhuha')->count();

        // Absen CS stats
        $csMasukToday      = AbsenCs::whereDate('tanggal', $tanggal)->where('tipe_absen', 'Masuk')->count();
        $csPulangToday     = AbsenCs::whereDate('tanggal', $tanggal)->where('tipe_absen', 'Pulang')->count();
        $csHadirWFO        = AbsenCs::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'like', '%WFO%')->count();
        $csHadirWFH        = AbsenCs::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'like', '%WFH%')->count();
        $csTotal           = AbsenCs::count();

        return response()->json([
            'tanggal'          => Carbon::parse($tanggal)->format('d M Y'),
            'ubudiyah_today'   => $ubudiyahToday,
            'ubudiyah_total'   => $ubudiyahTotal,
            'tahajud_today'    => $tahajudToday,
            'dhuha_today'      => $dhuhaToday,
            'cs_masuk_today'   => $csMasukToday,
            'cs_pulang_today'  => $csPulangToday,
            'cs_wfo'           => $csHadirWFO,
            'cs_wfh'           => $csHadirWFH,
            'cs_total'         => $csTotal,
        ]);
    }

    // ========================================================================
    // ABSENSI UBUDIYAH (absensis table)
    // ========================================================================

    /**
     * Paginated list for ubudiyah
     */
    public function apiUbudiyahList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = $request->get('search', '');
        $status  = $request->get('status', '');
        $ubudiyah = $request->get('ubudiyah', '');
        $tglFrom = $request->get('tanggal_dari', '');
        $tglTo   = $request->get('tanggal_sampai', '');
        $sort    = $request->get('sort', 'tanggal');
        $order   = $request->get('order', 'desc');

        $allowed = ['nama', 'tanggal', 'jam', 'status', 'ubudiyah', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'tanggal';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = Absensi::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($status) $query->where('status', $status);
        if ($ubudiyah) $query->where('ubudiyah', $ubudiyah);

        if ($tglFrom) $query->whereDate('tanggal', '>=', $tglFrom);
        if ($tglTo) $query->whereDate('tanggal', '<=', $tglTo);

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            $item->tanggal_fmt = $item->tanggal
                ? Carbon::parse($item->tanggal)->format('d M Y')
                : '-';
            $item->jam_fmt = $item->jam ? substr($item->jam, 0, 5) : '-';
            $item->foto_url = $item->foto
                ? (str_starts_with($item->foto, 'http') ? $item->foto : asset('storage/' . $item->foto))
                : null;
            $item->status_color = match ($item->status) {
                'Hadir' => 'green',
                'Izin'  => 'amber',
                'Sakit' => 'red',
                'Tugas diluar' => 'blue',
                default => 'gray',
            };
            $item->ubudiyah_color = match ($item->ubudiyah) {
                'Shalat Tahajud' => 'indigo',
                'Shalat Dhuha'   => 'amber',
                'Tidak Ubudiyah' => 'gray',
                default => 'gray',
            };
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Get single ubudiyah record
     */
    public function apiUbudiyahShow(int $id)
    {
        $item = Absensi::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        return response()->json($item);
    }

    /**
     * Store ubudiyah
     */
    public function apiUbudiyahStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'       => 'required|string|max:255',
            'tanggal'    => 'required|date',
            'jam'        => 'required',
            'status'     => 'required|in:Hadir,Izin,Sakit,Tugas diluar',
            'ubudiyah'   => 'nullable|in:Shalat Tahajud,Shalat Dhuha,Tidak Ubudiyah',
            'keterangan' => 'nullable|string|max:255',
            'alamat'     => 'nullable|string|max:255',
            'latitude'   => 'nullable|string|max:255',
            'longitude'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Absensi::create($validator->validated());

        return response()->json(['message' => 'Absensi ubudiyah berhasil ditambahkan', 'data' => $item], 201);
    }

    /**
     * Update ubudiyah
     */
    public function apiUbudiyahUpdate(Request $request, int $id)
    {
        $item = Absensi::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'nama'       => 'required|string|max:255',
            'tanggal'    => 'required|date',
            'jam'        => 'required',
            'status'     => 'required|in:Hadir,Izin,Sakit,Tugas diluar',
            'ubudiyah'   => 'nullable|in:Shalat Tahajud,Shalat Dhuha,Tidak Ubudiyah',
            'keterangan' => 'nullable|string|max:255',
            'alamat'     => 'nullable|string|max:255',
            'latitude'   => 'nullable|string|max:255',
            'longitude'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($validator->validated());

        return response()->json(['message' => 'Absensi ubudiyah berhasil diperbarui', 'data' => $item->fresh()]);
    }

    /**
     * Delete ubudiyah
     */
    public function apiUbudiyahDelete(int $id)
    {
        $item = Absensi::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        $item->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    // ========================================================================
    // ABSEN CS / HARIAN (absen_cs table)
    // ========================================================================

    /**
     * Paginated list for CS daily absen
     */
    public function apiHarianList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = $request->get('search', '');
        $tipe    = $request->get('tipe_absen', '');
        $status  = $request->get('status_kehadiran', '');
        $tglFrom = $request->get('tanggal_dari', '');
        $tglTo   = $request->get('tanggal_sampai', '');
        $sort    = $request->get('sort', 'tanggal');
        $order   = $request->get('order', 'desc');

        $allowed = ['nama_cs', 'tanggal', 'jam', 'tipe_absen', 'status_kehadiran', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'tanggal';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = AbsenCs::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_cs', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($tipe) $query->where('tipe_absen', $tipe);
        if ($status) $query->where('status_kehadiran', $status);

        if ($tglFrom) $query->whereDate('tanggal', '>=', $tglFrom);
        if ($tglTo) $query->whereDate('tanggal', '<=', $tglTo);

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            $item->tanggal_fmt = $item->tanggal
                ? Carbon::parse($item->tanggal)->format('d M Y')
                : '-';
            $item->jam_fmt = $item->jam ? Carbon::parse($item->jam)->format('H:i') : '-';
            $item->foto_url = $item->foto
                ? asset('absen_foto/' . $item->foto)
                : null;
            $item->tipe_color = $item->tipe_absen === 'Masuk' ? 'green' : 'orange';
            $item->status_label = $item->status_kehadiran ?: '-';
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Get single CS absen record
     */
    public function apiHarianShow(int $id)
    {
        $item = AbsenCs::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $item->foto_url = $item->foto ? asset('absen_foto/' . $item->foto) : null;

        return response()->json($item);
    }

    /**
     * Store CS absen
     */
    public function apiHarianStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_cs'          => 'required|string|max:255',
            'tanggal'          => 'required|date',
            'jam'              => 'required',
            'tipe_absen'       => 'required|in:Masuk,Pulang',
            'status_kehadiran' => 'nullable|string|max:50',
            'lokasi'           => 'nullable|string|max:255',
            'keterangan'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = AbsenCs::create($validator->validated());

        return response()->json(['message' => 'Absen harian berhasil ditambahkan', 'data' => $item], 201);
    }

    /**
     * Update CS absen
     */
    public function apiHarianUpdate(Request $request, int $id)
    {
        $item = AbsenCs::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'nama_cs'          => 'required|string|max:255',
            'tanggal'          => 'required|date',
            'jam'              => 'required',
            'tipe_absen'       => 'required|in:Masuk,Pulang',
            'status_kehadiran' => 'nullable|string|max:50',
            'lokasi'           => 'nullable|string|max:255',
            'keterangan'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($validator->validated());

        return response()->json(['message' => 'Absen harian berhasil diperbarui', 'data' => $item->fresh()]);
    }

    /**
     * Delete CS absen
     */
    public function apiHarianDelete(int $id)
    {
        $item = AbsenCs::find($id);
        if (!$item) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        $item->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    // ========================================================================
    // FILTER OPTIONS
    // ========================================================================

    /**
     * Distinct nama options for dropdowns
     */
    public function apiOptions()
    {
        $ubudiyahNames = Absensi::distinct()->orderBy('nama')->pluck('nama');
        $csNames       = AbsenCs::distinct()->orderBy('nama_cs')->pluck('nama_cs');

        $statusKehadiran = AbsenCs::distinct()
            ->whereNotNull('status_kehadiran')
            ->where('status_kehadiran', '!=', '')
            ->orderBy('status_kehadiran')
            ->pluck('status_kehadiran');

        return response()->json([
            'ubudiyah_names'   => $ubudiyahNames,
            'cs_names'         => $csNames,
            'status_kehadiran' => $statusKehadiran,
        ]);
    }
}
