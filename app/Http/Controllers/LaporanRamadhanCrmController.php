<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerolehan;
use App\Models\RamadhanPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRamadhanCrmController extends Controller
{
    // ========================================
    // VIEW
    // ========================================

    public function index()
    {
        return view('laporan-ramadhan.index');
    }

    // ========================================
    // API: Daftar Periode Ramadhan
    // ========================================

    /**
     * Get all ramadhan periods for dropdown/config.
     */
    public function apiPeriods()
    {
        $periods = RamadhanPeriod::chronological()->get()->map(function ($p) {
            return [
                'id'          => $p->id,
                'hijri_year'  => $p->hijri_year,
                'label'       => $p->label,
                'start_date'  => $p->start_date->format('Y-m-d'),
                'end_date'    => $p->end_date->format('Y-m-d'),
                'total_days'  => $p->total_days,
                'target'      => $p->target,
                'target_formatted' => 'Rp ' . number_format($p->target, 0, ',', '.'),
                'masehi_year' => $p->masehi_year,
            ];
        });

        return response()->json($periods);
    }

    // ========================================
    // API: CRUD Periode Ramadhan
    // ========================================

    public function apiStorePeriod(Request $request)
    {
        $validated = $request->validate([
            'hijri_year'  => 'required|integer|unique:ramadhan_periods,hijri_year',
            'label'       => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'target'      => 'required|numeric|min:0',
        ]);

        $period = RamadhanPeriod::create($validated);

        return response()->json([
            'message' => 'Periode Ramadhan berhasil ditambahkan',
            'data'    => $period,
        ], 201);
    }

    public function apiUpdatePeriod(Request $request, $id)
    {
        $period = RamadhanPeriod::findOrFail($id);

        $validated = $request->validate([
            'hijri_year'  => 'required|integer|unique:ramadhan_periods,hijri_year,' . $id,
            'label'       => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'target'      => 'required|numeric|min:0',
        ]);

        $period->update($validated);

        return response()->json([
            'message' => 'Periode Ramadhan berhasil diperbarui',
            'data'    => $period,
        ]);
    }

    public function apiDeletePeriod($id)
    {
        $period = RamadhanPeriod::findOrFail($id);
        $period->delete();

        return response()->json(['message' => 'Periode Ramadhan berhasil dihapus']);
    }

    // ========================================
    // API: Stats Overview (semua periode)
    // ========================================

    /**
     * Statistik ringkasan per periode Ramadhan.
     * Return: array of { label, total_perolehan, target, percentage, total_transaksi, ... }
     */
    public function apiStats(Request $request)
    {
        $periods = RamadhanPeriod::chronological()->get();

        if ($periods->isEmpty()) {
            return response()->json([
                'periods' => [],
                'summary' => [],
            ]);
        }

        $hariRamadhan = $request->input('hari_ramadhan'); // filter specific day 1-30
        $kumulatif    = $request->input('kumulatif');      // kumulatif 1 s/d hari ke-N

        $stats = [];

        foreach ($periods as $period) {
            // Determine date range based on filter
            if ($kumulatif) {
                $day = min((int) $kumulatif, $period->total_days);
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->dayToDate($day)->format('Y-m-d');
            } elseif ($hariRamadhan) {
                $day = min((int) $hariRamadhan, $period->total_days);
                $specificDate = $period->dayToDate($day)->format('Y-m-d');
                $startDate = $specificDate;
                $endDate   = $specificDate;
            } else {
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->end_date->format('Y-m-d');
            }

            $result = LaporanPerolehan::whereBetween('tanggal', [$startDate, $endDate])
                ->selectRaw('COALESCE(SUM(jml_perolehan), 0) as total_perolehan')
                ->selectRaw('COUNT(*) as total_transaksi')
                ->selectRaw('COUNT(DISTINCT nama_cs) as total_cs')
                ->first();

            $totalPerolehan = (float) $result->total_perolehan;
            $percentage = $period->target > 0
                ? round(($totalPerolehan / $period->target) * 100, 2)
                : 0;

            $stats[] = [
                'period_id'      => $period->id,
                'hijri_year'     => $period->hijri_year,
                'label'          => $period->label,
                'masehi_year'    => $period->masehi_year,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'target'         => $period->target,
                'target_formatted'     => 'Rp ' . number_format($period->target, 0, ',', '.'),
                'total_perolehan'      => $totalPerolehan,
                'total_perolehan_formatted' => 'Rp ' . number_format($totalPerolehan, 0, ',', '.'),
                'percentage'     => $percentage,
                'total_transaksi' => (int) $result->total_transaksi,
                'total_cs'       => (int) $result->total_cs,
            ];
        }

        return response()->json([
            'periods' => $stats,
        ]);
    }

    // ========================================
    // API: Perbandingan per CS
    // ========================================

    /**
     * Tabel perbandingan perolehan CS antar tahun Ramadhan.
     * Kolom dinamis berdasarkan jumlah periode yang terdaftar.
     */
    public function apiPerbandinganCS(Request $request)
    {
        $periods = RamadhanPeriod::chronological()->get();

        if ($periods->isEmpty()) {
            return response()->json(['data' => [], 'periods' => []]);
        }

        $hariRamadhan = $request->input('hari_ramadhan');
        $kumulatif    = $request->input('kumulatif');
        $filterTim    = $request->input('tim');
        $search       = $request->input('search');
        $sort         = $request->input('sort', 'nama_cs');
        $order        = $request->input('order', 'asc');

        // Build dynamic SUM CASE for each period
        $selectParts = ['nama_cs'];
        $bindings = [];

        foreach ($periods as $i => $period) {
            if ($kumulatif) {
                $day = min((int) $kumulatif, $period->total_days);
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->dayToDate($day)->format('Y-m-d');
            } elseif ($hariRamadhan) {
                $day = min((int) $hariRamadhan, $period->total_days);
                $specificDate = $period->dayToDate($day)->format('Y-m-d');
                $startDate = $specificDate;
                $endDate   = $specificDate;
            } else {
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->end_date->format('Y-m-d');
            }

            $alias = 'total_' . $period->masehi_year;
            $selectParts[] = "COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) as {$alias}";
            $bindings[] = $startDate;
            $bindings[] = $endDate;
        }

        $query = LaporanPerolehan::query()
            ->selectRaw(implode(', ', $selectParts), $bindings)
            ->groupBy('nama_cs');

        // Apply date range filter (limit rows to relevant periods only)
        $allStart = $periods->min('start_date')->format('Y-m-d');
        $allEnd   = $periods->max('end_date')->format('Y-m-d');

        // We need all records within any ramadhan period
        $query->where(function ($q) use ($periods, $hariRamadhan, $kumulatif) {
            foreach ($periods as $period) {
                if ($kumulatif) {
                    $day = min((int) $kumulatif, $period->total_days);
                    $start = $period->start_date->format('Y-m-d');
                    $end   = $period->dayToDate($day)->format('Y-m-d');
                } elseif ($hariRamadhan) {
                    $day = min((int) $hariRamadhan, $period->total_days);
                    $date = $period->dayToDate($day)->format('Y-m-d');
                    $start = $date;
                    $end   = $date;
                } else {
                    $start = $period->start_date->format('Y-m-d');
                    $end   = $period->end_date->format('Y-m-d');
                }
                $q->orWhereBetween('tanggal', [$start, $end]);
            }
        });

        if ($filterTim) {
            $query->where('tim', $filterTim);
        }
        if ($search) {
            $query->where('nama_cs', 'like', "%{$search}%");
        }

        // Sorting
        $validSorts = ['nama_cs'];
        foreach ($periods as $period) {
            $validSorts[] = 'total_' . $period->masehi_year;
        }
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama_cs', 'asc');
        }

        $results = $query->get();

        // Format output
        $data = $results->map(function ($row) use ($periods) {
            $item = ['nama_cs' => $row->nama_cs];
            foreach ($periods as $period) {
                $key = 'total_' . $period->masehi_year;
                $val = (float) ($row->{$key} ?? 0);
                $item[$key] = $val;
                $item[$key . '_formatted'] = 'Rp ' . number_format($val, 0, ',', '.');
                $item['persen_' . $period->masehi_year] = $period->target > 0
                    ? round(($val / $period->target) * 100, 2) : 0;
            }

            // Growth (jika ada >= 2 periode)
            if ($periods->count() >= 2) {
                $lastTwo = $periods->slice(-2)->values();
                $prevKey = 'total_' . $lastTwo[0]->masehi_year;
                $currKey = 'total_' . $lastTwo[1]->masehi_year;
                $prev = (float) ($row->{$prevKey} ?? 0);
                $curr = (float) ($row->{$currKey} ?? 0);
                $item['growth'] = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : ($curr > 0 ? 100 : 0);
            }

            return $item;
        });

        // Periode info for frontend column headers
        $periodInfo = $periods->map(fn($p) => [
            'masehi_year'     => $p->masehi_year,
            'hijri_year'      => $p->hijri_year,
            'label'           => $p->label,
            'target'          => $p->target,
            'target_formatted' => 'Rp ' . number_format($p->target, 0, ',', '.'),
        ]);

        return response()->json([
            'data'    => $data,
            'periods' => $periodInfo,
        ]);
    }

    // ========================================
    // API: Perbandingan per Tim
    // ========================================

    public function apiPerbandinganTim(Request $request)
    {
        $periods = RamadhanPeriod::chronological()->get();

        if ($periods->isEmpty()) {
            return response()->json(['data' => [], 'periods' => []]);
        }

        $hariRamadhan = $request->input('hari_ramadhan');
        $kumulatif    = $request->input('kumulatif');

        $selectParts = ['tim'];
        $bindings = [];

        foreach ($periods as $period) {
            if ($kumulatif) {
                $day = min((int) $kumulatif, $period->total_days);
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->dayToDate($day)->format('Y-m-d');
            } elseif ($hariRamadhan) {
                $day = min((int) $hariRamadhan, $period->total_days);
                $specificDate = $period->dayToDate($day)->format('Y-m-d');
                $startDate = $specificDate;
                $endDate   = $specificDate;
            } else {
                $startDate = $period->start_date->format('Y-m-d');
                $endDate   = $period->end_date->format('Y-m-d');
            }

            $alias = 'total_' . $period->masehi_year;
            $selectParts[] = "COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) as {$alias}";
            $bindings[] = $startDate;
            $bindings[] = $endDate;
        }

        $query = LaporanPerolehan::query()
            ->selectRaw(implode(', ', $selectParts), $bindings)
            ->where(function ($q) use ($periods, $hariRamadhan, $kumulatif) {
                foreach ($periods as $period) {
                    if ($kumulatif) {
                        $day = min((int) $kumulatif, $period->total_days);
                        $start = $period->start_date->format('Y-m-d');
                        $end   = $period->dayToDate($day)->format('Y-m-d');
                    } elseif ($hariRamadhan) {
                        $day = min((int) $hariRamadhan, $period->total_days);
                        $date = $period->dayToDate($day)->format('Y-m-d');
                        $start = $date;
                        $end   = $date;
                    } else {
                        $start = $period->start_date->format('Y-m-d');
                        $end   = $period->end_date->format('Y-m-d');
                    }
                    $q->orWhereBetween('tanggal', [$start, $end]);
                }
            })
            ->groupBy('tim')
            ->orderBy('tim', 'asc');

        $results = $query->get();

        $data = $results->map(function ($row) use ($periods) {
            $item = ['tim' => $row->tim ?: '(Tanpa Tim)'];
            foreach ($periods as $period) {
                $key = 'total_' . $period->masehi_year;
                $val = (float) ($row->{$key} ?? 0);
                $item[$key] = $val;
                $item[$key . '_formatted'] = 'Rp ' . number_format($val, 0, ',', '.');
            }
            return $item;
        });

        $periodInfo = $periods->map(fn($p) => [
            'masehi_year' => $p->masehi_year,
            'label'       => $p->label,
        ]);

        return response()->json([
            'data'    => $data,
            'periods' => $periodInfo,
        ]);
    }

    // ========================================
    // API: Trend Harian (Chart Data)
    // ========================================

    /**
     * Return perolehan per hari Ramadhan (1-30) untuk setiap periode.
     * Format data cocok untuk chart line/bar perbandingan.
     */
    public function apiTrendHarian(Request $request)
    {
        $periods = RamadhanPeriod::chronological()->get();
        $filterTim = $request->input('tim');
        $filterCs  = $request->input('nama_cs');

        $series = [];

        foreach ($periods as $period) {
            $dailyData = [];

            for ($day = 1; $day <= $period->total_days; $day++) {
                $date = $period->dayToDate($day)->format('Y-m-d');

                $query = LaporanPerolehan::where('tanggal', $date);

                if ($filterTim) {
                    $query->where('tim', $filterTim);
                }
                if ($filterCs) {
                    $query->where('nama_cs', $filterCs);
                }

                $total = (float) $query->sum('jml_perolehan');

                $dailyData[] = [
                    'day'   => $day,
                    'date'  => $date,
                    'total' => $total,
                    'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
                ];
            }

            $series[] = [
                'label'       => $period->label,
                'masehi_year' => $period->masehi_year,
                'hijri_year'  => $period->hijri_year,
                'data'        => $dailyData,
            ];
        }

        return response()->json(['series' => $series]);
    }

    // ========================================
    // API: Opsi Filter (Tim & CS list)
    // ========================================

    public function apiOptions()
    {
        $periods = RamadhanPeriod::chronological()->get();

        // Collect unique tim & CS from all ramadhan periods
        $query = LaporanPerolehan::query()
            ->where(function ($q) use ($periods) {
                foreach ($periods as $period) {
                    $q->orWhereBetween('tanggal', [
                        $period->start_date->format('Y-m-d'),
                        $period->end_date->format('Y-m-d'),
                    ]);
                }
            });

        $timList = (clone $query)->select('tim')
            ->whereNotNull('tim')
            ->where('tim', '!=', '')
            ->distinct()
            ->orderBy('tim')
            ->pluck('tim');

        $csList = (clone $query)->select('nama_cs')
            ->whereNotNull('nama_cs')
            ->where('nama_cs', '!=', '')
            ->distinct()
            ->orderBy('nama_cs')
            ->pluck('nama_cs');

        return response()->json([
            'tim_list' => $timList,
            'cs_list'  => $csList,
        ]);
    }
}
