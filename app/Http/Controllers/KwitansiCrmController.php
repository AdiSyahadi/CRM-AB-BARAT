<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Kwitansi;
use Carbon\Carbon;

class KwitansiCrmController extends Controller
{
    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    /**
     * Main Kwitansi v1 page
     */
    public function index()
    {
        return view('kwitansi.index');
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
        $tanggal = $request->get('tanggal', '');
        $sort    = $request->get('sort', 'tanggal');
        $order   = $request->get('order', 'desc');

        $allowed = ['tanggal', 'nama_donatur', 'jumlah_donasi', 'nama_donasi', 'nomor_kwitansi', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'tanggal';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = Kwitansi::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_donatur', 'like', "%{$search}%")
                  ->orWhere('nomor_kwitansi', 'like', "%{$search}%")
                  ->orWhere('nama_donasi', 'like', "%{$search}%");
            });
        }

        if ($tanggal === 'today') {
            $query->whereDate('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        } elseif ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        // Format jumlah_donasi for display
        $data->getCollection()->transform(function ($item) {
            $item->jumlah_donasi_formatted = 'Rp ' . number_format($item->jumlah_donasi ?? 0, 0, ',', '.');
            $item->tanggal_formatted = $item->tanggal
                ? Carbon::parse($item->tanggal)->format('d M Y')
                : '-';
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Get stats for dashboard cards
     */
    public function apiStats(Request $request)
    {
        $tanggal = $request->get('tanggal', '');

        $queryBase = Kwitansi::query();
        if ($tanggal === 'today') {
            $queryBase->whereDate('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        } elseif ($tanggal) {
            $queryBase->whereDate('tanggal', $tanggal);
        }

        $todayWib = Carbon::now('Asia/Jakarta')->toDateString();
        $total = (clone $queryBase)->count();
        $totalNominal = (clone $queryBase)->sum('jumlah_donasi');
        $todayCount = Kwitansi::whereDate('tanggal', $todayWib)->count();
        $todayNominal = Kwitansi::whereDate('tanggal', $todayWib)->sum('jumlah_donasi');

        return response()->json([
            'total_kwitansi' => $total,
            'total_nominal' => $totalNominal,
            'total_nominal_formatted' => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
            'today_count' => $todayCount,
            'today_nominal' => $todayNominal,
            'today_nominal_formatted' => 'Rp ' . number_format($todayNominal, 0, ',', '.'),
        ]);
    }

    /**
     * Store new kwitansi
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'       => 'required|date',
            'nama_donatur'  => 'required|string|max:255',
            'jumlah_donasi' => 'required|numeric|min:0',
            'nama_donasi'   => 'required|string|max:255',
        ], [
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'nama_donatur.required'  => 'Nama donatur wajib diisi.',
            'jumlah_donasi.required' => 'Jumlah donasi wajib diisi.',
            'nama_donasi.required'   => 'Nama program wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kwitansi = Kwitansi::create([
            'tanggal'       => $request->tanggal,
            'nama_donatur'  => $request->nama_donatur,
            'jumlah_donasi' => $request->jumlah_donasi,
            'nama_donasi'   => $request->nama_donasi,
        ]);

        // Reload to get nomor_kwitansi (auto-generated via model boot)
        $kwitansi->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Kwitansi berhasil dibuat. No: ' . $kwitansi->nomor_kwitansi,
            'data'    => $kwitansi,
        ], 201);
    }

    /**
     * Get single kwitansi for edit
     */
    public function apiShow($id)
    {
        $kwitansi = Kwitansi::findOrFail($id);
        return response()->json(['data' => $kwitansi]);
    }

    /**
     * Update existing kwitansi
     */
    public function apiUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'       => 'required|date',
            'nama_donatur'  => 'required|string|max:255',
            'jumlah_donasi' => 'required|numeric|min:0',
            'nama_donasi'   => 'required|string|max:255',
        ], [
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'nama_donatur.required'  => 'Nama donatur wajib diisi.',
            'jumlah_donasi.required' => 'Jumlah donasi wajib diisi.',
            'nama_donasi.required'   => 'Nama program wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kwitansi = Kwitansi::findOrFail($id);
        $kwitansi->update([
            'tanggal'       => $request->tanggal,
            'nama_donatur'  => $request->nama_donatur,
            'jumlah_donasi' => $request->jumlah_donasi,
            'nama_donasi'   => $request->nama_donasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kwitansi berhasil diperbarui.',
            'data'    => $kwitansi->fresh(),
        ]);
    }

    /**
     * Delete kwitansi
     */
    public function apiDelete($id)
    {
        $kwitansi = Kwitansi::findOrFail($id);
        $kwitansi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kwitansi berhasil dihapus.',
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

        $count = Kwitansi::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} kwitansi berhasil dihapus.",
        ]);
    }
}
