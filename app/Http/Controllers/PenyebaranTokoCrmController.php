<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PenyebaranToko;
use Carbon\Carbon;

class PenyebaranTokoCrmController extends Controller
{
    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    public function index()
    {
        return view('penyebaran-toko.index');
    }

    // ========================================================================
    // API ENDPOINTS
    // ========================================================================

    /**
     * Paginated list with search, filter & sort
     */
    public function apiList(Request $request)
    {
        $perPage  = (int) $request->get('per_page', 20);
        $search   = (string) ($request->get('search') ?? '');
        $status   = (string) ($request->get('status') ?? '');
        $sort     = (string) ($request->get('sort') ?? 'tanggal_registrasi');
        $order    = (string) ($request->get('order') ?? 'desc');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $allowed = ['tanggal_registrasi', 'nama_cs', 'nama_toko', 'nama_donatur', 'nomor_kencleng', 'status', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'tanggal_registrasi';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = PenyebaranToko::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_cs', 'like', "%{$search}%")
                  ->orWhere('nama_toko', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%")
                  ->orWhere('nomor_kencleng', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($status !== '' && in_array($status, ['Di terima', 'Di tolak'])) {
            $query->where('status', $status);
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $query->where('tanggal_registrasi', '>=', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $query->where('tanggal_registrasi', '<=', $dateTo);
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            $item->tanggal_fmt = $item->tanggal_registrasi
                ? Carbon::parse($item->tanggal_registrasi)->format('d M Y')
                : '-';
            $item->foto_url = $item->foto_base64
                ? '/' . $item->foto_base64
                : null;
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Stats for dashboard cards
     */
    public function apiStats()
    {
        $total    = PenyebaranToko::count();
        $diterima = PenyebaranToko::where('status', 'Di terima')->count();
        $ditolak  = PenyebaranToko::where('status', 'Di tolak')->count();
        $totalCs  = PenyebaranToko::whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct('nama_cs')
            ->count('nama_cs');

        return response()->json([
            'total'     => $total,
            'diterima'  => $diterima,
            'ditolak'   => $ditolak,
            'total_cs'  => $totalCs,
        ]);
    }

    /**
     * Map markers data (all records with valid lat/lng)
     */
    public function apiMapData()
    {
        $items = PenyebaranToko::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->select('id', 'nama_toko', 'nama_cs', 'nama_donatur', 'nomor_kencleng', 'alamat', 'status', 'latitude', 'longitude', 'tanggal_registrasi')
            ->get()
            ->map(function ($item) {
                $item->tanggal_fmt = $item->tanggal_registrasi
                    ? Carbon::parse($item->tanggal_registrasi)->format('d M Y')
                    : '-';
                return $item;
            });

        return response()->json($items);
    }

    /**
     * Get single record
     */
    public function apiShow(int $id)
    {
        $item = PenyebaranToko::find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $item->tanggal_fmt = $item->tanggal_registrasi
            ? Carbon::parse($item->tanggal_registrasi)->format('d M Y')
            : '-';
        $item->foto_url = $item->foto_base64
            ? '/' . $item->foto_base64
            : null;

        return response()->json($item);
    }

    /**
     * Store new record
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_registrasi' => 'required|date',
            'nama_cs'            => 'required|string|max:100',
            'nama_toko'          => 'required|string|max:200',
            'nama_donatur'       => 'required|string|max:200',
            'nomor_kencleng'     => 'required|string|max:50',
            'no_hp'              => 'required|string|max:20',
            'alamat'             => 'required|string',
            'status'             => 'nullable|in:Di terima,Di tolak',
            'keterangan'         => 'nullable|string|max:500',
            'latitude'           => 'nullable|string|max:20',
            'longitude'          => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (empty($data['status'])) {
            $data['status'] = 'Di terima';
        }

        $item = PenyebaranToko::create($data);

        return response()->json([
            'message' => 'Data penyebaran toko berhasil ditambahkan',
            'data'    => $item,
        ], 201);
    }

    /**
     * Update record
     */
    public function apiUpdate(Request $request, int $id)
    {
        $item = PenyebaranToko::find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_registrasi' => 'required|date',
            'nama_cs'            => 'required|string|max:100',
            'nama_toko'          => 'required|string|max:200',
            'nama_donatur'       => 'required|string|max:200',
            'nomor_kencleng'     => 'required|string|max:50',
            'no_hp'              => 'required|string|max:20',
            'alamat'             => 'required|string',
            'status'             => 'nullable|in:Di terima,Di tolak',
            'keterangan'         => 'nullable|string|max:500',
            'latitude'           => 'nullable|string|max:20',
            'longitude'          => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'message' => 'Data penyebaran toko berhasil diperbarui',
            'data'    => $item->fresh(),
        ]);
    }

    /**
     * Delete record
     */
    public function apiDelete(int $id)
    {
        $item = PenyebaranToko::find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Data penyebaran toko berhasil dihapus']);
    }
}
