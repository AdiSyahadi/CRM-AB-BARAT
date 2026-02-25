<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\LaporanPerolehan;
use Carbon\Carbon;

class PartnershipCrmController extends Controller
{
    // ========================================================================
    // BASE QUERY (always filter tim = 'Partnership')
    // ========================================================================

    private function baseQuery()
    {
        return LaporanPerolehan::where('tim', 'Partnership');
    }

    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    public function index()
    {
        return view('partnership.index');
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
        $search  = (string) ($request->get('search') ?? '');
        $sort    = (string) ($request->get('sort') ?? 'tanggal');
        $order   = (string) ($request->get('order') ?? 'desc');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $allowed = ['tanggal', 'nama_cs', 'jml_perolehan', 'nama_donatur', 'nama_bank', 'keterangan', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'tanggal';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = $this->baseQuery();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_cs', 'like', "%{$search}%")
                  ->orWhere('nama_donatur', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('nama_bank', 'like', "%{$search}%")
                  ->orWhere('no_rek', 'like', "%{$search}%");
            });
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $query->where('tanggal', '>=', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $query->where('tanggal', '<=', $dateTo);
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            $item->tanggal_fmt = $item->tanggal
                ? Carbon::parse($item->tanggal)->format('d M Y')
                : '-';
            $item->jml_perolehan_fmt = $item->jml_perolehan
                ? 'Rp ' . number_format($item->jml_perolehan, 0, ',', '.')
                : '-';
            $item->created_fmt = $item->created_at
                ? Carbon::parse($item->created_at)->format('d M Y H:i')
                : '-';
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Stats for dashboard cards
     */
    public function apiStats()
    {
        $total = $this->baseQuery()->count();
        $totalPerolehan = $this->baseQuery()->sum('jml_perolehan');

        // Count distinct CS names
        $totalCs = $this->baseQuery()
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct('nama_cs')
            ->count('nama_cs');

        // Latest entry date
        $latestDate = $this->baseQuery()->max('tanggal');

        return response()->json([
            'total_data'      => $total,
            'total_perolehan' => 'Rp ' . number_format($totalPerolehan ?? 0, 0, ',', '.'),
            'total_cs'        => $totalCs,
            'latest_date'     => $latestDate ? Carbon::parse($latestDate)->format('d M Y') : '-',
        ]);
    }

    /**
     * Get single record
     */
    public function apiShow(int $id)
    {
        $item = $this->baseQuery()->find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $item->tanggal_fmt = $item->tanggal
            ? Carbon::parse($item->tanggal)->format('d M Y')
            : '-';
        $item->jml_perolehan_fmt = $item->jml_perolehan
            ? 'Rp ' . number_format($item->jml_perolehan, 0, ',', '.')
            : '-';

        return response()->json($item);
    }

    /**
     * Store new record
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'        => 'required|date',
            'nama_cs'        => 'required|string|max:100',
            'jml_perolehan'  => 'required|numeric|min:0',
            'nama_donatur'   => 'nullable|string|max:200',
            'nama_bank'      => 'nullable|string|max:100',
            'no_rek'         => 'nullable|string|max:50',
            'keterangan'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['tim'] = 'Partnership';
        $data['hasil_dari'] = 'Partnership';
        $data['perolehan_jam'] = $data['perolehan_jam'] ?? '0';

        $item = LaporanPerolehan::create($data);

        return response()->json([
            'message' => 'Data partnership berhasil ditambahkan',
            'data'    => $item,
        ], 201);
    }

    /**
     * Update record
     */
    public function apiUpdate(Request $request, int $id)
    {
        $item = $this->baseQuery()->find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal'        => 'required|date',
            'nama_cs'        => 'required|string|max:100',
            'jml_perolehan'  => 'required|numeric|min:0',
            'nama_donatur'   => 'nullable|string|max:200',
            'nama_bank'      => 'nullable|string|max:100',
            'no_rek'         => 'nullable|string|max:50',
            'keterangan'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $validator->validated();
        // Ensure perolehan_jam stays populated (model requires it)
        if (!$item->perolehan_jam) {
            $updateData['perolehan_jam'] = '0';
        }

        $item->update($updateData);

        return response()->json([
            'message' => 'Data partnership berhasil diperbarui',
            'data'    => $item->fresh(),
        ]);
    }

    /**
     * Delete record
     */
    public function apiDelete(int $id)
    {
        $item = $this->baseQuery()->find($id);

        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Data partnership berhasil dihapus']);
    }
}
