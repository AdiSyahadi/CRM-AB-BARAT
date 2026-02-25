<x-layouts.app active="performa-bulanan" title="Performa Bulanan - CRM Dashboard" xData="performaBulananApp()" :chartjs="true">

@push('styles')
<style>
    .pivot-table { border-collapse: separate; border-spacing: 0; }
    .pivot-table thead th { position: sticky; top: 0; z-index: 10; background: #f9fafb; }
    .pivot-table th, .pivot-table td { white-space: nowrap; }
    .pivot-table tbody tr:hover { background: rgba(5, 150, 105, 0.04); }
    .month-cell { min-width: 90px; text-align: right; }
    .highlight-month { background: rgba(5, 150, 105, 0.08) !important; }
    .rank-1 { color: #D97706; }
    .rank-2 { color: #64748B; }
    .rank-3 { color: #92400E; }
    .tab-active { background: linear-gradient(135deg, #059669, #10B981); color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.3); }
    .tab-inactive { color: #6b7280; background: white; }
    .tab-inactive:hover { color: #059669; background: #f0fdf4; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .fade-in { animation: fadeIn 0.3s ease; }
</style>
@endpush

<div class="max-w-7xl mx-auto space-y-5">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Performa Bulanan</h1>
            <p class="text-sm text-gray-500 mt-1">Analisis perolehan bulanan per CS dan per Tim</p>
        </div>
        <button @click="exportData()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 shadow-sm transition">
            <i class="bi bi-download"></i>
            <span>Export CSV</span>
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3 items-center">
            <!-- Year Selector -->
            <div class="flex items-center gap-2">
                <button @click="changeYear(-1)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <select x-model="selectedYear" @change="refreshAll()"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold bg-white focus:border-primary-400 outline-none transition text-center">
                    @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <button @click="changeYear(1)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            <!-- Team Filter -->
            <select x-model="filterTeam" @change="refreshAll()"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:border-primary-400 outline-none transition">
                <option value="all">Semua Tim</option>
                @foreach($teams as $team)
                <option value="{{ $team }}">{{ $team }}</option>
                @endforeach
            </select>

            <!-- Mode Toggle -->
            <div class="flex bg-gray-100 rounded-xl p-1 ml-auto">
                <button @click="viewMode = 'cs'; refreshAll()"
                        :class="viewMode === 'cs' ? 'tab-active' : 'tab-inactive'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition">
                    <i class="bi bi-person-fill mr-1"></i> Per CS
                </button>
                <button @click="viewMode = 'team'; refreshAll()"
                        :class="viewMode === 'team' ? 'tab-active' : 'tab-inactive'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition">
                    <i class="bi bi-diagram-3-fill mr-1"></i> Per Tim
                </button>
            </div>

            <!-- Compare Year -->
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Bandingkan:</label>
                <select x-model="compareYear" @change="loadTrend()"
                        class="border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:border-primary-400 outline-none transition">
                    <option value="">Tidak</option>
                    @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-cash-stack text-primary-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Tahun</span>
            </div>
            <div class="text-xl font-bold text-gray-800" x-text="'Rp ' + Number(grandTotal?.total || 0).toLocaleString('id-ID')"></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-calendar3 text-blue-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Rata-rata/Bulan</span>
            </div>
            <div class="text-xl font-bold text-gray-800" x-text="'Rp ' + Math.round((grandTotal?.total || 0) / 12).toLocaleString('id-ID')"></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-graph-up-arrow text-emerald-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Bulan Terbaik</span>
            </div>
            <div class="text-lg font-bold text-gray-800" x-text="bestMonth?.label || '-'"></div>
            <div class="text-[10px] text-gray-400" x-text="'Rp ' + Number(bestMonth?.value || 0).toLocaleString('id-ID')"></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-people-fill text-amber-600 text-sm"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 uppercase">Jumlah CS/Tim</span>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="pivotData.length"></div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700"><i class="bi bi-graph-up text-primary-500 mr-1"></i> Trend Perolehan Bulanan</h3>
            <div class="flex gap-2">
                <button @click="chartMetric = 'perolehan'; renderTrendChart()" :class="chartMetric === 'perolehan' ? 'bg-primary-100 text-primary-700' : 'text-gray-400'"
                        class="text-[10px] px-3 py-1 rounded-lg font-semibold transition">Perolehan</button>
                <button @click="chartMetric = 'laporan'; renderTrendChart()" :class="chartMetric === 'laporan' ? 'bg-primary-100 text-primary-700' : 'text-gray-400'"
                        class="text-[10px] px-3 py-1 rounded-lg font-semibold transition">Laporan</button>
                <button @click="chartMetric = 'donatur'; renderTrendChart()" :class="chartMetric === 'donatur' ? 'bg-primary-100 text-primary-700' : 'text-gray-400'"
                        class="text-[10px] px-3 py-1 rounded-lg font-semibold transition">Donatur</button>
            </div>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Top Performers (Month Selector) -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700"><i class="bi bi-trophy-fill text-amber-500 mr-1"></i> Top Performer</h3>
            <select x-model="topMonth" @change="loadTopPerformers()"
                    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:border-primary-400 outline-none">
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
        </div>
        <div x-show="loadingTop" class="text-center py-6 text-gray-400 text-sm">Memuat...</div>
        <div x-show="!loadingTop" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <template x-for="(p, i) in topPerformers" :key="i">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-2 right-3 text-3xl font-black opacity-10" :class="i === 0 ? 'rank-1' : (i === 1 ? 'rank-2' : (i === 2 ? 'rank-3' : 'text-gray-300'))"
                         x-text="'#' + p.rank"></div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-[10px]" x-text="(p.nama_cs || '').substring(0,2).toUpperCase()"></span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-800 leading-tight truncate" x-text="p.nama_cs" style="max-width: 130px"></p>
                            <p class="text-[9px] text-gray-400" x-text="p.tim"></p>
                        </div>
                    </div>
                    <p class="text-sm font-bold text-primary-600" x-text="p.total_perolehan_formatted"></p>
                    <div class="flex items-center gap-3 mt-1 text-[9px] text-gray-400">
                        <span><span x-text="p.total_laporan"></span> lpr</span>
                        <span><span x-text="p.total_donatur"></span> dnr</span>
                        <span><span x-text="p.hari_aktif"></span> hr</span>
                    </div>
                </div>
            </template>
        </div>
        <template x-if="!loadingTop && topPerformers.length === 0">
            <p class="text-center text-gray-400 text-sm py-6">Tidak ada data untuk bulan ini</p>
        </template>
    </div>

    <!-- Pivot Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="bi bi-table text-primary-500 mr-1"></i>
                Tabel Perolehan <span x-text="selectedYear"></span>
                <span x-show="viewMode === 'cs'" class="text-gray-400 font-normal">Per CS</span>
                <span x-show="viewMode === 'team'" class="text-gray-400 font-normal">Per Tim</span>
            </h3>
            <div class="flex items-center gap-2">
                <label class="text-[10px] text-gray-400">Cari:</label>
                <input type="text" x-model="pivotSearch" placeholder="Nama..." class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 w-40 focus:border-primary-400 outline-none">
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loadingPivot" class="text-center py-10 text-gray-400 text-sm">
            <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memuat data...
        </div>

        <!-- Table -->
        <div x-show="!loadingPivot" class="overflow-x-auto">
            <table class="pivot-table w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase">
                        <th class="text-left px-4 py-3 font-medium sticky left-0 bg-gray-50 z-20 min-w-[40px]">#</th>
                        <th class="text-left px-4 py-3 font-medium sticky left-10 bg-gray-50 z-20 min-w-[180px]" x-text="viewMode === 'cs' ? 'Nama CS' : 'Tim'"></th>
                        <template x-if="viewMode === 'cs'"><th class="text-left px-4 py-3 font-medium">Tim</th></template>
                        <template x-for="(m, i) in monthLabels" :key="i">
                            <th class="month-cell px-4 py-3 font-medium cursor-pointer hover:text-primary-600"
                                :class="highlightMonth === i && 'highlight-month'"
                                @click="highlightMonth = highlightMonth === i ? -1 : i"
                                x-text="m"></th>
                        </template>
                        <th class="month-cell px-4 py-3 font-semibold text-primary-600 bg-primary-50/50">Total</th>
                        <th class="px-4 py-3 font-medium text-center">Lpr</th>
                        <th class="px-4 py-3 font-medium text-center">Dnr</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in filteredPivotData" :key="idx">
                        <tr class="border-t border-gray-50">
                            <td class="px-4 py-2.5 text-gray-400 sticky left-0 bg-white z-10" x-text="idx + 1"></td>
                            <td class="px-4 py-2.5 font-medium text-gray-800 sticky left-10 bg-white z-10">
                                <span x-text="row.name" class="truncate block" style="max-width: 170px"></span>
                            </td>
                            <template x-if="viewMode === 'cs'"><td class="px-4 py-2.5 text-gray-400" x-text="row.team || '-'"></td></template>
                            <template x-for="(mk, mi) in monthKeys" :key="mi">
                                <td class="month-cell px-4 py-2.5 font-mono"
                                    :class="[
                                        highlightMonth === mi && 'highlight-month',
                                        row[mk] > 0 ? 'text-gray-800' : 'text-gray-300',
                                        isTopInMonth(row, mk) && 'font-bold text-primary-600'
                                    ]"
                                    x-text="row[mk] > 0 ? Number(row[mk]).toLocaleString('id-ID') : '-'"></td>
                            </template>
                            <td class="month-cell px-4 py-2.5 font-bold text-primary-700 bg-primary-50/30" x-text="Number(row.total).toLocaleString('id-ID')"></td>
                            <td class="px-4 py-2.5 text-center text-gray-500" x-text="row.total_laporan?.toLocaleString('id-ID')"></td>
                            <td class="px-4 py-2.5 text-center text-gray-500" x-text="row.total_donatur?.toLocaleString('id-ID')"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot x-show="grandTotal">
                    <tr class="border-t-2 border-primary-200 bg-primary-50/30 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-primary-50/30 z-10"></td>
                        <td class="px-4 py-3 text-primary-700 sticky left-10 bg-primary-50/30 z-10" :colspan="viewMode === 'cs' ? 1 : 1">GRAND TOTAL</td>
                        <template x-if="viewMode === 'cs'"><td class="px-4 py-3"></td></template>
                        <template x-for="(mk, mi) in monthKeys" :key="'gt-'+mi">
                            <td class="month-cell px-4 py-3 text-primary-700"
                                :class="highlightMonth === mi && 'highlight-month'"
                                x-text="Number(grandTotal?.[mk] || 0).toLocaleString('id-ID')"></td>
                        </template>
                        <td class="month-cell px-4 py-3 text-primary-800 bg-primary-100/50" x-text="Number(grandTotal?.total || 0).toLocaleString('id-ID')"></td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function performaBulananApp() {
    return {
        sidebarOpen: false,
        selectedYear: {{ $years->first() ?? now()->year }},
        filterTeam: 'all',
        viewMode: 'cs',
        compareYear: '',
        chartMetric: 'perolehan',
        topMonth: {{ now()->month }},
        pivotSearch: '',
        highlightMonth: -1,

        pivotData: [],
        grandTotal: null,
        monthLabels: [],
        trendData: null,
        topPerformers: [],

        loadingPivot: true,
        loadingTop: true,

        // Charts
        trendChart: null,

        monthKeys: ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'],

        get filteredPivotData() {
            if (!this.pivotSearch) return this.pivotData;
            const q = this.pivotSearch.toLowerCase();
            return this.pivotData.filter(r => r.name.toLowerCase().includes(q));
        },

        get bestMonth() {
            if (!this.grandTotal) return null;
            const mLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            let best = { label: '-', value: 0 };
            this.monthKeys.forEach((k, i) => {
                const v = parseInt(this.grandTotal[k]) || 0;
                if (v > best.value) {
                    best = { label: mLabels[i] + ' ' + this.selectedYear, value: v };
                }
            });
            return best;
        },

        init() {
            this.refreshAll();
        },

        async refreshAll() {
            this.loadPivot();
            this.loadTrend();
            this.loadTopPerformers();
        },

        changeYear(delta) {
            const years = @json($years);
            const idx = years.indexOf(parseInt(this.selectedYear));
            const newIdx = idx - delta; // years are desc sorted
            if (newIdx >= 0 && newIdx < years.length) {
                this.selectedYear = years[newIdx];
                this.refreshAll();
            }
        },

        async loadPivot() {
            this.loadingPivot = true;
            try {
                const params = new URLSearchParams({
                    year: this.selectedYear,
                    team: this.filterTeam,
                    mode: this.viewMode,
                });
                const res = await fetch(`/api/customer-service/monthly-pivot?${params}`);
                const json = await res.json();
                if (json.success) {
                    this.pivotData = json.data;
                    this.grandTotal = json.grand_total;
                    this.monthLabels = json.months;
                }
            } catch (e) { console.error('Pivot error:', e); }
            this.loadingPivot = false;
        },

        async loadTrend() {
            try {
                const params = new URLSearchParams({
                    year: this.selectedYear,
                    team: this.filterTeam,
                    ...(this.compareYear ? { compare_year: this.compareYear } : {}),
                });
                const res = await fetch(`/api/customer-service/monthly-trend?${params}`);
                const json = await res.json();
                if (json.success) {
                    this.trendData = json.data;
                    this.$nextTick(() => this.renderTrendChart());
                }
            } catch (e) { console.error('Trend error:', e); }
        },

        async loadTopPerformers() {
            this.loadingTop = true;
            try {
                const params = new URLSearchParams({
                    year: this.selectedYear,
                    month: this.topMonth,
                    team: this.filterTeam,
                    limit: 10,
                });
                const res = await fetch(`/api/customer-service/top-performers?${params}`);
                const json = await res.json();
                if (json.success) this.topPerformers = json.data;
            } catch (e) { console.error('Top error:', e); }
            this.loadingTop = false;
        },

        renderTrendChart() {
            if (!this.trendData) return;
            if (this.trendChart) this.trendChart.destroy();
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;

            const metric = this.chartMetric;
            const datasets = [{
                label: this.trendData.current.year,
                data: this.trendData.current[metric],
                borderColor: 'rgb(5, 150, 105)',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(5, 150, 105)',
                pointRadius: 4,
                pointHoverRadius: 6,
            }];

            if (this.trendData.compare) {
                datasets.push({
                    label: this.trendData.compare.year,
                    data: this.trendData.compare[metric],
                    borderColor: 'rgb(156, 163, 175)',
                    backgroundColor: 'rgba(156, 163, 175, 0.05)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(156, 163, 175)',
                    pointRadius: 3,
                });
            }

            const yCallback = metric === 'perolehan'
                ? (v => { if (v >= 1000000) return (v/1000000).toFixed(0) + 'Jt'; if (v >= 1000) return (v/1000).toFixed(0) + 'K'; return v; })
                : (v => v.toLocaleString('id-ID'));

            this.trendChart = new Chart(ctx, {
                type: 'line',
                data: { labels: this.trendData.labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 20, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.raw;
                                    if (metric === 'perolehan') return ctx.dataset.label + ': Rp ' + v.toLocaleString('id-ID');
                                    return ctx.dataset.label + ': ' + v.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: yCallback } },
                        x: { grid: { display: false } }
                    }
                }
            });
        },

        isTopInMonth(row, monthKey) {
            if (!this.pivotData.length) return false;
            const max = Math.max(...this.pivotData.map(r => r[monthKey] || 0));
            return max > 0 && row[monthKey] === max;
        },

        exportData() {
            const params = new URLSearchParams({
                year: this.selectedYear,
                team: this.filterTeam,
                mode: this.viewMode,
            });
            window.open(`/api/customer-service/export-monthly?${params}`, '_blank');
        },
    };
}
</script>
@endpush

</x-layouts.app>
