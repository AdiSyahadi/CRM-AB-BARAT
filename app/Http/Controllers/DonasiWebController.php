<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonasiWebController extends Controller
{
    /**
     * Database connection for WordPress donations
     */
    protected $connection = 'mysql_second';
    protected $table = 'wp_dja_donate';
    protected $campaignTable = 'wp_dja_campaign';
    protected $categoryTable = 'wp_dja_category';

    /**
     * Max main_donate threshold — filter out corrupt records (phone numbers stored as nominal)
     */
    protected $maxDonate = 10000000;

    // ============================================================
    // VIEW
    // ============================================================

    /**
     * Display the Donasi Website analytics dashboard
     */
    public function index()
    {
        return view('donasi-web.index');
    }

    // ============================================================
    // API ENDPOINTS
    // ============================================================

    /**
     * API: Overview statistics (stat cards)
     */
    public function apiOverviewStats(Request $request)
    {
        $period = $request->get('period', '30d');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $query = $this->baseQuery($period, $status, $campaign, $request);
        $prevQuery = $this->baseQuery($period, $status, $campaign, $request, true);

        // Current period stats
        $current = $query->selectRaw('
            COALESCE(SUM(main_donate), 0) as total_amount,
            COUNT(*) as total_transactions,
            COALESCE(AVG(main_donate), 0) as avg_amount,
            COUNT(DISTINCT whatsapp) as unique_donors
        ')->first();

        // Total (including pending) for conversion rate
        $totalAll = $this->baseQuery($period, null, $campaign, $request)
            ->count();
        $totalPaid = $this->baseQuery($period, '1', $campaign, $request)
            ->count();

        // Previous period stats for trend comparison
        $previous = $prevQuery->selectRaw('
            COALESCE(SUM(main_donate), 0) as total_amount,
            COUNT(*) as total_transactions,
            COALESCE(AVG(main_donate), 0) as avg_amount,
            COUNT(DISTINCT whatsapp) as unique_donors
        ')->first();

        return response()->json([
            'total_amount' => (float) $current->total_amount,
            'total_transactions' => (int) $current->total_transactions,
            'avg_amount' => round((float) $current->avg_amount),
            'unique_donors' => (int) $current->unique_donors,
            'conversion_rate' => $totalAll > 0 ? round(($totalPaid / $totalAll) * 100, 1) : 0,
            'trends' => [
                'amount_change' => $this->calcChange($current->total_amount, $previous->total_amount),
                'transactions_change' => $this->calcChange($current->total_transactions, $previous->total_transactions),
                'avg_change' => $this->calcChange($current->avg_amount, $previous->avg_amount),
                'donors_change' => $this->calcChange($current->unique_donors, $previous->unique_donors),
            ],
        ]);
    }

    /**
     * API: Trend data for line/bar chart
     */
    public function apiTrendData(Request $request)
    {
        $groupBy = $request->get('group_by', 'monthly');
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $format = match ($groupBy) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-W%v',
            default => '%Y-%m',
        };

        $data = $this->baseQuery($period, $status, $campaign, $request)
            ->selectRaw("DATE_FORMAT(created_at, ?) as period_label, COUNT(*) as count, SUM(main_donate) as total, AVG(main_donate) as avg_nominal", [$format])
            ->groupBy('period_label')
            ->orderBy('period_label')
            ->get();

        // Best month & best day
        $bestMonth = $this->conn()->where('main_donate', '<=', $this->maxDonate)
            ->where('status', 1)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as cnt, SUM(main_donate) as total")
            ->groupBy('m')->orderByDesc('total')->first();

        $bestDay = $this->conn()->where('main_donate', '<=', $this->maxDonate)
            ->where('status', 1)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as d, COUNT(*) as cnt, SUM(main_donate) as total")
            ->groupBy('d')->orderByDesc('total')->first();

        // MoM growth
        $thisMonth = $this->conn()->where('main_donate', '<=', $this->maxDonate)
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('main_donate');

        $lastMonth = $this->conn()->where('main_donate', '<=', $this->maxDonate)
            ->where('status', 1)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('main_donate');

        // Prediction for this month
        $dayOfMonth = now()->day;
        $daysInMonth = now()->daysInMonth;
        $predicted = $dayOfMonth > 0 ? round(($thisMonth / $dayOfMonth) * $daysInMonth) : 0;

        return response()->json([
            'labels' => $data->pluck('period_label'),
            'counts' => $data->pluck('count'),
            'totals' => $data->pluck('total'),
            'averages' => $data->map(fn($d) => round($d->avg_nominal)),
            'best_month' => $bestMonth?->m ?? '-',
            'best_month_amount' => (float) ($bestMonth?->total ?? 0),
            'best_day' => $bestDay?->d ?? '-',
            'best_day_amount' => (float) ($bestDay?->total ?? 0),
            'mom_growth' => $this->calcChange($thisMonth, $lastMonth),
            'predicted_this_month' => $predicted,
        ]);
    }

    /**
     * API: Campaign breakdown
     */
    public function apiCampaignBreakdown(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');

        // JOIN with wp_dja_campaign for title and category
        $campaigns = $this->baseQuery($period, $status, 'all', $request)
            ->leftJoin($this->campaignTable . ' as c', $this->table . '.campaign_id', '=', 'c.campaign_id')
            ->leftJoin($this->categoryTable . ' as cat', 'c.category_id', '=', 'cat.id')
            ->selectRaw($this->table . '.campaign_id, c.title as campaign_title, cat.category as category_name, c.image_url, COUNT(*) as total_donations, SUM(' . $this->table . '.main_donate) as total_amount, AVG(' . $this->table . '.main_donate) as avg_amount, COUNT(DISTINCT ' . $this->table . '.whatsapp) as unique_donors')
            ->groupBy($this->table . '.campaign_id', 'c.title', 'cat.category', 'c.image_url')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($c) {
                $c->campaign_title = $c->campaign_title
                    ? html_entity_decode($c->campaign_title, ENT_QUOTES, 'UTF-8')
                    : $c->campaign_id;
                return $c;
            });

        // Monthly trend per campaign (top 3)
        $topCampaignIds = $campaigns->take(3)->pluck('campaign_id');
        $campaignTrends = [];
        foreach ($topCampaignIds as $cid) {
            $trend = $this->baseQuery($period, $status, $cid, $request)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(main_donate) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            $campaignTrends[$cid] = $trend;
        }

        return response()->json([
            'campaigns' => $campaigns,
            'campaign_trends' => $campaignTrends,
            'total_campaigns' => $campaigns->count(),
        ]);
    }

    /**
     * API: Payment analytics
     */
    public function apiPaymentAnalytics(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $base = fn() => $this->baseQuery($period, $status, $campaign, $request);

        // Payment method breakdown
        $methods = (clone $base())->selectRaw('payment_method, COUNT(*) as count, SUM(main_donate) as total')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->map(function ($m) {
                $m->payment_method = match ($m->payment_method) {
                    'va' => 'Virtual Account',
                    'instant' => 'Instant (QRIS/E-Wallet)',
                    'transfer' => 'Transfer Manual',
                    default => $m->payment_method ?: '(Lainnya)',
                };
                return $m;
            });

        // Bank/channel breakdown
        $banks = (clone $base())->selectRaw('payment_code, COUNT(*) as count, SUM(main_donate) as total')
            ->groupBy('payment_code')
            ->orderByDesc('count')
            ->get()
            ->map(function ($b) {
                $b->payment_code = strtoupper($b->payment_code ?: 'LAINNYA');
                return $b;
            });

        // Gateway breakdown
        $gateways = (clone $base())->selectRaw("COALESCE(NULLIF(payment_gateway,''), 'manual') as gateway, COUNT(*) as count, SUM(main_donate) as total")
            ->groupBy('gateway')
            ->orderByDesc('count')
            ->get();

        // Process by
        $processBy = (clone $base())->selectRaw("COALESCE(NULLIF(process_by,''), 'pending') as processor, COUNT(*) as count")
            ->groupBy('processor')
            ->orderByDesc('count')
            ->get();

        // Image confirmation
        $imgConfirmed = (clone $base())->whereNotNull('img_confirmation_status')
            ->where('img_confirmation_status', 1)->count();
        $imgTotal = (clone $base())->count();

        // Nominal distribution (histogram)
        $ranges = [
            ['min' => 0, 'max' => 0, 'label' => 'Rp 0'],
            ['min' => 1, 'max' => 50000, 'label' => '1 - 50K'],
            ['min' => 50001, 'max' => 100000, 'label' => '50K - 100K'],
            ['min' => 100001, 'max' => 200000, 'label' => '100K - 200K'],
            ['min' => 200001, 'max' => 500000, 'label' => '200K - 500K'],
            ['min' => 500001, 'max' => 1000000, 'label' => '500K - 1M'],
            ['min' => 1000001, 'max' => 5000000, 'label' => '1M - 5M'],
            ['min' => 5000001, 'max' => 10000000, 'label' => '5M - 10M'],
        ];

        $nominalDist = [];
        foreach ($ranges as $r) {
            $cnt = (clone $base())->whereBetween('nominal', [$r['min'], $r['max']])->count();
            $total = (clone $base())->whereBetween('nominal', [$r['min'], $r['max']])->sum('nominal');
            $nominalDist[] = [
                'label' => $r['label'],
                'count' => $cnt,
                'total' => (float) $total,
            ];
        }

        return response()->json([
            'methods' => $methods,
            'banks' => $banks,
            'gateways' => $gateways,
            'process_by' => $processBy,
            'img_confirmed' => $imgConfirmed,
            'img_total' => $imgTotal,
            'nominal_distribution' => $nominalDist,
        ]);
    }

    /**
     * API: Donor insights
     */
    public function apiDonorInsights(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $base = fn() => $this->baseQuery($period, $status, $campaign, $request);

        // Unique counts
        $uniqueEmails = (clone $base())->whereNotNull('email')->where('email', '!=', '')->distinct()->count('email');
        $uniquePhones = (clone $base())->whereNotNull('whatsapp')->where('whatsapp', '!=', '')->distinct()->count('whatsapp');

        // Repeat donors
        $repeatDonors = DB::connection($this->connection)
            ->select("SELECT COUNT(*) as cnt FROM (SELECT whatsapp FROM {$this->table} WHERE whatsapp IS NOT NULL AND whatsapp != '' AND main_donate <= {$this->maxDonate} " . ($status !== null ? "AND status = {$status} " : '') . "GROUP BY whatsapp HAVING COUNT(*) > 1) t");
        $repeatCount = $repeatDonors[0]->cnt ?? 0;

        // Anonim rate
        $anonCount = (clone $base())->where('anonim', 1)->count();
        $totalCount = (clone $base())->count();
        $anonRate = $totalCount > 0 ? round(($anonCount / $totalCount) * 100, 1) : 0;

        // With comment/doa
        $withComment = (clone $base())->whereNotNull('comment')->where('comment', '!=', '')->count();

        // Sapaan distribution
        $sapaan = (clone $base())->selectRaw("COALESCE(NULLIF(sapaan,''), 'Tidak Diketahui') as sapaan, COUNT(*) as count")
            ->groupBy('sapaan')
            ->orderByDesc('count')
            ->get();

        // Top donors (masked for privacy)
        $topDonors = (clone $base())->selectRaw('name, whatsapp, email, COUNT(*) as total_donations, SUM(main_donate) as total_amount, MAX(created_at) as last_donation')
            ->whereNotNull('name')->where('name', '!=', '')
            ->groupBy('name', 'whatsapp', 'email')
            ->orderByDesc('total_amount')
            ->limit(15)
            ->get()
            ->map(function ($d, $i) {
                return [
                    'rank' => $i + 1,
                    'name' => $this->maskName($d->name),
                    'whatsapp' => $this->maskPhone($d->whatsapp),
                    'total_donations' => (int) $d->total_donations,
                    'total_amount' => (float) $d->total_amount,
                    'last_donation' => $d->last_donation,
                ];
            });

        // Follow-up funnel (f1 - f5)
        $funnel = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = $this->conn()->where('main_donate', '<=', $this->maxDonate)
                ->where("f{$i}", 1)->count();
            $funnel["f{$i}"] = $count;
        }

        return response()->json([
            'unique_emails' => $uniqueEmails,
            'unique_phones' => $uniquePhones,
            'repeat_donors' => $repeatCount,
            'anonim_count' => $anonCount,
            'anonim_rate' => $anonRate,
            'with_comment' => $withComment,
            'comment_rate' => $totalCount > 0 ? round(($withComment / $totalCount) * 100, 1) : 0,
            'total_count' => $totalCount,
            'sapaan' => $sapaan,
            'top_donors' => $topDonors,
            'funnel' => $funnel,
        ]);
    }

    /**
     * API: Traffic & UTM analytics
     */
    public function apiTrafficUtm(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $base = fn() => $this->baseQuery($period, $status, $campaign, $request);

        // UTM Source breakdown
        $utmSources = (clone $base())
            ->selectRaw("COALESCE(NULLIF(utm_source,''), 'direct') as source, COUNT(*) as count, SUM(main_donate) as total, AVG(main_donate) as avg_amount, COUNT(DISTINCT whatsapp) as unique_donors")
            ->groupBy('source')
            ->orderByDesc('count')
            ->get()
            ->map(function ($s) {
                $s->source = match ($s->source) {
                    'ig' => 'Instagram',
                    'fb' => 'Facebook',
                    'direct' => 'Direct / Organic',
                    default => ucfirst($s->source),
                };
                return $s;
            });

        // UTM Medium breakdown
        $utmMediums = (clone $base())
            ->selectRaw("COALESCE(NULLIF(utm_medium,''), 'none') as medium, COUNT(*) as count, SUM(main_donate) as total")
            ->groupBy('medium')
            ->orderByDesc('count')
            ->get();

        // Device breakdown
        $devices = (clone $base())
            ->selectRaw("COALESCE(NULLIF(mobdesktop,''), 'unknown') as device, COUNT(*) as count")
            ->groupBy('device')
            ->orderByDesc('count')
            ->get();

        // OS breakdown
        $os = (clone $base())
            ->selectRaw("COALESCE(NULLIF(os,''), 'Unknown') as os_name, COUNT(*) as count")
            ->groupBy('os_name')
            ->orderByDesc('count')
            ->get();

        // Monthly trend per source (top 3 sources)
        $sourceTrend = (clone $base())
            ->whereIn('utm_source', ['ig', 'fb'])
            ->selectRaw("utm_source as source, DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(main_donate) as total")
            ->groupBy('source', 'month')
            ->orderBy('month')
            ->get()
            ->groupBy('source');

        return response()->json([
            'utm_sources' => $utmSources,
            'utm_mediums' => $utmMediums,
            'devices' => $devices,
            'os' => $os,
            'source_trend' => $sourceTrend,
        ]);
    }

    /**
     * API: Program/package breakdown (from info_package2 JSON)
     */
    public function apiProgramPackages(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $rows = $this->baseQuery($period, $status, $campaign, $request)
            ->whereNotNull('info_package2')
            ->where('info_package2', '!=', '[]')
            ->where('info_package2', '!=', '')
            ->select('info_package2', 'nominal')
            ->get();

        $packages = [];
        foreach ($rows as $row) {
            $arr = json_decode($row->info_package2, true);
            if (is_array($arr)) {
                foreach ($arr as $item) {
                    $name = trim($item['package'] ?? 'Unknown');
                    if (!isset($packages[$name])) {
                        $packages[$name] = ['name' => $name, 'count' => 0, 'total' => 0];
                    }
                    $packages[$name]['count']++;
                    // Parse nominal from item if available
                    $nomStr = $item['nominal'] ?? '';
                    $nom = (int) preg_replace('/[^0-9]/', '', $nomStr);
                    $packages[$name]['total'] += $nom;
                }
            }
        }

        // Sort by count desc
        usort($packages, fn($a, $b) => $b['count'] - $a['count']);

        return response()->json([
            'packages' => array_values($packages),
            'total_with_package' => $rows->count(),
        ]);
    }

    /**
     * API: Time patterns (heatmap, hourly, daily)
     */
    public function apiTimePatterns(Request $request)
    {
        $period = $request->get('period', 'all');
        $status = $request->get('status', '1');
        $campaign = $request->get('campaign', 'all');

        $base = fn() => $this->baseQuery($period, $status, $campaign, $request);

        // Heatmap: hour x day-of-week
        $heatmap = (clone $base())
            ->selectRaw('HOUR(created_at) as hour, DAYOFWEEK(created_at) as dow, COUNT(*) as count, SUM(main_donate) as total')
            ->groupBy('hour', 'dow')
            ->orderBy('dow')
            ->orderBy('hour')
            ->get();

        // Format heatmap as grid[dow][hour]
        $grid = [];
        $dayNames = [1 => 'Minggu', 2 => 'Senin', 3 => 'Selasa', 4 => 'Rabu', 5 => 'Kamis', 6 => 'Jumat', 7 => 'Sabtu'];
        for ($dow = 1; $dow <= 7; $dow++) {
            for ($h = 0; $h <= 23; $h++) {
                $grid[$dow][$h] = ['count' => 0, 'total' => 0];
            }
        }
        foreach ($heatmap as $cell) {
            $grid[$cell->dow][$cell->hour] = [
                'count' => (int) $cell->count,
                'total' => (float) $cell->total,
            ];
        }

        // Hourly aggregation
        $hourly = (clone $base())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(main_donate) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Daily aggregation (day of week)
        $daily = (clone $base())
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as count, SUM(main_donate) as total')
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->map(function ($d) use ($dayNames) {
                $d->day_name = $dayNames[$d->dow] ?? '?';
                return $d;
            });

        // Find peak hour and peak day
        $peakHour = $hourly->sortByDesc('count')->first();
        $peakDay = $daily->sortByDesc('count')->first();

        return response()->json([
            'heatmap' => $grid,
            'day_names' => $dayNames,
            'hourly' => $hourly,
            'daily' => $daily,
            'peak_hour' => $peakHour ? (int) $peakHour->hour : null,
            'peak_day' => $peakDay?->day_name ?? null,
        ]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Get base query builder with common filters applied
     */
    private function baseQuery(string $period, ?string $status, string $campaign, Request $request, bool $previous = false)
    {
        $t = $this->table;
        $query = $this->conn()->where("{$t}.main_donate", '<=', $this->maxDonate);

        // Status filter
        if ($status !== null && $status !== 'all') {
            $query->where("{$t}.status", (int) $status);
        }

        // Campaign filter
        if ($campaign !== 'all') {
            $query->where("{$t}.campaign_id", $campaign);
        }

        // Period filter
        $dates = $this->resolvePeriodDates($period, $request, $previous);
        if ($dates) {
            $query->whereBetween("{$t}.created_at", [$dates['start'], $dates['end']]);
        }

        return $query;
    }

    /**
     * Resolve start/end dates based on period string
     */
    private function resolvePeriodDates(string $period, Request $request, bool $previous = false): ?array
    {
        $now = Carbon::now();

        $ranges = match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'length' => 1,
            ],
            '7d' => [
                'start' => $now->copy()->subDays(7)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'length' => 7,
            ],
            '30d' => [
                'start' => $now->copy()->subDays(30)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'length' => 30,
            ],
            '90d' => [
                'start' => $now->copy()->subDays(90)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'length' => 90,
            ],
            'custom' => [
                'start' => Carbon::parse($request->get('start_date', $now->copy()->subDays(30)->format('Y-m-d')))->startOfDay(),
                'end' => Carbon::parse($request->get('end_date', $now->format('Y-m-d')))->endOfDay(),
                'length' => null,
            ],
            default => null, // 'all' — no date filter
        };

        if (!$ranges) {
            return null;
        }

        if ($previous) {
            $length = $ranges['length'] ?? $ranges['start']->diffInDays($ranges['end']);
            return [
                'start' => $ranges['start']->copy()->subDays($length)->toDateTimeString(),
                'end' => $ranges['start']->copy()->subSecond()->toDateTimeString(),
            ];
        }

        return [
            'start' => $ranges['start']->toDateTimeString(),
            'end' => $ranges['end']->toDateTimeString(),
        ];
    }

    /**
     * Get a fresh query builder for wp_dja_donate
     */
    private function conn()
    {
        return DB::connection($this->connection)->table($this->table);
    }

    /**
     * Get campaign name mapping (campaign_id => title)
     */
    private function getCampaignMap(): array
    {
        return DB::connection($this->connection)
            ->table($this->campaignTable)
            ->pluck('title', 'campaign_id')
            ->map(fn($t) => html_entity_decode($t, ENT_QUOTES, 'UTF-8'))
            ->toArray();
    }

    /**
     * Calculate percentage change between two values
     */
    private function calcChange($current, $previous): float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Mask donor name for privacy (show first 3 chars + ***)
     */
    private function maskName(string $name): string
    {
        if (mb_strlen($name) <= 3) {
            return $name . '***';
        }
        return mb_substr($name, 0, 3) . str_repeat('*', min(mb_strlen($name) - 3, 5));
    }

    /**
     * Mask phone number for privacy (show first 4 + last 3 digits)
     */
    private function maskPhone(?string $phone): string
    {
        if (!$phone || strlen($phone) < 7) {
            return $phone ?? '-';
        }
        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }
}
