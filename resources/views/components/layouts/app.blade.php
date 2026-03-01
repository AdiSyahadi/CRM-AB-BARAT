{{--
    Shared App Layout Component
    Usage: <x-layouts.app :active="'dashboard'" title="Page Title">
    
    Supported props:
    - active: sidebar active menu key (dashboard, donatur, performa-cs, monitor-cs, laporan-perolehan)
    - title: page <title> text
    - bodyClass: extra classes on <body> (default: bg-gray-50 min-h-screen)
    - xData: Alpine x-data attribute for <body>
    - xCloak: whether to add x-cloak (default: true)
    - chartjs: whether to include Chart.js CDN (default: false)
--}}

@props([
    'active' => 'dashboard',
    'title' => 'Abbarat Dashboard',
    'bodyClass' => 'bg-gray-50 min-h-screen',
    'xData' => '',
    'xCloak' => true,
    'chartjs' => false,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @if($chartjs)
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif
    
    <!-- Tailwind Config (shared across all pages) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ECFDF5',
                            100: '#D1FAE5',
                            200: '#A7F3D0',
                            300: '#6EE7B7',
                            400: '#34D399',
                            500: '#10B981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065F46',
                            900: '#064E3B',
                        }
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Shared Styles -->
    <link rel="stylesheet" href="{{ asset('css/shared.css') }}">

    <!-- Shared Scripts -->
    <script src="{{ asset('js/shared.js') }}"></script>
    
    <!-- Livewire Styles (for wire:navigate SPA) -->
    @livewireStyles
    
    <!-- Page-specific Styles -->
    @stack('styles')
    
    <!-- Page-specific Head Scripts -->
    @stack('head-scripts')
</head>
<body class="{{ $bodyClass }}"
      @if($xData) x-data="{{ $xData }}" @endif
      @if($xCloak) x-cloak @endif>

    {{-- Page-specific content before sidebar (e.g. loading modals, overlays) --}}
    @stack('before-sidebar')

    {{-- Sidebar Component --}}
    <x-sidebar :active="$active" />

    {{-- Main Content Area --}}
    <main class="lg:ml-72 min-h-screen">
        {{ $slot }}
    </main>

    {{-- Page-specific content after main (e.g. modals) --}}
    @stack('after-main')

    {{-- Page-specific Scripts --}}
    @stack('scripts')

    <!-- Livewire Scripts (for wire:navigate SPA) -->
    @livewireScripts
</body>
</html>
