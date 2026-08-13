<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vida+ Agenda Médica - @yield('titulo', 'Autenticación')</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Sans:wght@300..800&family=Geist:wght@100..900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1E8E5A",
                        "primary-dark": "#10231A",
                        "primary-light": "#72D350",
                        "secondary": "#1E8E5A",
                        "secondary-light": "#E4F5E9",
                        "danger": "#E76F51",
                        "background": "#F6FBF4",
                        "surface": "#FFFFFF",
                        "border": "#E3EDE5",
                        "text-primary": "#10231A",
                        "text-secondary": "#5B6B62",
                        "text-muted": "#8FA198",
                        brand: {
                            dark:      '#0E2218',
                            emerald:   '#1E8E5A',
                            light:     '#72D350',
                            bg:        '#F6FBF4',
                            heading:   '#10231A',
                            muted:     '#5B6B62',
                            cardBg:    '#E4F5E9',
                            sectionBg: '#F0F7F1',
                            border:    '#E3EDE5',
                            subtle:    '#8FA198'
                        }
                    },
                    fontFamily: {
                        sans:   ['Inter', 'system-ui', 'sans-serif'],
                        funnel: ['Funnel Sans', 'sans-serif'],
                        geist:  ['Geist', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F6FBF4;
            background-image:
                radial-gradient(55rem 55rem at 108% -12%, rgba(30, 142, 90, 0.10), transparent 60%),
                radial-gradient(45rem 45rem at -8% 112%, rgba(114, 211, 80, 0.12), transparent 60%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56'%3E%3Cpath d='M26 22h4v4h4v4h-4v4h-4v-4h-4v-4h4z' fill='%231E8E5A' fill-opacity='0.05'/%3E%3C/svg%3E");
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .dropdown-content {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px) scale(0.98);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .dropdown-parent:hover .dropdown-content,
        .dropdown-parent:focus-within .dropdown-content,
        .dropdown-content.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        .dropdown-parent:hover .chevron-ico,
        .dropdown-parent:focus-within .chevron-ico {
            transform: rotate(180deg);
        }
        .navbar-glass {
            background-color: rgba(246, 251, 244, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        /* Línea de vida (ECG): trazo que se dibuja una sola vez al cargar */
        .ecg-line path {
            stroke-dasharray: 220;
            stroke-dashoffset: 0;
        }
        @media (prefers-reduced-motion: no-preference) {
            .ecg-line path {
                animation: ecg-draw 1.6s ease-out both;
            }
        }
        @keyframes ecg-draw {
            from { stroke-dashoffset: 220; }
            to { stroke-dashoffset: 0; }
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    @yield('styles')
</head>
<body class="min-h-screen flex flex-col justify-between items-center antialiased selection:bg-brand-emerald selection:text-white">
    <!-- Navbar landing visible en auth -->
    @include('components.landing-navbar')

    <main class="w-full flex-1 flex flex-col items-center justify-center p-4 my-8">
        <div class="w-full @yield('ancho', 'max-w-md') flex flex-col items-center">
            @include('components.flash-message')
            @yield('content')
        </div>
    </main>

    <!-- Scripts de Navbar -->
    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu    = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }
    </script>
    @yield('scripts')
</body>
</html>


