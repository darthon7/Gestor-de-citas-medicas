<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediAdmin - @yield('titulo', 'Gestión de Citas Médicas')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#005275",
                        "primary-dark": "#0F4C6B",
                        "primary-light": "#A8D5E2",
                        "primary-container": "#1b6b93",
                        "on-primary": "#ffffff",
                        "secondary": "#006a60",
                        "secondary-light": "#B5E8D5",
                        "tertiary": "#885c00",
                        "tertiary-fixed-dim": "#ffba42",
                        "danger": "#E76F51",
                        "danger-light": "#FADED4",
                        "error": "#ba1a1a",
                        "warning-gold": "#E9A319",
                        "background": "#f7f9fc",
                        "surface": "#FFFFFF",
                        "border": "#E2E8F0",
                        "text-primary": "#1A1A2E",
                        "text-secondary": "#4A5568",
                        "text-muted": "#A0AEC0",
                        "on-surface": "#191c1e",
                        "on-surface-variant": "#40484e"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sidebar-width": "260px"
                    },
                    "fontFamily": {
                        "sans": ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Inter', sans-serif;
            color: #191c1e;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .sidebar-item-active {
            color: #ffffff !important;
            background-color: rgba(27, 107, 147, 0.3) !important;
            border-left: 4px solid #B5E8D5 !important;
        }
        .card-shadow {
            box-shadow: 0 2px 12px rgba(27, 107, 147, 0.08);
        }
        .card-shadow-hover:hover {
            box-shadow: 0 4px 20px rgba(27, 107, 147, 0.14);
        }
    </style>
    @yield('styles')
</head>
<body class="bg-background text-on-surface antialiased min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        @include('components.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto pl-0 md:pl-[260px]">
            <!-- Top Header Bar -->
            <header class="h-16 bg-surface border-b border-border px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4 flex-1 max-w-lg">
                    <span class="text-sm font-medium text-text-secondary hidden sm:inline-block">Hospital Central</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-text-muted hidden md:inline">{{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM, YYYY') }}</span>
                    @auth
                    <a href="{{ route('perfil') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-text-primary leading-tight">{{ Auth::user()->nombre }}</p>
                            <p class="text-xs text-text-secondary capitalize">{{ Auth::user()->rol }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            {{ strtoupper(substr(Auth::user()->nombre ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                    @endauth
                </div>
            </header>

            <main class="flex-1 p-6 md:p-8">
                @include('components.flash-message')
                @yield('content')
            </main>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
