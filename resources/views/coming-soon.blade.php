<x-layouts.app active="" title="{{ $menu }} - Coming Soon | Abbarat" :xCloak="false">

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .float-animation { animation: float 3s ease-in-out infinite; }
    .pulse-ring { animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite; }
    .shimmer {
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }
    .blob-cs-1 {
        position: absolute; width: 400px; height: 400px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.1) 100%);
        border-radius: 50%; filter: blur(60px); top: -100px; right: -100px;
    }
    .blob-cs-2 {
        position: absolute; width: 300px; height: 300px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
        border-radius: 50%; filter: blur(60px); bottom: -50px; left: -50px;
    }
</style>
@endpush

<!-- Coming Soon Content - centered in main area -->
<div class="min-h-screen flex items-center justify-center p-4 relative">
    <!-- Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="blob-cs-1"></div>
        <div class="blob-cs-2"></div>
    </div>
    
    <!-- Main Content -->
    <div class="relative z-10 max-w-lg w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header Gradient -->
            <div class="bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 px-8 py-10 text-center relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                
                <!-- Icon -->
                <div class="relative inline-block mb-4">
                    <div class="absolute inset-0 bg-white/20 rounded-full pulse-ring"></div>
                    <div class="relative w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center float-animation">
                        <i class="bi bi-gear-wide-connected text-white text-4xl"></i>
                    </div>
                </div>
                
                <!-- Title -->
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Coming Soon</h1>
                <p class="text-primary-100 text-sm">Fitur ini sedang dalam pengembangan</p>
            </div>
            
            <!-- Body -->
            <div class="px-8 py-8">
                <!-- Menu Name Badge -->
                <div class="flex justify-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 border border-primary-200 rounded-full">
                        <i class="bi bi-layers text-primary-600"></i>
                        <span class="text-primary-700 font-semibold text-sm">{{ $menu }}</span>
                    </div>
                </div>
                
                <!-- Message -->
                <div class="text-center mb-8">
                    <p class="text-gray-600 leading-relaxed">
                        Tim kami sedang bekerja keras untuk menghadirkan fitur 
                        <span class="font-semibold text-gray-800">{{ $menu }}</span> 
                        yang lebih baik untuk Anda.
                    </p>
                </div>
                
                <!-- Progress Indicator -->
                <div class="bg-gray-50 rounded-2xl p-5 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Progress Pengembangan</span>
                        <span class="text-sm font-bold text-primary-600">75%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2.5 rounded-full relative" style="width: 75%">
                            <div class="absolute inset-0 shimmer"></div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Estimasi: Segera hadir</p>
                </div>
                
                <!-- Features Coming -->
                <div class="space-y-3 mb-8">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Yang akan hadir:</p>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-check text-primary-600 text-xs"></i>
                        </div>
                        <span>Antarmuka yang lebih modern & intuitif</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-check text-primary-600 text-xs"></i>
                        </div>
                        <span>Fitur lengkap sesuai kebutuhan</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-check text-primary-600 text-xs"></i>
                        </div>
                        <span>Performa optimal & responsif</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('analisis-donatur') }}" wire:navigate
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40">
                        <i class="bi bi-house-door"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                    <a href="/admin" 
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all border border-gray-200">
                        <i class="bi bi-grid"></i>
                        <span>Admin Panel</span>
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                    <i class="bi bi-heart-pulse-fill text-primary-500"></i>
                    <span>Abbarat Management System</span>
                </div>
            </div>
        </div>
        
        <!-- Bottom Info -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500">
                Ada pertanyaan? Hubungi 
                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Tim Support</a>
            </p>
        </div>
    </div>
</div>

</x-layouts.app>
