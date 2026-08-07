<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediAdmin - @yield('titulo', 'Autenticación')</title>

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
                        "secondary": "#006a60",
                        "danger": "#E76F51",
                        "background": "#eef5f9",
                        "surface": "#FFFFFF",
                        "border": "#E2E8F0",
                        "text-primary": "#1A1A2E",
                        "text-secondary": "#4A5568",
                        "text-muted": "#A0AEC0"
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #e6f2f8 0%, #f7f9fc 100%);
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
    @yield('styles')
</head>
<body class="min-h-screen flex flex-col justify-center items-center p-4 antialiased">
    <div class="w-full max-w-md">
        @include('components.flash-message')
        @yield('content')
    </div>
</body>
</html>
