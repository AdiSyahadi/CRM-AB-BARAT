<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Analisis Donatur</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ECFDF5', 100: '#D1FAE5', 200: '#A7F3D0', 300: '#6EE7B7',
                            400: '#34D399', 500: '#10B981', 600: '#059669', 700: '#047857',
                            800: '#065F46', 900: '#064E3B'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .blob-1 {
            position: absolute;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(52, 211, 153, 0.2));
            border-radius: 50%;
            filter: blur(80px);
            top: -200px;
            right: -200px;
            animation: blob-float 8s ease-in-out infinite;
        }
        .blob-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, rgba(52, 211, 153, 0.2), rgba(110, 231, 183, 0.15));
            border-radius: 50%;
            filter: blur(80px);
            bottom: -150px;
            left: -150px;
            animation: blob-float 10s ease-in-out infinite reverse;
        }
        @keyframes blob-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.05); }
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(16, 185, 129, 0.1);
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.15), 0 0 80px rgba(16, 185, 129, 0.1), 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
        .logo-card {
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2), 0 0 30px rgba(16, 185, 129, 0.1);
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-primary-50 via-primary-100/50 to-primary-100 flex items-center justify-center p-2 sm:p-4 relative">
    <!-- Animated Blobs -->
    <div class="blob-1 hidden sm:block"></div>
    <div class="blob-2 hidden sm:block"></div>
    
    <!-- Login Card -->
    <div class="glass-card w-full max-w-xs sm:max-w-sm rounded-xl sm:rounded-2xl p-4 sm:p-6 relative z-10">
        <!-- Logo/Header -->
        <div class="text-center mb-3 sm:mb-5">
            <div class="logo-card w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-2 sm:mb-3 rounded-lg sm:rounded-xl p-1.5 sm:p-2 flex items-center justify-center">
                <img src="https://lazalbahjah.org/wp-content/uploads/2024/03/11.png" 
                     alt="LAZ Al Bahjah" 
                     class="w-full h-full object-contain">
            </div>
            <h1 class="text-base sm:text-lg font-bold text-gray-800">SATU DATA</h1>
            <p class="text-gray-500 text-[10px] sm:text-xs">LAZ AL BAHJAH BARAT</p>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2 text-red-600">
                <i class="bi bi-exclamation-circle text-sm"></i>
                <span class="text-xs font-medium">{{ $errors->first() }}</span>
            </div>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.submit') }}" class="space-y-2 sm:space-y-3">
            @csrf
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-[10px] sm:text-xs font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="bi bi-envelope text-xs sm:text-sm"></i>
                    </span>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full pl-8 sm:pl-9 pr-3 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                           placeholder="nama@email.com"
                           required 
                           autofocus>
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-[10px] sm:text-xs font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="bi bi-lock text-xs sm:text-sm"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full pl-8 sm:pl-9 pr-9 sm:pr-10 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                           placeholder="••••••••"
                           required>
                    <button type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            aria-label="Tampilkan password">
                        <i class="bi bi-eye text-xs sm:text-sm" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer">
                    <input type="checkbox" 
                           name="remember" 
                           {{ old('remember') ? 'checked' : '' }}
                           class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-primary-500 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-[10px] sm:text-xs text-gray-600">Ingat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-1.5 sm:py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all flex items-center justify-center gap-1.5 sm:gap-2">
                <i class="bi bi-box-arrow-in-right text-xs sm:text-sm"></i>
                <span>Login</span>
            </button>
        </form>

        <!-- Divider -->
        <div class="my-2 sm:my-3 flex items-center gap-2 sm:gap-3">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-[9px] sm:text-[10px] text-gray-400">atau</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <!-- Admin Panel Link -->
        <a href="/admin/login" 
           class="w-full py-1.5 sm:py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs sm:text-sm font-medium rounded-lg transition-all flex items-center justify-center gap-1.5 sm:gap-2">
            <i class="bi bi-shield-lock text-xs sm:text-sm"></i>
            <span>Admin Panel (Filament)</span>
        </a>
        
        <!-- Footer inside card -->
        <div class="text-center text-[9px] sm:text-[10px] text-gray-400 mt-2 sm:mt-4">
            &copy; {{ date('Y') }} LAZ Al Bahjah
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
