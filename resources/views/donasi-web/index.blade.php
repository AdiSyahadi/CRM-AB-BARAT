<x-layouts.app active="donasi-web" title="Donasi Website - Abbarat Dashboard" xData="donasiWebApp()" :chartjs="true">

@push('styles')
<style>
    /* Tab navigation */
    .tab-pill { transition: all 0.2s ease; }
    .tab-pill.active { background: linear-gradient(135deg, #10B981, #059669); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
    
    /* Stat cards */
    .dw-stat-card { transition: all 0.3s ease; }
    .dw-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    
    /* Heatmap cell */
    .heatmap-cell { transition: all 0.15s ease; border-radius: 4px; }
    .heatmap-cell:hover { transform: scale(1.15); z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
    
    /* Funnel */
    .funnel-bar { transition: width 0.8s ease-out; }
    
    /* Leaderboard */
    .donor-row { transition: all 0.2s ease; }
    .donor-row:hover { background: linear-gradient(90deg, rgba(16,185,129,0.06) 0%, transparent 100%); }
    
    /* Skeleton loading */
    .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmerSkeleton 1.5s infinite; border-radius: 8px; }
    @keyframes shimmerSkeleton { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    
    /* Counter animation */
    .counter-value { font-variant-numeric: tabular-nums; }
</style>
@endpush

@push('before-sidebar')
    <!-- Loading Modal -->
    <div x-show="loading && !dataLoaded"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-md z-[9999] flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 max-w-md mx-4 text-center">
            <div class="relative w-24 h-24 mx-auto mb-6">
                <div class="absolute inset-0 bg-primary-100 rounded-full animate-ping opacity-30"></div>
                <div class="relative w-24 h-24 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center shadow-lg shadow-primary-500/40">
                    <i class="bi bi-globe2 text-white text-4xl"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Donasi Website</h2>
            <p class="text-gray-500 mb-6">Memuat data analitik...</p>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full animate-pulse" style="width: 70%"></div>
            </div>
            <div class="flex justify-center gap-1.5 mt-4">
                <template x-for="i in 3" :key="i">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" :style="'animation-delay: ' + (i * 0.15) + 's'"></div>
                </template>
            </div>
        </div>
    </div>
@endpush

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
        <div class="flex items-center justify-between px-4 md:px-6 py-3">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl" aria-label="Toggle menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg md:text-xl font-bold text-gray-800">Donasi Website</h1>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-full">ANALYTICS</span>
                    </div>
                    <p class="text-xs text-gray-500">Data dari lazalbahjahbarat.id</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="refreshAll()" :disabled="loading" class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition" :class="loading && 'animate-spin'" aria-label="Refresh data">
                    <i class="bi bi-arrow-clockwise text-lg"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="p-4 md:p-6 space-y-5">

        <!-- ========== GLOBAL FILTERS ========== -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Period Preset Buttons -->
                <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                    <template x-for="p in periods" :key="p.value">
                        <button @click="setPeriod(p.value)"
                                :class="period === p.value ? 'bg-white text-primary-700 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1.5 rounded-lg text-xs transition" x-text="p.label"></button>
                    </template>
                </div>

                <!-- Campaign Filter -->
                <select x-model="campaignFilter" @change="refreshAll()" class="filter-select text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                    <option value="all">Semua Campaign</option>
                    <template x-for="c in campaignList" :key="c.id">
                        <option :value="c.id" x-text="c.title"></option>
                    </template>
                </select>

                <!-- Status Filter -->
                <select x-model="statusFilter" @change="refreshAll()" class="filter-select text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                    <option value="1">Berhasil</option>
                    <option value="all">Semua Status</option>
                </select>
            </div>
        </div>

        <!-- ========== STAT CARDS (always visible) ========== -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Donasi -->
            <div class="dw-stat-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -mr-6 -mt-6"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-cash-stack text-emerald-600 text-sm"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">Total Donasi</span>
                    </div>
                    <div class="text-xl lg:text-2xl font-bold text-gray-800 counter-value" x-text="formatCurrencyShort(overview.total_amount)">-</div>
                    <div class="flex items-center gap-1 mt-1">
                        <template x-if="overview.trends?.amount_change > 0">
                            <span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="overview.trends.amount_change + '%'"></span></span>
                        </template>
                        <template x-if="overview.trends?.amount_change < 0">
                            <span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.trends.amount_change) + '%'"></span></span>
                        </template>
                        <template x-if="overview.trends?.amount_change == 0">
                            <span class="text-[10px] text-gray-400">—</span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Jumlah Transaksi -->
            <div class="dw-stat-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mt-6"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-receipt text-blue-600 text-sm"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">Transaksi</span>
                    </div>
                    <div class="text-xl lg:text-2xl font-bold text-gray-800 counter-value" x-text="formatNumber(overview.total_transactions)">-</div>
                    <div class="flex items-center gap-1 mt-1">
                        <template x-if="overview.trends?.transactions_change > 0">
                            <span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="overview.trends.transactions_change + '%'"></span></span>
                        </template>
                        <template x-if="overview.trends?.transactions_change < 0">
                            <span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.trends.transactions_change) + '%'"></span></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Rata-rata -->
            <div class="dw-stat-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-violet-50 rounded-full -mr-6 -mt-6"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calculator text-violet-600 text-sm"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">Rata-rata</span>
                    </div>
                    <div class="text-xl lg:text-2xl font-bold text-gray-800 counter-value" x-text="formatCurrencyShort(overview.avg_amount)">-</div>
                    <div class="flex items-center gap-1 mt-1">
                        <template x-if="overview.trends?.avg_change > 0">
                            <span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="overview.trends.avg_change + '%'"></span></span>
                        </template>
                        <template x-if="overview.trends?.avg_change < 0">
                            <span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.trends.avg_change) + '%'"></span></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Donor Unik -->
            <div class="dw-stat-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-full -mr-6 -mt-6"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-people text-amber-600 text-sm"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">Donor Unik</span>
                    </div>
                    <div class="text-xl lg:text-2xl font-bold text-gray-800 counter-value" x-text="formatNumber(overview.unique_donors)">-</div>
                    <div class="flex items-center gap-1 mt-1">
                        <template x-if="overview.trends?.donors_change > 0">
                            <span class="text-[10px] font-semibold text-emerald-600"><i class="bi bi-arrow-up-short"></i><span x-text="overview.trends.donors_change + '%'"></span></span>
                        </template>
                        <template x-if="overview.trends?.donors_change < 0">
                            <span class="text-[10px] font-semibold text-red-500"><i class="bi bi-arrow-down-short"></i><span x-text="Math.abs(overview.trends.donors_change) + '%'"></span></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Conversion Rate -->
            <div class="dw-stat-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 col-span-2 lg:col-span-1 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-rose-50 rounded-full -mr-6 -mt-6"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-funnel text-rose-600 text-sm"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">Conversion</span>
                    </div>
                    <div class="text-xl lg:text-2xl font-bold text-gray-800 counter-value"><span x-text="overview.conversion_rate ?? '-'"></span>%</div>
                    <div class="text-[10px] text-gray-400 mt-1">paid / total</div>
                </div>
            </div>
        </div>

        <!-- ========== TAB NAVIGATION ========== -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 overflow-x-auto">
            <div class="flex gap-1 min-w-max">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="switchTab(tab.id)"
                            :class="activeTab === tab.id ? 'active' : ''"
                            class="tab-pill flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition">
                        <i class="bi" :class="tab.icon"></i>
                        <span x-text="tab.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- ========== TAB: TREND ========== -->
        <div x-show="activeTab === 'trend'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Trend Donasi</h2>
                    <div class="flex bg-gray-100 rounded-lg p-0.5 gap-0.5">
                        <template x-for="g in ['daily','weekly','monthly']" :key="g">
                            <button @click="trendGroupBy = g; fetchTrend()"
                                    :class="trendGroupBy === g ? 'bg-white shadow-sm text-primary-700 font-semibold' : 'text-gray-500'"
                                    class="px-3 py-1 rounded-md text-[11px] transition capitalize" x-text="g === 'daily' ? 'Harian' : g === 'weekly' ? 'Mingguan' : 'Bulanan'"></button>
                        </template>
                    </div>
                </div>
                <div class="relative" style="height: 350px">
                    <canvas id="trendChart"></canvas>
                    <div x-show="tabLoading.trend" class="absolute inset-0 flex items-center justify-center bg-white/60">
                        <div class="w-8 h-8 border-3 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>
            </div>
            <!-- Trend Summary -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Bulan Terbaik</p>
                    <p class="text-sm font-bold text-gray-800 mt-1" x-text="trendData.best_month || '-'"></p>
                    <p class="text-xs text-primary-600" x-text="formatCurrencyShort(trendData.best_month_amount)"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Hari Terbaik</p>
                    <p class="text-sm font-bold text-gray-800 mt-1" x-text="trendData.best_day || '-'"></p>
                    <p class="text-xs text-primary-600" x-text="formatCurrencyShort(trendData.best_day_amount)"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Growth MoM</p>
                    <p class="text-sm font-bold mt-1" :class="(trendData.mom_growth||0) >= 0 ? 'text-emerald-600' : 'text-red-500'" x-text="(trendData.mom_growth > 0 ? '+' : '') + (trendData.mom_growth||0) + '%'"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Prediksi Bulan Ini</p>
                    <p class="text-sm font-bold text-gray-800 mt-1" x-text="formatCurrencyShort(trendData.predicted_this_month)"></p>
                </div>
            </div>
        </div>

        <!-- ========== TAB: CAMPAIGN ========== -->
        <div x-show="activeTab === 'campaign'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid lg:grid-cols-3 gap-4">
                <!-- Doughnut Chart -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Proporsi Campaign</h3>
                    <div class="relative" style="height: 250px">
                        <canvas id="campaignChart"></canvas>
                        <div x-show="tabLoading.campaign" class="absolute inset-0 flex items-center justify-center bg-white/60">
                            <div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </div>
                </div>
                <!-- Campaign Table -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Performa per Campaign</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-400 uppercase border-b border-gray-100">
                                    <th class="py-2 px-2">Campaign</th>
                                    <th class="py-2 px-2">Kategori</th>
                                    <th class="py-2 px-2 text-right">Donasi</th>
                                    <th class="py-2 px-2 text-right">Total</th>
                                    <th class="py-2 px-2 text-right">Avg</th>
                                    <th class="py-2 px-2 text-right">Donor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(c, i) in (campaignData.campaigns || [])" :key="i">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                        <td class="py-2.5 px-2 max-w-[250px]">
                                            <div class="font-medium text-gray-800 truncate" x-text="c.campaign_title"></div>
                                            <div class="text-[10px] text-gray-400 font-mono" x-text="c.campaign_id"></div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            <span x-show="c.category_name" class="px-1.5 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-600 rounded-full" x-text="c.category_name"></span>
                                        </td>
                                        <td class="py-2.5 px-2 text-right font-semibold" x-text="formatNumber(c.total_donations)"></td>
                                        <td class="py-2.5 px-2 text-right text-primary-600 font-semibold" x-text="formatCurrencyShort(c.total_amount)"></td>
                                        <td class="py-2.5 px-2 text-right" x-text="formatCurrencyShort(c.avg_amount)"></td>
                                        <td class="py-2.5 px-2 text-right" x-text="formatNumber(c.unique_donors)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Program Packages -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Program / Paket Donasi</h3>
                <div class="relative" style="height: 280px">
                    <canvas id="programChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ========== TAB: PAYMENT ========== -->
        <div x-show="activeTab === 'payment'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid lg:grid-cols-3 gap-4">
                <!-- Method Doughnut -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                    <div class="relative" style="height: 250px">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
                <!-- Bank Bar -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Bank / Channel</h3>
                    <div class="relative" style="height: 250px">
                        <canvas id="bankChart"></canvas>
                    </div>
                </div>
                <!-- Confirmation Stats -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Proses Pembayaran</h3>
                    <div class="space-y-3">
                        <template x-for="pb in (paymentData.process_by || [])" :key="pb.processor">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                         :class="pb.processor === 'flip' ? 'bg-blue-100' : pb.processor === 'admin' ? 'bg-amber-100' : 'bg-gray-200'">
                                        <i class="text-sm" :class="pb.processor === 'flip' ? 'bi bi-robot text-blue-600' : pb.processor === 'admin' ? 'bi bi-person-badge text-amber-600' : 'bi bi-hourglass-split text-gray-500'"></i>
                                    </div>
                                    <span class="text-xs font-medium capitalize" x-text="pb.processor"></span>
                                </div>
                                <span class="text-sm font-bold" x-text="formatNumber(pb.count)"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-image text-emerald-600 text-sm"></i>
                                </div>
                                <span class="text-xs font-medium">Bukti Transfer</span>
                            </div>
                            <span class="text-sm font-bold" x-text="formatNumber(paymentData.img_confirmed || 0)"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Nominal Distribution -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-1">Distribusi Nominal Donasi</h3>
                <p class="text-xs text-gray-400 mb-4">Mayoritas donasi di range Rp 100K-200K</p>
                <div class="relative" style="height: 280px">
                    <canvas id="nominalChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ========== TAB: DONORS ========== -->
        <div x-show="activeTab === 'donors'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <!-- Donor Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 bg-primary-100 rounded-lg flex items-center justify-center"><i class="bi bi-person-check text-primary-600 text-xs"></i></div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Donor Unik</span>
                    </div>
                    <span class="text-lg font-bold text-gray-800" x-text="formatNumber(donorData.unique_phones)">-</span>
                    <p class="text-[10px] text-gray-400">by phone</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center"><i class="bi bi-arrow-repeat text-blue-600 text-xs"></i></div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Repeat Donors</span>
                    </div>
                    <span class="text-lg font-bold text-gray-800" x-text="formatNumber(donorData.repeat_donors)">-</span>
                    <p class="text-[10px] text-gray-400">2+ donasi</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center"><i class="bi bi-incognito text-violet-600 text-xs"></i></div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Anonim Rate</span>
                    </div>
                    <span class="text-lg font-bold text-gray-800" x-text="(donorData.anonim_rate || 0) + '%'">-</span>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 bg-rose-100 rounded-lg flex items-center justify-center"><i class="bi bi-chat-heart text-rose-600 text-xs"></i></div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Dengan Doa</span>
                    </div>
                    <span class="text-lg font-bold text-gray-800" x-text="formatNumber(donorData.with_comment)">-</span>
                    <p class="text-[10px] text-gray-400" x-text="(donorData.comment_rate || 0) + '%'"></p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-4 mt-4">
                <!-- Top Donors -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800">Top Donatur</h3>
                        <span class="text-[10px] text-gray-400"><i class="bi bi-shield-lock"></i> nama & nomor tersamarkan</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-400 uppercase border-b border-gray-100">
                                    <th class="py-2 px-1 w-6">#</th>
                                    <th class="py-2 px-2">Nama</th>
                                    <th class="py-2 px-2">WhatsApp</th>
                                    <th class="py-2 px-2 text-right">Donasi</th>
                                    <th class="py-2 px-2 text-right">Total</th>
                                    <th class="py-2 px-2 text-right">Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="d in (donorData.top_donors || [])" :key="d.rank">
                                    <tr class="donor-row border-b border-gray-50">
                                        <td class="py-2.5 px-1">
                                            <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-bold"
                                                  :class="d.rank <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'"
                                                  x-text="d.rank"></span>
                                        </td>
                                        <td class="py-2.5 px-2 font-medium" x-text="d.name"></td>
                                        <td class="py-2.5 px-2 font-mono text-gray-500" x-text="d.whatsapp"></td>
                                        <td class="py-2.5 px-2 text-right" x-text="d.total_donations"></td>
                                        <td class="py-2.5 px-2 text-right font-semibold text-primary-600" x-text="formatCurrencyShort(d.total_amount)"></td>
                                        <td class="py-2.5 px-2 text-right text-gray-400" x-text="timeAgo(d.last_donation)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sapaan Chart -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Demografi (Sapaan)</h3>
                    <div class="relative" style="height: 220px">
                        <canvas id="sapaanChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Follow-up Funnel -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-1">Funnel Follow-up</h3>
                <p class="text-xs text-gray-400 mb-4">Conversion rate dari invoice sampai follow-up akhir</p>
                <div class="space-y-3">
                    <template x-for="(stage, i) in funnelStages" :key="i">
                        <div class="flex items-center gap-3">
                            <div class="w-28 text-xs font-medium text-gray-600 text-right flex-shrink-0" x-text="stage.label"></div>
                            <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                <div class="funnel-bar h-full rounded-full flex items-center px-3"
                                     :class="stage.colorClass"
                                     :style="'width: ' + stage.percentage + '%'">
                                    <span class="text-[11px] font-bold text-white whitespace-nowrap" x-text="formatNumber(donorData.funnel?.['f' + (i+1)] || 0) + ' (' + stage.percentage + '%)'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2">
                    <i class="bi bi-lightbulb text-amber-600 mt-0.5"></i>
                    <p class="text-xs text-amber-800">Hanya <strong>11.7%</strong> yang di-follow-up. Potensi besar untuk improve retention dan repeat donation.</p>
                </div>
            </div>
        </div>

        <!-- ========== TAB: TRAFFIC ========== -->
        <div x-show="activeTab === 'traffic'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid lg:grid-cols-3 gap-4">
                <!-- UTM Source Chart -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Sumber Traffic</h3>
                    <div class="relative" style="height: 250px">
                        <canvas id="utmChart"></canvas>
                    </div>
                </div>
                <!-- UTM Table -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Performa per Sumber</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-400 uppercase border-b border-gray-100">
                                    <th class="py-2 px-2">Sumber</th>
                                    <th class="py-2 px-2 text-right">Donasi</th>
                                    <th class="py-2 px-2 text-right">Total</th>
                                    <th class="py-2 px-2 text-right">Avg</th>
                                    <th class="py-2 px-2 text-right">Donor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(s, i) in (trafficData.utm_sources || [])" :key="i">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                        <td class="py-2.5 px-2 font-medium">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full" :style="'background:' + chartColors[i % chartColors.length]"></div>
                                                <span x-text="s.source"></span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2 text-right font-semibold" x-text="formatNumber(s.count)"></td>
                                        <td class="py-2.5 px-2 text-right text-primary-600 font-semibold" x-text="formatCurrencyShort(s.total)"></td>
                                        <td class="py-2.5 px-2 text-right" x-text="formatCurrencyShort(s.avg_amount)"></td>
                                        <td class="py-2.5 px-2 text-right" x-text="formatNumber(s.unique_donors)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Device Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                <template x-for="d in (trafficData.devices || [])" :key="d.device">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <i class="text-2xl mb-2" :class="d.device === 'mobile' ? 'bi bi-phone text-blue-500' : 'bi bi-laptop text-gray-500'"></i>
                        <p class="text-lg font-bold text-gray-800" x-text="formatNumber(d.count)"></p>
                        <p class="text-[10px] text-gray-400 uppercase font-semibold capitalize" x-text="d.device"></p>
                    </div>
                </template>
                <template x-for="o in (trafficData.os || []).slice(0, 2)" :key="o.os_name">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <i class="text-2xl mb-2" :class="o.os_name === 'Android' ? 'bi bi-android2 text-green-500' : o.os_name === 'iPhone' ? 'bi bi-apple text-gray-700' : 'bi bi-laptop text-gray-500'"></i>
                        <p class="text-lg font-bold text-gray-800" x-text="formatNumber(o.count)"></p>
                        <p class="text-[10px] text-gray-400 uppercase font-semibold" x-text="o.os_name"></p>
                    </div>
                </template>
            </div>

            <!-- Source Monthly Trend -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Trend per Sumber (Bulanan)</h3>
                <div class="relative" style="height: 280px">
                    <canvas id="sourceTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ========== TAB: TIME PATTERNS ========== -->
        <div x-show="activeTab === 'time'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <!-- Heatmap -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-1">Heatmap Donasi: Jam &times; Hari</h3>
                <p class="text-xs text-gray-400 mb-4">Hover untuk melihat detail</p>
                <div class="overflow-x-auto">
                    <div class="min-w-[700px]">
                        <!-- Hour labels -->
                        <div class="flex">
                            <div class="w-16 flex-shrink-0"></div>
                            <div class="flex-1 grid gap-0.5 text-[9px] text-gray-400 text-center mb-1" style="grid-template-columns: repeat(24, minmax(0, 1fr))">
                                <template x-for="h in 24" :key="h">
                                    <div x-text="(h-1) + ':00'"></div>
                                </template>
                            </div>
                        </div>
                        <!-- Rows -->
                        <template x-for="dow in [2,3,4,5,6,7,1]" :key="dow">
                            <div class="flex items-center mb-0.5">
                                <div class="w-16 flex-shrink-0 text-[11px] font-medium text-gray-600 text-right pr-2" x-text="timeData.day_names?.[dow] || ''"></div>
                                <div class="flex-1 grid gap-0.5" style="grid-template-columns: repeat(24, minmax(0, 1fr))">
                                    <template x-for="h in 24" :key="h">
                                        <div class="heatmap-cell aspect-square relative group cursor-pointer"
                                             :style="'background-color: ' + getHeatColor(timeData.heatmap?.[dow]?.[h-1]?.count || 0)"
                                             :title="(timeData.day_names?.[dow] || '') + ' ' + (h-1) + ':00 — ' + (timeData.heatmap?.[dow]?.[h-1]?.count || 0) + ' donasi'">
                                            <!-- Tooltip -->
                                            <div class="hidden group-hover:block absolute top-full left-1/2 -translate-x-1/2 mt-1 z-[50] bg-gray-800 text-white text-[9px] rounded-lg px-2 py-1 whitespace-nowrap shadow-lg pointer-events-none">
                                                <span x-text="(timeData.heatmap?.[dow]?.[h-1]?.count || 0) + ' donasi'"></span><br>
                                                <span x-text="formatCurrencyShort(timeData.heatmap?.[dow]?.[h-1]?.total || 0)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <!-- Legend -->
                        <div class="flex items-center justify-end gap-1 mt-3 text-[9px] text-gray-400">
                            <span>Sedikit</span>
                            <div class="flex gap-0.5">
                                <div class="w-4 h-4 rounded" style="background: #f0fdf4"></div>
                                <div class="w-4 h-4 rounded" style="background: #bbf7d0"></div>
                                <div class="w-4 h-4 rounded" style="background: #6ee7b7"></div>
                                <div class="w-4 h-4 rounded" style="background: #10b981"></div>
                                <div class="w-4 h-4 rounded" style="background: #047857"></div>
                            </div>
                            <span>Banyak</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hourly & Daily Charts -->
            <div class="grid lg:grid-cols-2 gap-4 mt-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Distribusi per Jam</h3>
                    <div class="relative" style="height: 260px">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Distribusi per Hari</h3>
                    <div class="relative" style="height: 260px">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Time Insight Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <i class="bi bi-sunrise text-amber-600 text-xl mb-2"></i>
                    <h4 class="text-xs font-bold text-amber-800">Peak Subuh</h4>
                    <p class="text-[10px] text-amber-700 mt-1">04:00-06:00 — Donatur aktif setelah sholat subuh</p>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <i class="bi bi-moon-stars text-indigo-600 text-xl mb-2"></i>
                    <h4 class="text-xs font-bold text-indigo-800">Peak Malam</h4>
                    <p class="text-[10px] text-indigo-700 mt-1">19:00-21:00 — Setelah maghrib & isya</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <i class="bi bi-calendar-event text-emerald-600 text-xl mb-2"></i>
                    <h4 class="text-xs font-bold text-emerald-800">Hari Jumat</h4>
                    <p class="text-[10px] text-emerald-700 mt-1">Hari sedekah paling populer</p>
                </div>
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                    <i class="bi bi-calendar-heart text-rose-600 text-xl mb-2"></i>
                    <h4 class="text-xs font-bold text-rose-800">Efek Ramadhan</h4>
                    <p class="text-[10px] text-rose-700 mt-1">Peak bulanan tertinggi di bulan Ramadhan</p>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
