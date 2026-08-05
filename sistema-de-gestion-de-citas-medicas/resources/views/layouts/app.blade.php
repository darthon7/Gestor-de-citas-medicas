<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agenda Médica - @yield('titulo', 'Gestión de Citas')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme & App Styles (Vite con fallback inteligente a asset()) -->
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/bootstrap-theme.css') }}">
        <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    @endif
    @yield('styles')

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- Sidebar Component -->
        @include('components.sidebar')

        <!-- Main Content Area -->
        <div class="main-content-wrapper p-4 overflow-auto">
            @include('components.flash-message')
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
    @yield('scripts')
</body>
</html>
