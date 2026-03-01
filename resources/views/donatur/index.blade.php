<x-layouts.app active="donatur" title="Donatur CRM" xData="donaturCrmApp()">

@push('styles')
<style>
    /* Segment Card */
    .segment-card { transition: all 0.2s ease; cursor: pointer; }
    .segment-card:hover { transform: scale(1.02); }
    .segment-card.active { ring: 2px; ring-color: #10B981; }
    
    /* Slide Panel */
    .slide-panel {
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
    }
    .slide-panel.open {
        transform: translateX(0);
    }
    
    /* Badge Colors */
    .badge-vip { background: #FEF3C7; color: #92400E; }
    .badge-loyal { background: #D1FAE5; color: #065F46; }
    .badge-new { background: #DBEAFE; color: #1E40AF; }
    .badge-one-time { background: #F3F4F6; color: #374151; }
    .badge-at-risk { background: #FFEDD5; color: #9A3412; }
    .badge-churned { background: #FEE2E2; color: #991B1B; }
    .badge-never { background: #F1F5F9; color: #475569; }
    
    /* Score Badge */
    .score-hot { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; }
    .score-warm { background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%); color: white; }
    .score-cold { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); color: white; }
</style>
@endpush
    
@push('before-sidebar')
    <!-- Initial Loading Modal - Friendly Version -->
    <div x-show="initialLoading.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-md z-[9999] flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 max-w-md mx-4 text-center transform">
            <!-- Animated Icon -->
            <div class="relative w-24 h-24 mx-auto mb-6">
                <div class="absolute inset-0 bg-primary-100 rounded-full animate-ping opacity-30"></div>
                <div class="relative w-24 h-24 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center shadow-lg shadow-primary-500/40">
                    <i class="bi bi-people-fill text-white text-4xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Donatur CRM</h2>
            <p class="text-gray-500 mb-6">Menyiapkan data untuk Anda...</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500 ease-out"
                     :style="'width: ' + initialLoading.progress + '%'"></div>
            </div>
            
            <!-- Loading Status -->
            <p class="text-sm text-primary-600 font-medium mb-6" x-text="initialLoading.status">Memuat statistik...</p>
            
            <!-- Fun Tips / Quotes - rotating -->
            <div class="bg-gradient-to-r from-primary-50 to-green-50 rounded-2xl p-4 border border-primary-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="text-primary-600 text-lg" :class="'bi ' + initialLoading.tips[initialLoading.currentTip].icon"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-semibold text-primary-700 mb-1">Tips CRM</p>
                        <p class="text-sm text-gray-600 leading-relaxed" x-text="initialLoading.tips[initialLoading.currentTip].text">
                            Loading tips...
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Dots animation -->
            <div class="flex justify-center gap-1.5 mt-6">
                <template x-for="i in 3" :key="i">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce"
                         :style="'animation-delay: ' + (i * 0.15) + 's'"></div>
                </template>
            </div>
        </div>
    </div>
@endpush
    
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100">
            <div class="flex items-center justify-between px-4 md:px-6 py-3">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl">
                    <i class="bi bi-list text-xl"></i>
                </button>
                
                <!-- Title -->
                <div class="flex items-center gap-3">
                    <div class="hidden lg:flex w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl items-center justify-center">
                        <i class="bi bi-people-fill text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-lg md:text-xl font-bold text-gray-800">Donatur CRM</h1>
                        <p class="text-xs text-gray-500 hidden md:block">Kelola dan analisis data donatur</p>
                    </div>
                </div>
                
                <!-- Header Actions -->
                <div class="flex items-center gap-2">
                    <!-- Alert Bell -->
                    <div class="relative" x-data="{ alertOpen: false }">
                        <button @click="alertOpen = !alertOpen; if(alertOpen && smartAlerts.alerts.length === 0) loadAlerts()"
                                class="relative p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition">
                            <i class="bi bi-bell text-xl"></i>
                            <!-- Badge -->
                            <span x-show="smartAlerts.total > 0" 
                                  x-transition
                                  class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                  x-text="smartAlerts.total > 99 ? '99+' : smartAlerts.total"></span>
                        </button>
                        
                        <!-- Dropdown -->
                        <div x-show="alertOpen" 
                             x-transition
                             @click.away="alertOpen = false"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">
                            <!-- Header -->
                            <div class="px-4 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white flex items-center justify-between">
                                <span class="font-semibold">Smart Alerts</span>
                                <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full" x-text="smartAlerts.total + ' total'"></span>
                            </div>
                            
                            <!-- Loading -->
                            <div x-show="smartAlerts.loading" class="p-6 text-center">
                                <div class="spinner mx-auto"></div>
                            </div>
                            
                            <!-- Alert List -->
                            <div x-show="!smartAlerts.loading" class="divide-y divide-gray-100">
                                <template x-for="alert in smartAlerts.alerts" :key="alert.id">
                                    <button @click="handleAlertAction(alert); alertOpen = false"
                                            class="w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-50 transition text-left">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                                             :class="{
                                                 'bg-red-100 text-red-600': alert.color === 'red',
                                                 'bg-orange-100 text-orange-600': alert.color === 'orange',
                                                 'bg-yellow-100 text-yellow-600': alert.color === 'yellow'
                                             }">
                                            <i :class="alert.icon"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-800 text-sm" x-text="alert.label"></p>
                                            <p class="text-xs text-gray-500" x-text="alert.description"></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-bold rounded-full"
                                              :class="{
                                                  'bg-red-100 text-red-700': alert.color === 'red',
                                                  'bg-orange-100 text-orange-700': alert.color === 'orange',
                                                  'bg-yellow-100 text-yellow-700': alert.color === 'yellow'
                                              }"
                                              x-text="alert.count"></span>
                                    </button>
                                </template>
                            </div>
                            
                            <!-- Empty State -->
                            <div x-show="!smartAlerts.loading && smartAlerts.alerts.length === 0" class="p-6 text-center">
                                <i class="bi bi-check-circle text-3xl text-green-500 mb-2"></i>
                                <p class="text-gray-500 text-sm">Tidak ada alert</p>
                            </div>
                            
                            <!-- Footer -->
                            <div class="px-4 py-2 bg-gray-50 border-t border-gray-100">
                                <button @click="loadAlerts()" class="text-xs text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <i class="bi bi-arrow-clockwise" :class="smartAlerts.loading && 'animate-spin'"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Donatur Button -->
                    <button @click="openCreateModal()" 
                            class="flex items-center gap-2 px-3 md:px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition shadow-lg shadow-primary-500/30">
                        <i class="bi bi-plus-lg"></i>
                        <span class="hidden md:inline">Tambah Donatur</span>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <div class="p-4 md:p-6 space-y-6">
            
            <!-- Stats Cards -->
            <section class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-6 gap-2 md:gap-4">
                <!-- Total Donatur -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-primary-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-people-fill text-primary-600 text-sm md:text-base"></i>
                        </div>
                        <span class="text-[10px] md:text-xs text-gray-400 hidden sm:block">Total</span>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.total_donatur)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">Donatur</p>
                </div>
                
                <!-- Donatur Baru -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-person-plus-fill text-blue-600 text-sm md:text-base"></i>
                        </div>
                        <span class="text-[10px] md:text-xs px-1.5 md:px-2 py-0.5 rounded-full hidden sm:block" 
                              :class="stats.growth_rate >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                              x-text="(stats.growth_rate >= 0 ? '+' : '') + stats.growth_rate + '%'">-</span>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_baru)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">Baru</p>
                </div>
                
                <!-- Donatur Aktif -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-lightning-charge-fill text-green-600 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.donatur_aktif)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">Aktif</p>
                </div>
                
                <!-- VIP -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-yellow-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-star-fill text-yellow-600 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.vip)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">VIP</p>
                </div>
                
                <!-- At Risk -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-exclamation-triangle-fill text-orange-600 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.at_risk)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">At Risk</p>
                </div>
                
                <!-- Churned -->
                <div class="stat-card bg-white rounded-xl md:rounded-2xl p-2.5 md:p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 md:mb-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-red-100 rounded-lg md:rounded-xl flex items-center justify-center">
                            <i class="bi bi-x-circle-fill text-red-600 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <p class="text-lg md:text-2xl font-bold text-gray-800" x-text="formatNumber(stats.churned)">-</p>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-0.5 md:mt-1">Churned</p>
                </div>
            </section>
            
            <!-- Segments -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="bi bi-collection text-primary-500 mr-2"></i>
                    Smart Segments
                </h2>
                <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 md:grid md:grid-cols-4 lg:grid-cols-7 md:gap-3 -mx-4 px-4 md:mx-0 md:px-0">
                    <template x-for="(seg, key) in segments" :key="key">
                        <button @click="filterBySegment(key)"
                                class="segment-card flex-shrink-0 w-28 md:w-auto p-2.5 md:p-3 rounded-xl border-2 transition text-left"
                                :class="filters.segment === key ? 'border-primary-500 bg-primary-50' : 'border-gray-100 hover:border-gray-200'">
                            <div class="flex items-center gap-1.5 md:gap-2 mb-1.5 md:mb-2">
                                <span class="text-base md:text-lg" x-html="getSegmentIcon(key)"></span>
                                <span class="text-[10px] md:text-xs font-medium text-gray-600 truncate" x-text="seg.name"></span>
                            </div>
                            <p class="text-lg md:text-xl font-bold text-gray-800" x-text="formatNumber(seg.count)">0</p>
                        </button>
                    </template>
                </div>
            </section>
            
            <!-- Follow-up Center -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="bi bi-telephone-forward text-primary-500 mr-2"></i>
                        Follow-up Center
                    </h2>
                    <button @click="loadFollowUpTasks()" 
                            class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                        <i class="bi bi-arrow-clockwise" :class="followUp.loading && 'animate-spin'"></i>
                        Refresh
                    </button>
                </div>
                
                <!-- Priority Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- High Priority - VIP At Risk -->
                    <div @click="selectFollowUpPriority('high')"
                         class="cursor-pointer p-4 rounded-xl border-2 transition"
                         :class="followUp.selectedPriority === 'high' ? 'border-red-500 bg-red-50' : 'border-gray-100 hover:border-red-200 hover:bg-red-50/50'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-red-600">🔥 VIP At Risk</span>
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full" 
                                  x-text="followUp.tasks.high_priority?.count || 0"></span>
                        </div>
                        <p class="text-xs text-gray-500">VIP belum donasi >30 hari</p>
                    </div>
                    
                    <!-- Medium Priority - At Risk -->
                    <div @click="selectFollowUpPriority('medium')"
                         class="cursor-pointer p-4 rounded-xl border-2 transition"
                         :class="followUp.selectedPriority === 'medium' ? 'border-orange-500 bg-orange-50' : 'border-gray-100 hover:border-orange-200 hover:bg-orange-50/50'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-orange-600">⚠️ At Risk</span>
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full" 
                                  x-text="followUp.tasks.medium_priority?.count || 0"></span>
                        </div>
                        <p class="text-xs text-gray-500">Perlu follow-up segera</p>
                    </div>
                    
                    <!-- Low Priority - New Welcome -->
                    <div @click="selectFollowUpPriority('low')"
                         class="cursor-pointer p-4 rounded-xl border-2 transition"
                         :class="followUp.selectedPriority === 'low' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-100 hover:border-yellow-200 hover:bg-yellow-50/50'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-yellow-600">👋 New Welcome</span>
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full" 
                                  x-text="followUp.tasks.low_priority?.count || 0"></span>
                        </div>
                        <p class="text-xs text-gray-500">Donatur baru perlu di-welcome</p>
                    </div>
                </div>
                
                <!-- Follow-up Queue -->
                <div x-show="followUp.selectedPriority" 
                     x-transition
                     class="border border-gray-100 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700" 
                              x-text="getFollowUpTitle()"></span>
                        <button @click="followUp.selectedPriority = null" class="text-gray-400 hover:text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Loading -->
                    <div x-show="followUp.loading" class="p-8 text-center">
                        <div class="spinner mx-auto"></div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!followUp.loading && getFollowUpList().length === 0" class="p-8 text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="bi bi-check-circle text-xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Tidak ada task untuk kategori ini</p>
                    </div>
                    
                    <!-- Queue List -->
                    <div x-show="!followUp.loading && getFollowUpList().length > 0" class="max-h-64 overflow-y-auto">
                        <template x-for="item in getFollowUpList()" :key="item.id">
                            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition">
                                <!-- Avatar -->
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-medium text-sm flex-shrink-0"
                                     x-text="item.nama?.charAt(0)?.toUpperCase() || '?'"></div>
                                
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 truncate" x-text="item.nama"></p>
                                    <p class="text-xs text-gray-500" x-text="item.reason"></p>
                                </div>
                                
                                <!-- Value -->
                                <div class="text-right hidden md:block">
                                    <p class="text-sm font-semibold text-primary-600" x-text="item.lifetime_value_formatted"></p>
                                    <p class="text-xs text-gray-400" x-text="(item.frequency || 0) + 'x donasi'"></p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-1">
                                    <a :href="item.wa_link" target="_blank"
                                       class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                                       title="WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <button @click="openDetailPanel(item.id)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button @click="markFollowUpDone(item.id)"
                                            class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                            title="Mark Done">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Collapsed state info -->
                <div x-show="!followUp.selectedPriority" class="text-center py-2">
                    <p class="text-sm text-gray-400">
                        <span x-text="followUp.tasks.total_tasks || 0"></span> task menunggu follow-up
                    </p>
                </div>
            </section>
            
            <!-- Filters & Table -->
            <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-3 md:p-4 border-b border-gray-100 space-y-3 md:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2 md:gap-3">
                        <!-- Search -->
                        <div class="flex-1 min-w-0 sm:min-w-[200px]">
                            <div class="relative">
                                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" 
                                       x-model.debounce.500ms="filters.search"
                                       @input="loadTable()"
                                       placeholder="Cari nama, no HP, DID..."
                                       class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition text-sm">
                            </div>
                        </div>
                        
                        <!-- Filter row for mobile -->
                        <div class="flex gap-2 w-full sm:w-auto">
                            <!-- Kategori Filter -->
                            <select x-model="filters.kat_donatur" 
                                    @change="loadTable()"
                                    class="filter-select flex-1 sm:flex-none px-3 md:px-4 py-2 md:py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition text-sm bg-white">
                                <option value="">Kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </select>
                            
                            <!-- CS Filter -->
                            <select x-model="filters.nama_cs" 
                                    @change="loadTable()"
                                    class="filter-select flex-1 sm:flex-none px-3 md:px-4 py-2 md:py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition text-sm bg-white">
                                <option value="">CS</option>
                                @foreach($csList as $cs)
                                    <option value="{{ $cs }}">{{ $cs }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Clear Segment -->
                        <button x-show="filters.segment" 
                                @click="clearSegmentFilter()"
                                class="px-3 py-2 md:py-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition text-sm flex items-center justify-center gap-2">
                            <i class="bi bi-x-lg"></i>
                            <span class="truncate" x-text="'Segment: ' + (segments[filters.segment]?.name || filters.segment)"></span>
                        </button>
                    </div>
                    
                    <!-- Bulk Actions (show when selected) -->
                    <div x-show="selectedIds.length > 0" 
                         x-transition
                         class="flex flex-wrap items-center gap-2 md:gap-3 p-2.5 md:p-3 bg-primary-50 rounded-xl">
                        <span class="text-sm font-medium text-primary-700">
                            <span x-text="selectedIds.length"></span> dipilih
                        </span>
                        <div class="flex-1"></div>
                        <button @click="bulkWhatsApp()" class="px-2.5 md:px-3 py-1.5 bg-green-500 text-white rounded-lg text-xs md:text-sm hover:bg-green-600 transition flex items-center gap-1">
                            <i class="bi bi-whatsapp"></i>
                            <span class="hidden sm:inline">WhatsApp</span>
                        </button>
                        <button @click="openBulkAssignModal()" class="px-2.5 md:px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs md:text-sm hover:bg-blue-600 transition flex items-center gap-1">
                            <i class="bi bi-person-check"></i>
                            <span class="hidden sm:inline">Assign</span>
                        </button>
                        <button @click="exportSelected()" class="px-2.5 md:px-3 py-1.5 bg-primary-500 text-white rounded-lg text-xs md:text-sm hover:bg-primary-600 transition flex items-center gap-1">
                            <i class="bi bi-download"></i>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                        <button @click="confirmBulkDelete()" class="px-2.5 md:px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs md:text-sm hover:bg-red-600 transition flex items-center gap-1">
                            <i class="bi bi-trash"></i>
                            <span class="hidden sm:inline">Hapus</span>
                        </button>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" 
                                           @change="toggleSelectAll($event)"
                                           :checked="selectedIds.length === table.data.length && table.data.length > 0"
                                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                </th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider">Donatur</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Kontak</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">CS</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Segment</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button @click="sortBy('lifetime_value')" class="flex items-center gap-1 hover:text-primary-600">
                                        <span class="hidden sm:inline">Total Donasi</span>
                                        <span class="sm:hidden">Donasi</span>
                                        <i class="bi" :class="filters.sort_by === 'lifetime_value' ? (filters.sort_dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-caret-down text-gray-300'"></i>
                                    </button>
                                </th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                    <button @click="sortBy('frequency')" class="flex items-center gap-1 hover:text-primary-600">
                                        Freq
                                        <i class="bi" :class="filters.sort_by === 'frequency' ? (filters.sort_dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-caret-down text-gray-300'"></i>
                                    </button>
                                </th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-left text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Score</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 text-center text-[10px] md:text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <!-- Loading State -->
                            <tr x-show="loading.table">
                                <td colspan="9" class="px-4 py-8 md:py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 md:gap-3">
                                        <div class="spinner"></div>
                                        <p class="text-xs md:text-sm text-gray-500">Memuat data...</p>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Empty State -->
                            <tr x-show="!loading.table && table.data.length === 0">
                                <td colspan="9" class="px-4 py-8 md:py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 md:gap-3">
                                        <div class="w-12 h-12 md:w-16 md:h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="bi bi-inbox text-2xl md:text-3xl text-gray-400"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">Tidak ada data donatur</p>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Data Rows -->
                            <template x-for="donatur in table.data" :key="donatur.id">
                                <tr class="table-row-hover cursor-pointer" @click="openDetailPanel(donatur.id)">
                                    <td class="px-2 md:px-4 py-2 md:py-3" @click.stop>
                                        <input type="checkbox" 
                                               :value="donatur.id"
                                               :checked="selectedIds.includes(donatur.id)"
                                               @change="toggleSelect(donatur.id)"
                                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3">
                                        <div class="flex items-center gap-2 md:gap-3">
                                            <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-xs md:text-sm flex-shrink-0"
                                                 x-text="donatur.initial"></div>
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-800 text-sm md:text-base truncate" x-text="donatur.nama_donatur || '-'"></p>
                                                <p class="text-[10px] md:text-xs text-gray-500 truncate" x-text="donatur.did || '-'"></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3 hidden md:table-cell">
                                        <p class="text-sm text-gray-600" x-text="donatur.no_hp"></p>
                                        <p class="text-xs text-gray-400" x-text="donatur.email || '-'"></p>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3 hidden lg:table-cell">
                                        <span class="text-sm text-gray-600" x-text="donatur.nama_cs || '-'"></span>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3 hidden lg:table-cell">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                              :class="'badge-' + donatur.segment?.key"
                                              x-text="donatur.segment?.name || '-'"></span>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3">
                                        <span class="font-semibold text-gray-800 text-xs md:text-sm" x-text="donatur.lifetime_value_formatted"></span>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3 hidden md:table-cell">
                                        <span class="text-gray-600" x-text="donatur.frequency + 'x'"></span>
                                    </td>
                                    <td class="px-2 md:px-4 py-2 md:py-3 hidden lg:table-cell">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                              :class="'score-' + donatur.engagement_score?.label?.toLowerCase()"
                                              x-text="donatur.engagement_score?.score"></span>
                                    </td>
                                    <td class="px-2 md:px-4 py-3 text-center" @click.stop>
                                        <div class="flex items-center justify-center gap-0.5 md:gap-1">
                                            <a :href="donatur.wa_link" target="_blank" 
                                               class="p-1.5 md:p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                                               title="WhatsApp">
                                                <i class="bi bi-whatsapp text-sm md:text-base"></i>
                                            </a>
                                            <button @click="openDetailPanel(donatur.id)" 
                                                    class="p-1.5 md:p-2 text-cyan-600 hover:bg-cyan-50 rounded-lg transition"
                                                    title="Lihat Detail">
                                                <i class="bi bi-eye text-sm md:text-base"></i>
                                            </button>
                                            <button @click="openEditModal(donatur.id)" 
                                                    class="p-1.5 md:p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition hidden sm:block"
                                                    title="Edit">
                                                <i class="bi bi-pencil text-sm md:text-base"></i>
                                            </button>
                                            <button @click="confirmDelete(donatur.id, donatur.nama_donatur)" 
                                                    class="p-1.5 md:p-2 text-red-600 hover:bg-red-50 rounded-lg transition hidden sm:block"
                                                    title="Hapus">
                                                <i class="bi bi-trash text-sm md:text-base"></i>
                                            </button>
                                            <!-- Mobile dropdown for more actions -->
                                            <div class="relative sm:hidden" x-data="{ open: false }">
                                                <button @click="open = !open" class="p-1.5 text-gray-500 hover:bg-gray-50 rounded-lg transition">
                                                    <i class="bi bi-three-dots-vertical text-sm"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" 
                                                     class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                                                    <button @click="openEditModal(donatur.id); open = false" 
                                                            class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        <i class="bi bi-pencil text-blue-600"></i> Edit
                                                    </button>
                                                    <button @click="confirmDelete(donatur.id, donatur.nama_donatur); open = false" 
                                                            class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        <i class="bi bi-trash text-red-600"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span x-text="table.first_item"></span> - <span x-text="table.last_item"></span> dari <span x-text="formatNumber(table.total)"></span> donatur
                    </p>
                    <div class="flex items-center gap-2">
                        <button @click="prevPage()" 
                                :disabled="table.current_page <= 1"
                                class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="text-sm text-gray-600">
                            Halaman <span x-text="table.current_page"></span> dari <span x-text="table.last_page"></span>
                        </span>
                        <button @click="nextPage()" 
                                :disabled="table.current_page >= table.last_page"
                                class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
            
        </div>
    
    <!-- Detail Panel (Slide from Right) -->
    <div x-show="detailPanel.open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeDetailPanel()"
         class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[80]">
    </div>
    <aside :class="detailPanel.open ? 'translate-x-0' : 'translate-x-full'"
           class="fixed top-0 right-0 z-[90] w-full md:w-[480px] h-screen bg-white shadow-2xl transform transition-transform duration-300">
        <div class="h-full flex flex-col" @click.stop>
            <!-- Header with Profile -->
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-6 py-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-2xl font-bold"
                             x-text="detailPanel.donatur?.initial || '-'"></div>
                        <div>
                            <h2 class="text-xl font-bold" x-text="detailPanel.donatur?.nama_donatur || '-'"></h2>
                            <p class="text-primary-100 text-sm" x-text="detailPanel.donatur?.did || '-'"></p>
                        </div>
                    </div>
                    <button @click="closeDetailPanel()" class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <!-- Badges -->
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium"
                          x-text="detailPanel.donatur?.kat_donatur || 'N/A'"></span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                          :class="{
                              'bg-yellow-400 text-yellow-900': detailPanel.donatur?.segment?.key === 'vip',
                              'bg-green-400 text-green-900': detailPanel.donatur?.segment?.key === 'loyal',
                              'bg-blue-400 text-blue-900': detailPanel.donatur?.segment?.key === 'new',
                              'bg-orange-400 text-orange-900': detailPanel.donatur?.segment?.key === 'at_risk',
                              'bg-red-400 text-red-900': detailPanel.donatur?.segment?.key === 'churned',
                              'bg-gray-400 text-gray-900': !['vip','loyal','new','at_risk','churned'].includes(detailPanel.donatur?.segment?.key)
                          }"
                          x-text="detailPanel.donatur?.segment?.name || 'Regular'"></span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                          :class="{
                              'bg-green-400 text-green-900': detailPanel.donatur?.engagement_score?.label === 'Hot',
                              'bg-yellow-400 text-yellow-900': detailPanel.donatur?.engagement_score?.label === 'Warm',
                              'bg-red-400 text-red-900': detailPanel.donatur?.engagement_score?.label === 'Cold'
                          }"
                          x-text="'Score: ' + (detailPanel.donatur?.engagement_score?.score || 0)"></span>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 bg-gray-50">
                <a :href="detailPanel.donatur?.wa_link" target="_blank"
                   class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition text-sm font-medium">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <button @click="openEditModal(detailPanel.donatur?.id); closeDetailPanel()"
                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition text-sm font-medium">
                    <i class="bi bi-pencil"></i>
                    <span>Edit</span>
                </button>
                <button @click="switchDetailTab('notes')"
                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition text-sm font-medium">
                    <i class="bi bi-chat-dots"></i>
                    <span>Note</span>
                </button>
            </div>
            
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-100">
                <button @click="switchDetailTab('overview')"
                        :class="detailPanel.activeTab === 'overview' ? 'text-primary-600 border-primary-500' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition">
                    Overview
                </button>
                <button @click="switchDetailTab('history')"
                        :class="detailPanel.activeTab === 'history' ? 'text-primary-600 border-primary-500' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition">
                    History
                </button>
                <button @click="switchDetailTab('activity')"
                        :class="detailPanel.activeTab === 'activity' ? 'text-primary-600 border-primary-500' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition">
                    Activity
                </button>
                <button @click="switchDetailTab('notes')"
                        :class="detailPanel.activeTab === 'notes' ? 'text-primary-600 border-primary-500' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition">
                    Notes
                </button>
            </div>
            
            <!-- Tab Content -->
            <div class="flex-1 overflow-y-auto">
                
                <!-- Overview Tab -->
                <div x-show="detailPanel.activeTab === 'overview'" class="p-6 space-y-6">
                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Kontak</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-phone text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">No. HP</p>
                                    <p class="font-medium text-gray-800" x-text="detailPanel.donatur?.no_hp || '-'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-envelope text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Email</p>
                                    <p class="font-medium text-gray-800" x-text="detailPanel.donatur?.email || '-'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-geo-alt text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Alamat</p>
                                    <p class="font-medium text-gray-800" x-text="detailPanel.donatur?.alamat || '-'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Donation Stats -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Statistik Donasi</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-4">
                                <p class="text-xs text-primary-600 mb-1">Lifetime Value</p>
                                <p class="text-xl font-bold text-primary-700" x-text="detailPanel.donatur?.lifetime_value_formatted || 'Rp 0'"></p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
                                <p class="text-xs text-blue-600 mb-1">Total Transaksi</p>
                                <p class="text-xl font-bold text-blue-700" x-text="(detailPanel.donatur?.frequency || 0) + 'x'"></p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                                <p class="text-xs text-green-600 mb-1">Donasi Pertama</p>
                                <p class="text-sm font-semibold text-green-700" x-text="detailPanel.donatur?.first_donation_formatted || '-'"></p>
                            </div>
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4">
                                <p class="text-xs text-orange-600 mb-1">Donasi Terakhir</p>
                                <p class="text-sm font-semibold text-orange-700" x-text="detailPanel.donatur?.last_donation_formatted || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Score Breakdown -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Engagement Score Breakdown</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Recency (30%)</span>
                                    <span class="font-medium" x-text="detailPanel.donatur?.engagement_score?.breakdown?.recency || 0"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-500 rounded-full transition-all duration-500"
                                         :style="'width: ' + (detailPanel.donatur?.engagement_score?.breakdown?.recency || 0) + '%'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Frequency (30%)</span>
                                    <span class="font-medium" x-text="detailPanel.donatur?.engagement_score?.breakdown?.frequency || 0"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                                         :style="'width: ' + (detailPanel.donatur?.engagement_score?.breakdown?.frequency || 0) + '%'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Monetary (25%)</span>
                                    <span class="font-medium" x-text="detailPanel.donatur?.engagement_score?.breakdown?.monetary || 0"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500 rounded-full transition-all duration-500"
                                         :style="'width: ' + (detailPanel.donatur?.engagement_score?.breakdown?.monetary || 0) + '%'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Tenure (15%)</span>
                                    <span class="font-medium" x-text="detailPanel.donatur?.engagement_score?.breakdown?.tenure || 0"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-500 rounded-full transition-all duration-500"
                                         :style="'width: ' + (detailPanel.donatur?.engagement_score?.breakdown?.tenure || 0) + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Info Lainnya</h3>
                        <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Customer Service</span>
                                <span class="font-medium text-gray-800" x-text="detailPanel.donatur?.nama_cs || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal Registrasi</span>
                                <span class="font-medium text-gray-800" x-text="detailPanel.donatur?.tanggal_registrasi_formatted || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Program</span>
                                <span class="font-medium text-gray-800" x-text="detailPanel.donatur?.program || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Channel</span>
                                <span class="font-medium text-gray-800" x-text="detailPanel.donatur?.channel || '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- History Tab -->
                <div x-show="detailPanel.activeTab === 'history'" class="p-6">
                    <!-- Loading -->
                    <div x-show="detailPanel.loadingHistory" class="flex justify-center py-12">
                        <div class="spinner"></div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!detailPanel.loadingHistory && detailPanel.history.length === 0" class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="bi bi-inbox text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Belum ada riwayat donasi</p>
                    </div>
                    
                    <!-- History Timeline -->
                    <div x-show="!detailPanel.loadingHistory && detailPanel.history.length > 0" class="space-y-4">
                        <template x-for="item in detailPanel.history" :key="item.id">
                            <div class="relative pl-6 pb-4 border-l-2 border-primary-200 last:border-transparent">
                                <div class="absolute -left-2 top-0 w-4 h-4 bg-primary-500 rounded-full border-2 border-white"></div>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-lg font-bold text-primary-600" x-text="item.jumlah_formatted"></span>
                                        <span class="text-xs text-gray-400" x-text="item.tanggal"></span>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex gap-2">
                                            <span class="text-gray-400 w-16">Kategori</span>
                                            <span class="text-gray-700" x-text="item.kategori || '-'"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="text-gray-400 w-16">Program</span>
                                            <span class="text-gray-700" x-text="item.program || '-'"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="text-gray-400 w-16">CS</span>
                                            <span class="text-gray-700" x-text="item.cs || '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Activity Tab -->
                <div x-show="detailPanel.activeTab === 'activity'" class="p-6">
                    <!-- Loading -->
                    <div x-show="detailPanel.loadingActivity" class="flex justify-center py-12">
                        <div class="spinner"></div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!detailPanel.loadingActivity && detailPanel.activityLogs.length === 0" class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="bi bi-clock-history text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Belum ada aktivitas tercatat</p>
                    </div>
                    
                    <!-- Activity Timeline -->
                    <div x-show="!detailPanel.loadingActivity && detailPanel.activityLogs.length > 0" class="space-y-3">
                        <template x-for="log in detailPanel.activityLogs" :key="log.id">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                     :class="{
                                         'bg-green-100 text-green-600': log.action === 'created',
                                         'bg-blue-100 text-blue-600': log.action === 'updated',
                                         'bg-purple-100 text-purple-600': log.action === 'note_added',
                                         'bg-gray-100 text-gray-600': !['created', 'updated', 'note_added'].includes(log.action)
                                     }">
                                    <i :class="log.icon"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-800" x-text="log.description"></p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <span x-text="log.created_at"></span>
                                        <span x-show="log.user"> • <span x-text="log.user"></span></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Notes Tab -->
                <div x-show="detailPanel.activeTab === 'notes'" class="p-6 flex flex-col h-full">
                    <!-- Add Note Form -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="detailPanel.newNote"
                                   @keydown.enter="addNote()"
                                   placeholder="Tulis catatan baru..."
                                   class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm">
                            <button @click="addNote()"
                                    :disabled="!detailPanel.newNote || detailPanel.savingNote"
                                    class="px-4 py-2 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-send" :class="detailPanel.savingNote && 'animate-pulse'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Loading -->
                    <div x-show="detailPanel.loadingNotes" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!detailPanel.loadingNotes && detailPanel.notes.length === 0" class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="bi bi-sticky text-xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Belum ada catatan</p>
                    </div>
                    
                    <!-- Notes List -->
                    <div x-show="!detailPanel.loadingNotes && detailPanel.notes.length > 0" class="space-y-3 flex-1 overflow-y-auto">
                        <template x-for="note in detailPanel.notes" :key="note.id">
                            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3">
                                <p class="text-sm text-gray-800 whitespace-pre-wrap" x-text="note.note"></p>
                                <p class="text-xs text-gray-400 mt-2">
                                    <span x-text="note.created_at"></span>
                                    <span x-show="note.user"> • <span x-text="note.user"></span></span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
                
            </div>
        </div>
    </aside>
    
    <!-- Create/Edit Modal -->
    <div x-show="modals.form" 
         role="dialog" aria-modal="true" aria-label="Form Donatur"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-start sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeFormModal()">
        <div @click.stop class="bg-white sm:rounded-2xl shadow-2xl w-full sm:max-w-2xl min-h-screen sm:min-h-0 sm:max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 bg-gradient-to-r from-primary-500 to-primary-600 sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-person-plus-fill text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-white" x-text="formData.id ? 'Edit Donatur' : 'Tambah Donatur Baru'"></h2>
                        <p class="text-xs text-white/70 hidden sm:block">Lengkapi informasi donatur di bawah ini</p>
                    </div>
                </div>
                <button @click="closeFormModal()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            
            <!-- Form Body -->
            <div class="flex-1 overflow-y-auto">
                <form @submit.prevent="submitForm()" class="p-4 sm:p-6 space-y-5">
                    
                    <!-- Phone Check Warning -->
                    <div x-show="formData.phoneWarning" x-transition class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 sm:p-4">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-yellow-800 text-sm">No HP sudah terdaftar!</p>
                                <p class="text-xs text-yellow-700 mt-0.5" x-text="formData.phoneWarning"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- Section 1: Data Utama (Wajib) -->
                    <!-- ============================================ -->
                    <div class="bg-primary-50/50 rounded-2xl p-4 border border-primary-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                                <i class="bi bi-person-badge-fill text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">Data Utama</h3>
                                <p class="text-[10px] text-gray-500">Informasi wajib diisi</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Nama Donatur - Full Width -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="bi bi-person absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" 
                                           x-model="formData.nama_donatur"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"
                                           :class="formData.errors?.nama_donatur ? 'border-red-500 bg-red-50' : 'bg-white'"
                                           placeholder="Masukkan nama lengkap">
                                </div>
                                <p x-show="formData.errors?.nama_donatur" class="text-red-500 text-xs mt-1" x-text="formData.errors?.nama_donatur"></p>
                            </div>
                            
                            <!-- No HP - dengan kode negara yang lebih baik -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    No WhatsApp/HP <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select x-model="formData.kode_negara"
                                            class="w-20 sm:w-24 px-2 sm:px-3 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition bg-white text-sm font-medium">
                                        <option value="+62">🇮🇩 +62</option>
                                        <option value="+60">🇲🇾 +60</option>
                                        <option value="+65">🇸🇬 +65</option>
                                        <option value="+1">🇺🇸 +1</option>
                                    </select>
                                    <div class="flex-1 relative">
                                        <i class="bi bi-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="tel" 
                                               x-model="formData.no_hp"
                                               @blur="checkPhoneDuplicate()"
                                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"
                                               :class="formData.errors?.no_hp || formData.phoneWarning ? 'border-red-500 bg-red-50' : 'bg-white'"
                                               placeholder="8123456789">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Contoh: 81234567890 (tanpa 0 di depan)</p>
                                <p x-show="formData.errors?.no_hp" class="text-red-500 text-xs mt-1" x-text="formData.errors?.no_hp"></p>
                            </div>
                            
                            <!-- Tanggal Registrasi & CS - 2 kolom -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Tanggal Registrasi <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="date" 
                                               x-model="formData.tanggal_registrasi"
                                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               :class="formData.errors?.tanggal_registrasi ? 'border-red-500 bg-red-50' : ''">
                                    </div>
                                    <p x-show="formData.errors?.tanggal_registrasi" class="text-red-500 text-xs mt-1" x-text="formData.errors?.tanggal_registrasi"></p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer Service</label>
                                    <div class="relative">
                                        <i class="bi bi-headset absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <select x-model="formData.nama_cs"
                                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition bg-white text-sm appearance-none">
                                            <option value="">Pilih CS</option>
                                            @php
                                                $csListForm = \App\Models\CustomerService::orderBy('name')->get();
                                            @endphp
                                            @foreach($csListForm as $cs)
                                                <option value="{{ $cs->name }}">{{ $cs->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- Section 2: Info Tambahan (Accordion/Collapsible) -->
                    <!-- ============================================ -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-2xl overflow-hidden">
                        <button type="button" 
                                @click="expanded = !expanded"
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-person-lines-fill text-gray-600 text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="text-sm font-semibold text-gray-800">Informasi Tambahan</h3>
                                    <p class="text-[10px] text-gray-500">Data opsional untuk kelengkapan</p>
                                </div>
                            </div>
                            <i class="bi text-gray-400 transition-transform duration-200" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                        
                        <div x-show="expanded" x-collapse>
                            <div class="p-4 space-y-4 border-t border-gray-100">
                                <!-- Nama Panggilan & Jenis Kelamin -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Nama Panggilan</label>
                                        <input type="text" 
                                               x-model="formData.nama_panggilan"
                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               placeholder="Panggilan sehari-hari">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Jenis Kelamin</label>
                                        <select x-model="formData.jenis_kelamin"
                                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition bg-white text-sm">
                                            <option value="">Pilih</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Kategori & Kode Donatur -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Kategori Donatur</label>
                                        <select x-model="formData.kat_donatur"
                                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition bg-white text-sm">
                                            <option value="">Pilih Kategori</option>
                                            <option value="Retail">Retail</option>
                                            <option value="Corporate">Corporate</option>
                                            <option value="Community">Community</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Kode Donatur</label>
                                        <select x-model="formData.kode_donatur"
                                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition bg-white text-sm">
                                            <option value="">Pilih Kode</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                                    <div class="relative">
                                        <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="email" 
                                               x-model="formData.email"
                                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               :class="formData.errors?.email ? 'border-red-500' : ''"
                                               placeholder="email@example.com">
                                    </div>
                                </div>
                                
                                <!-- Sosmed -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Akun Sosial Media</label>
                                    <div class="relative">
                                        <i class="bi bi-instagram absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="text" 
                                               x-model="formData.sosmed_account"
                                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               placeholder="@username">
                                    </div>
                                </div>
                                
                                <!-- Alamat -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Alamat</label>
                                    <textarea x-model="formData.alamat"
                                              rows="2"
                                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none text-sm bg-white"
                                              placeholder="Alamat lengkap donatur"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- Section 3: Info Sumber (Accordion) -->
                    <!-- ============================================ -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-2xl overflow-hidden">
                        <button type="button" 
                                @click="expanded = !expanded"
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-diagram-3 text-gray-600 text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="text-sm font-semibold text-gray-800">Sumber & Channel</h3>
                                    <p class="text-[10px] text-gray-500">Info akuisisi donatur</p>
                                </div>
                            </div>
                            <i class="bi text-gray-400 transition-transform duration-200" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                        
                        <div x-show="expanded" x-collapse>
                            <div class="p-4 space-y-4 border-t border-gray-100">
                                <!-- Program & Channel -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Program</label>
                                        <input type="text" 
                                               x-model="formData.program"
                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               placeholder="Program donasi">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Channel</label>
                                        <input type="text" 
                                               x-model="formData.channel"
                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                               placeholder="Channel akuisisi">
                                    </div>
                                </div>
                                
                                <!-- Fundraiser -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Fundraiser</label>
                                    <input type="text" 
                                           x-model="formData.fundraiser"
                                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm bg-white"
                                           placeholder="Nama fundraiser">
                                </div>
                                
                                <!-- Keterangan -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Keterangan</label>
                                    <textarea x-model="formData.keterangan"
                                              rows="2"
                                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none text-sm bg-white"
                                              placeholder="Catatan tambahan"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            
            <!-- Footer - Sticky di mobile -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 border-t border-gray-100 bg-white sticky bottom-0">
                <div class="text-xs text-gray-500 hidden sm:block">
                    <span class="text-red-500">*</span> Wajib diisi
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button @click="closeFormModal()" 
                            type="button"
                            class="flex-1 sm:flex-none px-5 py-3 sm:py-2.5 border border-gray-200 rounded-xl hover:bg-gray-100 transition font-medium text-gray-700 text-sm">
                        Batal
                    </button>
                    <button @click="submitForm()" 
                            type="button"
                            :disabled="formData.saving"
                            class="flex-1 sm:flex-none px-5 py-3 sm:py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm shadow-lg shadow-primary-500/30">
                        <span x-show="formData.saving" class="spinner-sm"></span>
                        <i x-show="!formData.saving" class="bi" :class="formData.id ? 'bi-check-lg' : 'bi-plus-lg'"></i>
                        <span x-text="formData.saving ? 'Menyimpan...' : (formData.id ? 'Update' : 'Simpan')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="modals.delete" 
         role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Donatur"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-3xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-500 mb-6" x-text="deleteConfirm.message"></p>
                <div class="flex gap-3 justify-center">
                    <button @click="modals.delete = false" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="executeDelete()" class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    
@push('scripts')
    <!-- Alpine.js App -->
    <script>
        function donaturCrmApp() {
            return {
                // UI State
                sidebarOpen: false,
                
                // Loading States
                loading: {
                    global: false,
                    table: false,
                    stats: false,
                    message: ''
                },
                
                // Stats Data
                stats: {
                    total_donatur: 0,
                    donatur_baru: 0,
                    donatur_aktif: 0,
                    vip: 0,
                    at_risk: 0,
                    churned: 0,
                    growth_rate: 0
                },
                
                // Segments Data
                segments: {},
                
                // Smart Alerts
                smartAlerts: {
                    loading: false,
                    total: 0,
                    alerts: []
                },
                
                // Follow-up Center
                followUp: {
                    loading: false,
                    selectedPriority: null,
                    tasks: {
                        high_priority: { count: 0, data: [] },
                        medium_priority: { count: 0, data: [] },
                        low_priority: { count: 0, data: [] },
                        total_tasks: 0
                    },
                    dismissed: [] // IDs yang sudah di-mark done (temporary, tidak persist)
                },
                
                // Filters
                filters: {
                    search: '',
                    kat_donatur: '',
                    nama_cs: '',
                    segment: '',
                    sort_by: 'created_at',
                    sort_dir: 'desc'
                },
                
                // Table Data
                table: {
                    data: [],
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    per_page: 20,
                    first_item: 0,
                    last_item: 0
                },
                
                // Initial Loading State
                initialLoading: {
                    show: true,
                    progress: 0,
                    status: 'Memulai...',
                    currentTip: 0,
                    tips: [
                        { icon: 'bi-lightbulb', text: 'Donatur VIP adalah mereka yang berdonasi total ≥10 juta atau ≥10 transaksi.' },
                        { icon: 'bi-bullseye', text: 'Gunakan filter Segment untuk fokus pada donatur yang perlu perhatian khusus.' },
                        { icon: 'bi-graph-up', text: 'Engagement Score membantu Anda prioritaskan follow-up donatur.' },
                        { icon: 'bi-chat-dots', text: 'Tambahkan catatan di setiap donatur untuk tracking komunikasi.' },
                        { icon: 'bi-cursor', text: 'Klik baris donatur untuk melihat detail lengkap dan riwayat donasi.' },
                        { icon: 'bi-bell', text: 'Cek Follow-up Center secara rutin untuk donatur yang perlu di-contact.' },
                        { icon: 'bi-whatsapp', text: 'Tombol WhatsApp langsung terhubung ke chat donatur.' },
                        { icon: 'bi-exclamation-triangle', text: 'Donatur "At Risk" adalah yang belum donasi 60-90 hari, segera follow-up!' },
                        { icon: 'bi-file-earmark-excel', text: 'Export data ke Excel untuk analisis lebih lanjut.' },
                        { icon: 'bi-people', text: 'Assign CS ke donatur untuk pembagian tugas follow-up.' }
                    ]
                },
                
                // Selection
                selectedIds: [],
                
                // Detail Panel
                detailPanel: {
                    open: false,
                    donatur: null,
                    activeTab: 'overview',
                    history: [],
                    loadingHistory: false,
                    activityLogs: [],
                    loadingActivity: false,
                    notes: [],
                    loadingNotes: false,
                    newNote: '',
                    savingNote: false
                },
                
                // Modals
                modals: {
                    form: false,
                    delete: false,
                    bulkAssign: false
                },
                
                // Form Data
                formData: {
                    id: null,
                    nama_cs: '',
                    kat_donatur: '',
                    kode_donatur: '',
                    kode_negara: '+62',
                    no_hp: '',
                    tanggal_registrasi: '',
                    nama_donatur: '',
                    nama_panggilan: '',
                    jenis_kelamin: '',
                    email: '',
                    alamat: '',
                    sosmed_account: '',
                    program: '',
                    channel: '',
                    fundraiser: '',
                    keterangan: '',
                    errors: {},
                    saving: false,
                    phoneWarning: ''
                },
                
                // Delete Confirmation
                deleteConfirm: {
                    id: null,
                    ids: [],
                    message: '',
                    isBulk: false
                },
                
                // Initialize
                async init() {
                    // Start tip rotation
                    this.startTipRotation();
                    
                    // Load data with progress tracking
                    await this.loadInitialData();
                },
                
                // Rotate tips every 3 seconds
                startTipRotation() {
                    setInterval(() => {
                        if (this.initialLoading.show) {
                            this.initialLoading.currentTip = (this.initialLoading.currentTip + 1) % this.initialLoading.tips.length;
                        }
                    }, 3000);
                },
                
                // Load initial data with progress
                async loadInitialData() {
                    const steps = [
                        { fn: () => this.loadStats(), status: 'Memuat statistik donatur...', progress: 20 },
                        { fn: () => this.loadSegments(), status: 'Memuat segmentasi...', progress: 40 },
                        { fn: () => this.loadTable(), status: 'Memuat data tabel...', progress: 70 },
                        { fn: () => this.loadFollowUpTasks(), status: 'Memuat follow-up tasks...', progress: 85 },
                        { fn: () => this.loadAlerts(), status: 'Memuat notifikasi...', progress: 95 }
                    ];
                    
                    for (const step of steps) {
                        this.initialLoading.status = step.status;
                        this.initialLoading.progress = step.progress;
                        await step.fn();
                        // Small delay for smooth progress animation
                        await new Promise(r => setTimeout(r, 200));
                    }
                    
                    // Complete
                    this.initialLoading.progress = 100;
                    this.initialLoading.status = 'Selesai! ✨';
                    
                    // Hide loading after short delay
                    await new Promise(r => setTimeout(r, 500));
                    this.initialLoading.show = false;
                },
                
                // API Calls
                async loadStats() {
                    this.loading.stats = true;
                    try {
                        const res = await fetch('/api/donatur/crm/stats');
                        const data = await res.json();
                        this.stats = data;
                    } catch (e) {
                        console.error('Error loading stats:', e);
                    }
                    this.loading.stats = false;
                },
                
                async loadSegments() {
                    try {
                        const res = await fetch('/api/donatur/crm/segments');
                        const data = await res.json();
                        this.segments = data;
                    } catch (e) {
                        console.error('Error loading segments:', e);
                    }
                },
                
                async loadFollowUpTasks() {
                    this.followUp.loading = true;
                    try {
                        const res = await fetch('/api/donatur/crm/follow-up-tasks');
                        const data = await res.json();
                        this.followUp.tasks = data;
                    } catch (e) {
                        console.error('Error loading follow-up tasks:', e);
                    }
                    this.followUp.loading = false;
                },
                
                async loadAlerts() {
                    this.smartAlerts.loading = true;
                    try {
                        const res = await fetch('/api/donatur/crm/alerts');
                        const data = await res.json();
                        this.smartAlerts.total = data.total;
                        this.smartAlerts.alerts = data.alerts;
                    } catch (e) {
                        console.error('Error loading alerts:', e);
                    }
                    this.smartAlerts.loading = false;
                },
                
                handleAlertAction(alert) {
                    if (alert.action === 'filter_segment') {
                        this.filterBySegment(alert.action_value);
                    }
                },
                
                selectFollowUpPriority(priority) {
                    this.followUp.selectedPriority = this.followUp.selectedPriority === priority ? null : priority;
                },
                
                getFollowUpTitle() {
                    const titles = {
                        high: '🔥 VIP At Risk - Prioritas Tinggi',
                        medium: '⚠️ At Risk - Prioritas Sedang',
                        low: '👋 New Welcome - Prioritas Rendah'
                    };
                    return titles[this.followUp.selectedPriority] || '';
                },
                
                getFollowUpList() {
                    const priority = this.followUp.selectedPriority;
                    if (!priority) return [];
                    
                    const priorityKey = priority === 'high' ? 'high_priority' : 
                                       priority === 'medium' ? 'medium_priority' : 'low_priority';
                    
                    const data = this.followUp.tasks[priorityKey]?.data || [];
                    // Filter out dismissed items
                    return data.filter(item => !this.followUp.dismissed.includes(item.id));
                },
                
                markFollowUpDone(id) {
                    // Add to dismissed list (temporary - will reset on page refresh)
                    if (!this.followUp.dismissed.includes(id)) {
                        this.followUp.dismissed.push(id);
                        this.showToast('Task marked as done', 'success');
                    }
                },
                
                async loadTable() {
                    this.loading.table = true;
                    try {
                        const params = new URLSearchParams({
                            page: this.table.current_page,
                            per_page: this.table.per_page,
                            search: this.filters.search,
                            kat_donatur: this.filters.kat_donatur,
                            nama_cs: this.filters.nama_cs,
                            segment: this.filters.segment,
                            sort_by: this.filters.sort_by,
                            sort_dir: this.filters.sort_dir
                        });
                        const res = await fetch('/api/donatur?' + params.toString());
                        const data = await res.json();
                        this.table = {
                            data: data.data,
                            total: data.total,
                            current_page: data.current_page,
                            last_page: data.last_page,
                            per_page: data.per_page,
                            first_item: data.first_item,
                            last_item: data.last_item
                        };
                        this.selectedIds = [];
                    } catch (e) {
                        console.error('Error loading table:', e);
                    }
                    this.loading.table = false;
                },
                
                // Filters
                filterBySegment(segment) {
                    this.filters.segment = this.filters.segment === segment ? '' : segment;
                    this.table.current_page = 1;
                    this.loadTable();
                },
                
                clearSegmentFilter() {
                    this.filters.segment = '';
                    this.table.current_page = 1;
                    this.loadTable();
                },
                
                sortBy(column) {
                    if (this.filters.sort_by === column) {
                        this.filters.sort_dir = this.filters.sort_dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.filters.sort_by = column;
                        this.filters.sort_dir = 'desc';
                    }
                    this.loadTable();
                },
                
                // Pagination
                prevPage() {
                    if (this.table.current_page > 1) {
                        this.table.current_page--;
                        this.loadTable();
                    }
                },
                
                nextPage() {
                    if (this.table.current_page < this.table.last_page) {
                        this.table.current_page++;
                        this.loadTable();
                    }
                },
                
                // Selection
                toggleSelect(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx > -1) {
                        this.selectedIds.splice(idx, 1);
                    } else {
                        this.selectedIds.push(id);
                    }
                },
                
                toggleSelectAll(event) {
                    if (event.target.checked) {
                        this.selectedIds = this.table.data.map(d => d.id);
                    } else {
                        this.selectedIds = [];
                    }
                },
                
                // Modals
                openCreateModal() {
                    this.resetFormData();
                    // Set default tanggal hari ini
                    this.formData.tanggal_registrasi = new Date().toISOString().split('T')[0];
                    this.modals.form = true;
                },
                
                async openEditModal(id) {
                    this.resetFormData();
                    this.formData.id = id;
                    this.formData.saving = true;
                    this.modals.form = true;
                    
                    try {
                        const res = await fetch(`/api/donatur/${id}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.formData.nama_cs = data.nama_cs || '';
                            this.formData.kat_donatur = data.kat_donatur || '';
                            this.formData.kode_donatur = data.kode_donatur || '';
                            this.formData.kode_negara = data.kode_negara || '+62';
                            this.formData.no_hp = data.no_hp || '';
                            this.formData.tanggal_registrasi = data.tanggal_registrasi || '';
                            this.formData.nama_donatur = data.nama_donatur || '';
                            this.formData.nama_panggilan = data.nama_panggilan || '';
                            this.formData.jenis_kelamin = data.jenis_kelamin || '';
                            this.formData.email = data.email || '';
                            this.formData.alamat = data.alamat || '';
                            this.formData.sosmed_account = data.sosmed_account || '';
                            this.formData.program = data.program || '';
                            this.formData.channel = data.channel || '';
                            this.formData.fundraiser = data.fundraiser || '';
                            this.formData.keterangan = data.keterangan || '';
                        }
                    } catch (e) {
                        console.error('Error loading donatur:', e);
                    }
                    this.formData.saving = false;
                },
                
                closeFormModal() {
                    this.modals.form = false;
                    this.resetFormData();
                },
                
                resetFormData() {
                    this.formData = {
                        id: null,
                        nama_cs: '',
                        kat_donatur: '',
                        kode_donatur: '',
                        kode_negara: '+62',
                        no_hp: '',
                        tanggal_registrasi: '',
                        nama_donatur: '',
                        nama_panggilan: '',
                        jenis_kelamin: '',
                        email: '',
                        alamat: '',
                        sosmed_account: '',
                        program: '',
                        channel: '',
                        fundraiser: '',
                        keterangan: '',
                        errors: {},
                        saving: false,
                        phoneWarning: ''
                    };
                },
                
                async checkPhoneDuplicate() {
                    if (!this.formData.no_hp || this.formData.no_hp.length < 8) return;
                    
                    try {
                        const res = await fetch(`/api/donatur/check-phone/${this.formData.no_hp}`);
                        const data = await res.json();
                        
                        if (data.exists && (!this.formData.id || data.donatur.id !== this.formData.id)) {
                            this.formData.phoneWarning = `No HP ini sudah terdaftar atas nama ${data.donatur.nama_donatur} (${data.donatur.did})`;
                        } else {
                            this.formData.phoneWarning = '';
                        }
                    } catch (e) {
                        console.error('Error checking phone:', e);
                    }
                },
                
                validateForm() {
                    this.formData.errors = {};
                    
                    if (!this.formData.nama_donatur || this.formData.nama_donatur.trim() === '') {
                        this.formData.errors.nama_donatur = 'Nama donatur wajib diisi';
                    }
                    if (!this.formData.no_hp || this.formData.no_hp.trim() === '') {
                        this.formData.errors.no_hp = 'No HP wajib diisi';
                    }
                    if (!this.formData.tanggal_registrasi) {
                        this.formData.errors.tanggal_registrasi = 'Tanggal registrasi wajib diisi';
                    }
                    if (this.formData.email && !this.formData.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        this.formData.errors.email = 'Format email tidak valid';
                    }
                    
                    return Object.keys(this.formData.errors).length === 0;
                },
                
                async submitForm() {
                    if (!this.validateForm()) return;
                    if (this.formData.phoneWarning && !this.formData.id) {
                        if (!confirm('No HP sudah terdaftar. Yakin ingin melanjutkan?')) return;
                    }
                    
                    this.formData.saving = true;
                    
                    const payload = {
                        nama_cs: this.formData.nama_cs,
                        kat_donatur: this.formData.kat_donatur,
                        kode_donatur: this.formData.kode_donatur,
                        kode_negara: this.formData.kode_negara,
                        no_hp: this.formData.no_hp,
                        tanggal_registrasi: this.formData.tanggal_registrasi,
                        nama_donatur: this.formData.nama_donatur,
                        nama_panggilan: this.formData.nama_panggilan,
                        jenis_kelamin: this.formData.jenis_kelamin,
                        email: this.formData.email,
                        alamat: this.formData.alamat,
                        sosmed_account: this.formData.sosmed_account,
                        program: this.formData.program,
                        channel: this.formData.channel,
                        fundraiser: this.formData.fundraiser,
                        keterangan: this.formData.keterangan
                    };
                    
                    try {
                        const url = this.formData.id ? `/api/donatur/${this.formData.id}` : '/api/donatur';
                        const method = this.formData.id ? 'PUT' : 'POST';
                        
                        const res = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const data = await res.json();
                        
                        if (res.ok) {
                            this.closeFormModal();
                            this.loadTable();
                            this.loadStats();
                            this.loadSegments();
                            // Show success toast
                            this.showToast(this.formData.id ? 'Donatur berhasil diupdate!' : 'Donatur berhasil ditambahkan!', 'success');
                        } else {
                            if (data.errors) {
                                this.formData.errors = data.errors;
                            } else if (data.error) {
                                this.showToast(data.error, 'error');
                            }
                        }
                    } catch (e) {
                        console.error('Error saving donatur:', e);
                        this.showToast('Terjadi kesalahan saat menyimpan data', 'error');
                    }
                    
                    this.formData.saving = false;
                },
                
                showToast(message, type = 'info') {
                    // Simple toast notification
                    const toast = document.createElement('div');
                    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl shadow-lg z-[200] transition-all transform translate-y-0 ${
                        type === 'success' ? 'bg-green-500 text-white' : 
                        type === 'error' ? 'bg-red-500 text-white' : 
                        'bg-gray-800 text-white'
                    }`;
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.classList.add('opacity-0', 'translate-y-2');
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                },
                
                openDetailPanel(id) {
                    this.detailPanel.donatur = this.table.data.find(d => d.id === id);
                    this.detailPanel.activeTab = 'overview';
                    this.detailPanel.history = [];
                    this.detailPanel.open = true;
                    // Fetch full donatur details
                    this.loadDonaturDetails(id);
                },
                
                async loadDonaturDetails(id) {
                    try {
                        const res = await fetch(`/api/donatur/${id}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.detailPanel.donatur = data;
                        }
                    } catch (e) {
                        console.error('Error loading donatur details:', e);
                    }
                },
                
                async loadHistory() {
                    if (!this.detailPanel.donatur) return;
                    
                    this.detailPanel.loadingHistory = true;
                    try {
                        const res = await fetch(`/api/donatur/${this.detailPanel.donatur.id}/history`);
                        if (res.ok) {
                            const result = await res.json();
                            this.detailPanel.history = result.data || [];
                        }
                    } catch (e) {
                        console.error('Error loading history:', e);
                    }
                    this.detailPanel.loadingHistory = false;
                },
                
                async loadActivityLogs() {
                    if (!this.detailPanel.donatur) return;
                    
                    this.detailPanel.loadingActivity = true;
                    try {
                        const res = await fetch(`/api/donatur/${this.detailPanel.donatur.id}/activity-logs`);
                        if (res.ok) {
                            const result = await res.json();
                            this.detailPanel.activityLogs = result.data || [];
                        }
                    } catch (e) {
                        console.error('Error loading activity logs:', e);
                    }
                    this.detailPanel.loadingActivity = false;
                },
                
                async loadNotes() {
                    if (!this.detailPanel.donatur) return;
                    
                    this.detailPanel.loadingNotes = true;
                    try {
                        const res = await fetch(`/api/donatur/${this.detailPanel.donatur.id}/notes`);
                        if (res.ok) {
                            const result = await res.json();
                            this.detailPanel.notes = result.data || [];
                        }
                    } catch (e) {
                        console.error('Error loading notes:', e);
                    }
                    this.detailPanel.loadingNotes = false;
                },
                
                async addNote() {
                    if (!this.detailPanel.newNote || !this.detailPanel.donatur) return;
                    
                    this.detailPanel.savingNote = true;
                    try {
                        const res = await fetch(`/api/donatur/${this.detailPanel.donatur.id}/note`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ note: this.detailPanel.newNote })
                        });
                        
                        if (res.ok) {
                            const result = await res.json();
                            this.detailPanel.notes.unshift(result.data);
                            this.detailPanel.newNote = '';
                            this.showToast('Catatan berhasil ditambahkan', 'success');
                        } else {
                            this.showToast('Gagal menambah catatan', 'error');
                        }
                    } catch (e) {
                        console.error('Error adding note:', e);
                        this.showToast('Terjadi kesalahan', 'error');
                    }
                    this.detailPanel.savingNote = false;
                },
                
                switchDetailTab(tab) {
                    this.detailPanel.activeTab = tab;
                    if (tab === 'history' && this.detailPanel.history.length === 0) {
                        this.loadHistory();
                    }
                    if (tab === 'activity' && this.detailPanel.activityLogs.length === 0) {
                        this.loadActivityLogs();
                    }
                    if (tab === 'notes' && this.detailPanel.notes.length === 0) {
                        this.loadNotes();
                    }
                },
                
                closeDetailPanel() {
                    this.detailPanel.open = false;
                    this.detailPanel.donatur = null;
                    this.detailPanel.activeTab = 'overview';
                    this.detailPanel.history = [];
                    this.detailPanel.activityLogs = [];
                    this.detailPanel.notes = [];
                    this.detailPanel.newNote = '';
                },
                
                // Delete
                confirmDelete(id, name) {
                    this.deleteConfirm = {
                        id: id,
                        ids: [],
                        message: `Apakah Anda yakin ingin menghapus donatur "${name}"?`,
                        isBulk: false
                    };
                    this.modals.delete = true;
                },
                
                confirmBulkDelete() {
                    this.deleteConfirm = {
                        id: null,
                        ids: [...this.selectedIds],
                        message: `Apakah Anda yakin ingin menghapus ${this.selectedIds.length} donatur yang dipilih?`,
                        isBulk: true
                    };
                    this.modals.delete = true;
                },
                
                async executeDelete() {
                    this.loading.global = true;
                    this.loading.message = 'Menghapus data...';
                    this.modals.delete = false;
                    
                    try {
                        if (this.deleteConfirm.isBulk) {
                            await fetch('/api/donatur/bulk/delete', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ ids: this.deleteConfirm.ids })
                            });
                        } else {
                            await fetch('/api/donatur/' + this.deleteConfirm.id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                        }
                        await this.loadStats();
                        await this.loadSegments();
                        await this.loadTable();
                    } catch (e) {
                        console.error('Error deleting:', e);
                        alert('Gagal menghapus data');
                    }
                    
                    this.loading.global = false;
                },
                
                // Bulk Actions
                bulkWhatsApp() {
                    const phones = this.table.data
                        .filter(d => this.selectedIds.includes(d.id))
                        .map(d => d.no_hp)
                        .join('\n');
                    navigator.clipboard.writeText(phones);
                    alert('Nomor HP telah disalin ke clipboard!');
                },
                
                openBulkAssignModal() {
                    this.modals.bulkAssign = true;
                },
                
                exportSelected() {
                    const ids = this.selectedIds.join(',');
                    window.location.href = '/api/donatur/export/excel?ids=' + ids;
                },
                
                // Helpers
                formatNumber(num) {
                    if (num === undefined || num === null) return '-';
                    return new Intl.NumberFormat('id-ID').format(num);
                },
                
                getSegmentIcon(key) {
                    const icons = {
                        vip: '<i class="bi bi-star-fill text-yellow-500"></i>',
                        loyal: '<i class="bi bi-heart-fill text-green-500"></i>',
                        new: '<i class="bi bi-sparkle text-blue-500"></i>',
                        one_time: '<i class="bi bi-person text-gray-500"></i>',
                        at_risk: '<i class="bi bi-exclamation-triangle-fill text-orange-500"></i>',
                        churned: '<i class="bi bi-x-circle-fill text-red-500"></i>',
                        never_donated: '<i class="bi bi-question-circle text-slate-500"></i>'
                    };
                    return icons[key] || '<i class="bi bi-circle"></i>';
                }
            };
        }
    </script>
@endpush

</x-layouts.app>