<script>
function donasiWebApp() {
    return {
        // State
        loading: true,
        dataLoaded: false,
        sidebarOpen: false,
        activeTab: 'trend',
        period: 'all',
        campaignFilter: 'all',
        statusFilter: '1',
        trendGroupBy: 'monthly',

        // Data stores
        overview: {},
        trendData: {},
        campaignData: {},
        paymentData: {},
        donorData: {},
        trafficData: {},
        timeData: {},
        programData: {},
        campaignList: [],

        // Tab loading states
        tabLoading: { trend: false, campaign: false, payment: false, donors: false, traffic: false, time: false },
        loadedTabs: {},

        // Chart instances
        charts: {},
        chartColors: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899', '#14B8A6', '#F97316', '#6366F1'],

        // Period options
        periods: [
            { label: 'Hari Ini', value: 'today' },
            { label: '7 Hari', value: '7d' },
            { label: '30 Hari', value: '30d' },
            { label: '90 Hari', value: '90d' },
            { label: 'Semua', value: 'all' },
        ],

        // Tab definitions
        tabs: [
            { id: 'trend', icon: 'bi-graph-up-arrow', label: 'Trend' },
            { id: 'campaign', icon: 'bi-bullseye', label: 'Campaign' },
            { id: 'payment', icon: 'bi-credit-card-2-front-fill', label: 'Pembayaran' },
            { id: 'donors', icon: 'bi-people-fill', label: 'Donatur' },
            { id: 'traffic', icon: 'bi-broadcast-pin', label: 'Traffic' },
            { id: 'time', icon: 'bi-alarm-fill', label: 'Waktu' },
        ],

        // Funnel stages
        funnelStages: [
            { label: 'Invoice (f1)', percentage: 100, colorClass: 'bg-primary-500' },
            { label: 'Follow-up 1 (f2)', percentage: 11.7, colorClass: 'bg-blue-500' },
            { label: 'Follow-up 2 (f3)', percentage: 5.8, colorClass: 'bg-violet-500' },
            { label: 'Follow-up 3 (f4)', percentage: 5.8, colorClass: 'bg-amber-500' },
            { label: 'Final (f5)', percentage: 7.0, colorClass: 'bg-rose-500' },
        ],

        // ==================== INIT ====================
        async init() {
            // Cleanup charts on Livewire navigation
            document.addEventListener('livewire:navigating', () => this.destroyAllCharts());
            
            await this.fetchOverview();
            await this.fetchTrend();
            this.loadedTabs['trend'] = true;
            this.loading = false;
            this.dataLoaded = true;

            // Get campaign list for filter
            this.fetchCampaignList();
        },

        // ==================== TAB SWITCHING ====================
        async switchTab(tabId) {
            this.activeTab = tabId;
            if (!this.loadedTabs[tabId]) {
                await this.loadTabData(tabId);
                this.loadedTabs[tabId] = true;
            }
        },

        async loadTabData(tabId) {
            this.tabLoading[tabId] = true;
            try {
                switch (tabId) {
                    case 'trend': await this.fetchTrend(); break;
                    case 'campaign': await this.fetchCampaign(); await this.fetchPrograms(); break;
                    case 'payment': await this.fetchPayment(); break;
                    case 'donors': await this.fetchDonors(); break;
                    case 'traffic': await this.fetchTraffic(); break;
                    case 'time': await this.fetchTimePatterns(); break;
                }
            } finally {
                this.tabLoading[tabId] = false;
            }
        },

        // ==================== FILTERS ====================
        setPeriod(p) {
            this.period = p;
            this.refreshAll();
        },

        async refreshAll() {
            this.loading = true;
            this.loadedTabs = {};
            await this.fetchOverview();
            await this.loadTabData(this.activeTab);
            this.loadedTabs[this.activeTab] = true;
            this.loading = false;
        },

        buildParams() {
            let p = `period=${this.period}&status=${this.statusFilter}&campaign=${this.campaignFilter}`;
            return p;
        },

        // ==================== API FETCHERS ====================
        async fetchOverview() {
            try {
                const res = await fetch(`/api/donasi-web/overview-stats?${this.buildParams()}`);
                this.overview = await res.json();
            } catch (e) { console.error('overview error', e); }
        },

        async fetchTrend() {
            this.tabLoading.trend = true;
            try {
                const res = await fetch(`/api/donasi-web/trend-data?${this.buildParams()}&group_by=${this.trendGroupBy}`);
                this.trendData = await res.json();
                this.$nextTick(() => this.renderTrendChart());
            } catch (e) { console.error('trend error', e); }
            this.tabLoading.trend = false;
        },

        async fetchCampaign() {
            try {
                const res = await fetch(`/api/donasi-web/campaign-breakdown?${this.buildParams()}`);
                this.campaignData = await res.json();
                this.$nextTick(() => this.renderCampaignChart());
            } catch (e) { console.error('campaign error', e); }
        },

        async fetchPrograms() {
            try {
                const res = await fetch(`/api/donasi-web/program-packages?${this.buildParams()}`);
                this.programData = await res.json();
                this.$nextTick(() => this.renderProgramChart());
            } catch (e) { console.error('program error', e); }
        },

        async fetchPayment() {
            try {
                const res = await fetch(`/api/donasi-web/payment-analytics?${this.buildParams()}`);
                this.paymentData = await res.json();
                this.$nextTick(() => {
                    this.renderPaymentMethodChart();
                    this.renderBankChart();
                    this.renderNominalChart();
                });
            } catch (e) { console.error('payment error', e); }
        },

        async fetchDonors() {
            try {
                const res = await fetch(`/api/donasi-web/donor-insights?${this.buildParams()}`);
                this.donorData = await res.json();
                this.$nextTick(() => this.renderSapaanChart());
                // Update funnel percentages dynamically
                if (this.donorData.funnel && this.donorData.total_count) {
                    const total = this.donorData.total_count;
                    this.funnelStages = [
                        { label: 'Invoice (f1)', percentage: 100, colorClass: 'bg-primary-500' },
                        { label: 'Follow-up 1 (f2)', percentage: Math.round((this.donorData.funnel.f2 / total) * 1000) / 10, colorClass: 'bg-blue-500' },
                        { label: 'Follow-up 2 (f3)', percentage: Math.round((this.donorData.funnel.f3 / total) * 1000) / 10, colorClass: 'bg-violet-500' },
                        { label: 'Follow-up 3 (f4)', percentage: Math.round((this.donorData.funnel.f4 / total) * 1000) / 10, colorClass: 'bg-amber-500' },
                        { label: 'Final (f5)', percentage: Math.round((this.donorData.funnel.f5 / total) * 1000) / 10, colorClass: 'bg-rose-500' },
                    ];
                }
            } catch (e) { console.error('donors error', e); }
        },

        async fetchTraffic() {
            try {
                const res = await fetch(`/api/donasi-web/traffic-utm?${this.buildParams()}`);
                this.trafficData = await res.json();
                this.$nextTick(() => {
                    this.renderUtmChart();
                    this.renderSourceTrendChart();
                });
            } catch (e) { console.error('traffic error', e); }
        },

        async fetchTimePatterns() {
            try {
                const res = await fetch(`/api/donasi-web/time-patterns?${this.buildParams()}`);
                this.timeData = await res.json();
                this.$nextTick(() => {
                    this.renderHourlyChart();
                    this.renderDailyChart();
                });
            } catch (e) { console.error('time error', e); }
        },

        async fetchCampaignList() {
            try {
                const res = await fetch(`/api/donasi-web/campaign-breakdown?period=all&status=1&campaign=all`);
                const data = await res.json();
                this.campaignList = (data.campaigns || []).map(c => ({ id: c.campaign_id, title: c.campaign_title }));
            } catch (e) {}
        },

        // ==================== CHART RENDERERS ====================
        destroyChart(key) {
            if (this.charts[key]) { this.charts[key].destroy(); delete this.charts[key]; }
        },
        destroyAllCharts() {
            Object.keys(this.charts).forEach(k => this.destroyChart(k));
        },

        renderTrendChart() {
            this.destroyChart('trend');
            const ctx = document.getElementById('trendChart');
            if (!ctx || !this.trendData.labels) return;
            this.charts.trend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.trendData.labels,
                    datasets: [
                        {
                            label: 'Jumlah Donasi',
                            data: this.trendData.counts,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16,185,129,0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Total Nominal',
                            data: this.trendData.totals,
                            borderColor: '#3B82F6',
                            borderDash: [5, 5],
                            tension: 0.4,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 15, font: { size: 11 } } } },
                    scales: {
                        y: { position: 'left', title: { display: true, text: 'Jumlah', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y1: { position: 'right', title: { display: true, text: 'Nominal (Rp)', font: { size: 10 } }, grid: { display: false },
                            ticks: { callback: v => this.formatCurrencyShort(v) } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } }
                    }
                }
            });
        },

        renderCampaignChart() {
            this.destroyChart('campaign');
            const ctx = document.getElementById('campaignChart');
            if (!ctx || !this.campaignData.campaigns) return;
            const camps = this.campaignData.campaigns.slice(0, 7);
            this.charts.campaign = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: camps.map(c => (c.campaign_title || c.campaign_id).substring(0, 20)),
                    datasets: [{ data: camps.map(c => c.total_amount), backgroundColor: this.chartColors.slice(0, camps.length), borderWidth: 0 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 10 } } } },
                    cutout: '60%'
                }
            });
        },

        renderProgramChart() {
            this.destroyChart('program');
            const ctx = document.getElementById('programChart');
            if (!ctx || !this.programData.packages) return;
            const pkgs = this.programData.packages.slice(0, 10);
            this.charts.program = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: pkgs.map(p => p.name),
                    datasets: [{ label: 'Jumlah Donasi', data: pkgs.map(p => p.count), backgroundColor: '#10B981', borderRadius: 6, maxBarThickness: 30 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { color: 'rgba(0,0,0,0.05)' } }, y: { ticks: { font: { size: 10 } } } }
                }
            });
        },

        renderPaymentMethodChart() {
            this.destroyChart('paymentMethod');
            const ctx = document.getElementById('paymentMethodChart');
            if (!ctx || !this.paymentData.methods) return;
            this.charts.paymentMethod = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.paymentData.methods.map(m => m.payment_method),
                    datasets: [{ data: this.paymentData.methods.map(m => m.count), backgroundColor: ['#10B981', '#3B82F6', '#F59E0B'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 10 } } } } }
            });
        },

        renderBankChart() {
            this.destroyChart('bank');
            const ctx = document.getElementById('bankChart');
            if (!ctx || !this.paymentData.banks) return;
            const banks = this.paymentData.banks.slice(0, 8);
            this.charts.bank = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: banks.map(b => b.payment_code),
                    datasets: [{ label: 'Transaksi', data: banks.map(b => b.count), backgroundColor: this.chartColors.slice(0, banks.length), borderRadius: 6, maxBarThickness: 28 }]
                },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(0,0,0,0.05)' } }, y: { ticks: { font: { size: 10 } } } } }
            });
        },

        renderNominalChart() {
            this.destroyChart('nominal');
            const ctx = document.getElementById('nominalChart');
            if (!ctx || !this.paymentData.nominal_distribution) return;
            const dist = this.paymentData.nominal_distribution;
            this.charts.nominal = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dist.map(d => d.label),
                    datasets: [{ label: 'Jumlah Donasi', data: dist.map(d => d.count), backgroundColor: dist.map((d, i) => i === 3 ? '#10B981' : '#D1FAE5'), borderColor: dist.map((d, i) => i === 3 ? '#059669' : '#A7F3D0'), borderWidth: 1, borderRadius: 6, maxBarThickness: 50 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { title: { display: true, text: 'Jumlah', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { ticks: { font: { size: 10 } } } } }
            });
        },

        renderSapaanChart() {
            this.destroyChart('sapaan');
            const ctx = document.getElementById('sapaanChart');
            if (!ctx || !this.donorData.sapaan) return;
            this.charts.sapaan = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.donorData.sapaan.map(s => s.sapaan),
                    datasets: [{ data: this.donorData.sapaan.map(s => s.count), backgroundColor: ['#3B82F6', '#EC4899', '#F59E0B', '#6B7280'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 11 } } } } }
            });
        },

        renderUtmChart() {
            this.destroyChart('utm');
            const ctx = document.getElementById('utmChart');
            if (!ctx || !this.trafficData.utm_sources) return;
            const srcs = this.trafficData.utm_sources.slice(0, 5);
            this.charts.utm = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: srcs.map(s => s.source),
                    datasets: [{ data: srcs.map(s => s.count), backgroundColor: this.chartColors.slice(0, srcs.length), borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 11 } } } } }
            });
        },

        renderSourceTrendChart() {
            this.destroyChart('sourceTrend');
            const ctx = document.getElementById('sourceTrendChart');
            if (!ctx || !this.trafficData.source_trend) return;
            const srcData = this.trafficData.source_trend;
            const allMonths = [...new Set(Object.values(srcData).flat().map(d => d.month))].sort();
            const datasets = [];
            const srcColors = { ig: '#E1306C', fb: '#1877F2' };
            const srcLabels = { ig: 'Instagram', fb: 'Facebook' };
            for (const [src, rows] of Object.entries(srcData)) {
                const rowMap = Object.fromEntries(rows.map(r => [r.month, r.count]));
                datasets.push({
                    label: srcLabels[src] || src,
                    data: allMonths.map(m => rowMap[m] || 0),
                    backgroundColor: srcColors[src] || '#6B7280',
                    borderRadius: 4,
                    maxBarThickness: 20,
                });
            }
            this.charts.sourceTrend = new Chart(ctx, {
                type: 'bar',
                data: { labels: allMonths, datasets },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } } }, scales: { x: { stacked: true, ticks: { font: { size: 10 } } }, y: { stacked: true, grid: { color: 'rgba(0,0,0,0.05)' } } } }
            });
        },

        renderHourlyChart() {
            this.destroyChart('hourly');
            const ctx = document.getElementById('hourlyChart');
            if (!ctx || !this.timeData.hourly) return;
            const hourly = this.timeData.hourly;
            const peakHour = this.timeData.peak_hour;
            this.charts.hourly = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hourly.map(h => h.hour + ':00'),
                    datasets: [{
                        label: 'Donasi',
                        data: hourly.map(h => h.count),
                        backgroundColor: hourly.map(h => (h.hour >= 4 && h.hour <= 6) || (h.hour >= 19 && h.hour <= 21) ? '#10B981' : '#D1FAE5'),
                        borderRadius: 4, maxBarThickness: 16
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: 'rgba(0,0,0,0.05)' } }, x: { ticks: { font: { size: 9 }, maxRotation: 90 } } } }
            });
        },

        renderDailyChart() {
            this.destroyChart('daily');
            const ctx = document.getElementById('dailyChart');
            if (!ctx || !this.timeData.daily) return;
            const daily = this.timeData.daily;
            this.charts.daily = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: daily.map(d => d.day_name),
                    datasets: [{
                        label: 'Donasi',
                        data: daily.map(d => d.count),
                        backgroundColor: daily.map(d => d.day_name === 'Jumat' ? '#10B981' : '#D1FAE5'),
                        borderRadius: 6, maxBarThickness: 40
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: 'rgba(0,0,0,0.05)' } } } }
            });
        },

        // ==================== HEATMAP ====================
        getHeatColor(count) {
            if (count === 0) return '#f9fafb';
            const max = this.getHeatmapMax();
            const ratio = Math.min(count / max, 1);
            if (ratio < 0.15) return '#f0fdf4';
            if (ratio < 0.3) return '#bbf7d0';
            if (ratio < 0.5) return '#6ee7b7';
            if (ratio < 0.75) return '#10b981';
            return '#047857';
        },
        getHeatmapMax() {
            if (!this.timeData.heatmap) return 1;
            let max = 1;
            for (const dow of Object.values(this.timeData.heatmap)) {
                for (const h of Object.values(dow)) {
                    if (h.count > max) max = h.count;
                }
            }
            return max;
        },

        // ==================== FORMATTERS ====================
        formatCurrency(val) {
            if (!val && val !== 0) return '-';
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        },
        formatCurrencyShort(val) {
            if (!val && val !== 0) return '-';
            val = Number(val);
            if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + 'M';
            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'K';
            return 'Rp ' + val;
        },
        formatNumber(val) {
            if (!val && val !== 0) return '-';
            return Number(val).toLocaleString('id-ID');
        },
        timeAgo(dateStr) {
            if (!dateStr) return '-';
            const diff = (new Date() - new Date(dateStr)) / 1000;
            if (diff < 3600) return Math.floor(diff / 60) + 'm lalu';
            if (diff < 86400) return Math.floor(diff / 3600) + 'j lalu';
            if (diff < 2592000) return Math.floor(diff / 86400) + 'h lalu';
            return Math.floor(diff / 2592000) + 'bln lalu';
        },
    };
}
</script>
@endpush

</x-layouts.app>
