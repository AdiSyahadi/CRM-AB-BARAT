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

        // Year filter
        $year = $request->get('year');
        if ($year && $year !== 'all') {
            $query->whereYear('tanggal', (int) $year);
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
    public function apiStats(Request $request)
    {
        $year = $request->get('year');
        $query = $this->baseQuery();

        if ($year && $year !== 'all') {
            $query->whereYear('tanggal', (int) $year);
        }

        $total = $query->count();
        $totalPerolehan = (clone $query)->sum('jml_perolehan');

        // Count distinct CS names
        $totalCs = (clone $query)
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct('nama_cs')
            ->count('nama_cs');

        // Latest entry date
        $latestDate = (clone $query)->max('tanggal');

        // Available years for filter
        $years = $this->baseQuery()
            ->selectRaw('YEAR(tanggal) as y')
            ->whereNotNull('tanggal')
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y');

        return response()->json([
            'total_data'      => $total,
            'total_perolehan' => 'Rp ' . number_format($totalPerolehan ?? 0, 0, ',', '.'),
            'total_cs'        => $totalCs,
            'latest_date'     => $latestDate ? Carbon::parse($latestDate)->format('d M Y') : '-',
            'years'           => $years,
        ]);
    }

    /**
     * Yearly comparison data
     */
    public function apiYearlyComparison()
    {
        // Per-year summary
        $yearly = $this->baseQuery()
            ->selectRaw('YEAR(tanggal) as year, COUNT(*) as total_data, SUM(jml_perolehan) as total_perolehan, COUNT(DISTINCT nama_cs) as total_cs')
            ->whereNotNull('tanggal')
            ->groupByRaw('YEAR(tanggal)')
            ->orderByDesc('year')
            ->get()
            ->map(function ($row) {
                $row->total_perolehan_fmt = 'Rp ' . number_format($row->total_perolehan ?? 0, 0, ',', '.');
                $row->total_perolehan = (float) $row->total_perolehan;
                return $row;
            });

        // Per-month breakdown for each year (for chart)
        $years = $yearly->pluck('year');
        $monthlyBreakdown = [];
        foreach ($years as $y) {
            $monthlyBreakdown[$y] = $this->baseQuery()
                ->selectRaw('MONTH(tanggal) as month, COUNT(*) as total_data, SUM(jml_perolehan) as total_perolehan')
                ->whereYear('tanggal', $y)
                ->groupByRaw('MONTH(tanggal)')
                ->orderBy('month')
                ->get()
                ->map(function ($row) {
                    $row->total_perolehan = (float) $row->total_perolehan;
                    return $row;
                });
        }

        return response()->json([
            'yearly' => $yearly,
            'monthly_breakdown' => $monthlyBreakdown,
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
