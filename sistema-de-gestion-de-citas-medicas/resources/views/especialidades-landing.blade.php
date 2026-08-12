<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explora el catálogo completo de especialidades médicas de Vida+. Filtra, compara y reserva tu cita en segundos.">
    <title>Especialidades Médicas — Vida+ Agenda Médica</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Sans:wght@300..800&family=Geist:wght@100..900&family=Inter:wght@300..900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Glassmorphic Navbar */
        .navbar-glass {
            background-color: rgba(246, 251, 244, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Card hover lift */
        .spec-card {
            transition: transform 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                        box-shadow 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .spec-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(30, 142, 90, 0.13);
        }

        /* Featured card hover */
        .feat-card {
            transition: transform 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                        box-shadow 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .feat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(30, 142, 90, 0.16);
        }

        /* Fade-in animation for cards */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeInUp {
            animation: fadeInUp 0.45s ease-out both;
        }

        /* Icon tile color classes */
        .tile-green  { background-color: #E4F5E9; }
        .tile-teal   { background-color: #E0F2EA; }
        .tile-lime   { background-color: #EDF5DF; }
        .tile-amber  { background-color: #FDF6E0; }
        .tile-blue   { background-color: #E0EEF9; }
        .tile-pink   { background-color: #FDE8E8; }
        .tile-purple { background-color: #EDE8FD; }
        .tile-sky    { background-color: #E0F3FD; }
    </style>
</head>
<body class="bg-brand-bg text-brand-heading font-sans antialiased overflow-x-hidden">

    <!-- ============================================================
         BARRA SUPERIOR DE ANUNCIO
    ============================================================ -->
    @include('components.announcement-banner')

    <!-- ============================================================
         NAVBAR
    ============================================================ -->
    @include('components.landing-navbar')

    <!-- ============================================================
         HERO
    ============================================================ -->
    <section class="w-full min-h-[320px] flex flex-col gap-5 px-4 sm:px-10 py-16 justify-center items-center"
             style="background-image: linear-gradient(0deg, #0E2218 0%, #12402A 55%, #1E8E5A 100%);">

        <!-- Eyebrow -->
        <div class="flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/20 bg-white/10">
            <i data-lucide="heart-pulse" class="w-3.5 h-3.5 text-[#72D350]"></i>
            <span class="text-xs font-semibold text-white whitespace-nowrap">
                {{ $totalEspecialidades }}+ especialidades &middot; {{ $totalDoctores }}+ médicos verificados
            </span>
        </div>

        <h1 class="text-4xl sm:text-5xl font-bold font-funnel text-white text-center leading-tight max-w-2xl">
            Encuentra al especialista<br>perfecto para ti
        </h1>

        <p class="text-sm sm:text-base text-[#C4D6C8] text-center max-w-lg leading-relaxed">
            Explora el catálogo completo de especialidades de Vida+. Filtra, compara y reserva tu cita en segundos.
        </p>

        <!-- Buscador -->
        <div class="w-full max-w-xl flex items-center gap-2 bg-white rounded-full shadow-xl px-4 py-1.5">
            <i data-lucide="search" class="w-5 h-5 text-brand-subtle shrink-0"></i>
            <input
                id="search-input"
                type="text"
                placeholder="Busca por especialidad, síntoma o médico"
                class="flex-1 text-sm text-brand-heading placeholder-brand-subtle bg-transparent py-2 focus:outline-none"
                oninput="filtrarTarjetas(this.value)"
            >
            <button class="px-4 py-2 bg-brand-emerald text-white text-xs font-bold rounded-full whitespace-nowrap hover:bg-emerald-700 transition-colors">
                Buscar
            </button>
        </div>
    </section>

    <!-- ============================================================
         CATÁLOGO — Explora todas las especialidades (máx 12 del backend)
    ============================================================ -->
    <section id="catalogo" class="w-full bg-brand-bg px-4 sm:px-10 lg:px-14 py-14">

        <div class="text-center mb-10">
            <p class="text-xs font-bold text-brand-emerald tracking-[2px] uppercase mb-2">CATÁLOGO COMPLETO</p>
            <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Explora todas las especialidades</h2>
        </div>

        @if($especialidades->count() > 0)
            @php
                $tileClasses = [
                    'tile-green','tile-teal','tile-lime','tile-amber',
                    'tile-blue','tile-pink','tile-purple','tile-sky',
                    'tile-green','tile-teal','tile-lime','tile-amber',
                ];
                $iconNames = [
                    'heart','brain','baby','apple','activity','sparkles',
                    'flower-2','eye','bone','thermometer','stethoscope','shield-check',
                ];
            @endphp

            <div id="cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($especialidades as $index => $esp)
                @php $cnt = $esp->doctores_count ?? $esp->doctores->count(); @endphp
                <div class="spec-card card-item flex flex-col gap-2.5 p-[18px] bg-white rounded-[18px] border border-brand-border/80 animate-fadeInUp"
                     style="animation-delay: {{ $index * 60 }}ms;"
                     data-nombre="{{ strtolower($esp->nombre) }}">

                    <!-- Ícono + Rating -->
                    <div class="flex items-center justify-between">
                        <div class="w-11 h-11 flex items-center justify-center rounded-[13px] {{ $tileClasses[$index % count($tileClasses)] }}">
                            <i data-lucide="{{ $iconNames[$index % count($iconNames)] }}" class="w-5 h-5 text-brand-emerald"></i>
                        </div>
                        <div class="flex items-center gap-1 px-2.5 py-1 bg-brand-sectionBg rounded-full">
                            <i data-lucide="star" class="w-3 h-3 text-yellow-500 fill-yellow-400"></i>
                            <span class="text-xs font-bold text-brand-heading font-geist">4.9</span>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <h3 class="text-[18px] font-bold font-funnel text-brand-heading leading-snug">{{ $esp->nombre }}</h3>

                    <!-- Descripción -->
                    <p class="text-[13px] text-brand-muted leading-[20px] flex-1 line-clamp-2">
                        {{ $esp->descripcion ?: 'Atención médica especializada y de calidad para tu bienestar.' }}
                    </p>

                    <!-- Pie -->
                    <div class="flex items-center justify-between mt-auto pt-1">
                        <span class="text-[12.5px] text-brand-subtle">
                            {{ $cnt }} {{ $cnt === 1 ? 'especialista' : 'especialistas' }}
                        </span>
                        <a href="{{ route('registro') }}"
                           class="px-3.5 py-1.5 bg-brand-emerald text-white text-xs font-bold rounded-full hover:bg-emerald-700 transition-colors">
                            Reservar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Sin resultados de búsqueda -->
            <div id="no-results" class="hidden text-center py-20">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-brand-sectionBg flex items-center justify-center">
                    <i data-lucide="search-x" class="w-8 h-8 text-brand-subtle"></i>
                </div>
                <p class="text-lg font-semibold text-brand-heading">Sin resultados</p>
                <p class="text-sm text-brand-muted mt-1">No encontramos especialidades con ese nombre.</p>
            </div>

        @else
            <!-- Estado vacío -->
            <div class="text-center py-24">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-brand-sectionBg flex items-center justify-center">
                    <i data-lucide="stethoscope" class="w-10 h-10 text-brand-subtle"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-heading mb-2">Catálogo en construcción</h3>
                <p class="text-sm text-brand-muted max-w-md mx-auto">
                    Estamos incorporando nuestras especialidades médicas. Vuelve pronto o contáctanos para más información.
                </p>
                <a href="{{ route('landing') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-brand-emerald text-white font-semibold rounded-full hover:bg-emerald-700 transition-colors text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Volver al inicio
                </a>
            </div>
        @endif
    </section>

    <!-- ============================================================
         DESTACADAS — Popular entre los pacientes
    ============================================================ -->
    @if($destacadas->count() > 0)
    <section class="w-full bg-brand-bg px-4 sm:px-10 lg:px-14 pt-6 pb-16 border-t border-brand-border/40">
        <div class="flex items-end justify-between mb-7">
            <div>
                <p class="text-xs font-bold text-brand-emerald tracking-[2px] uppercase mb-1">MÁS DEMANDADAS</p>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Popular entre los pacientes</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $featBg = ['#E4F5E9', '#FDF6E0', '#E0F2EA', '#EDE8FD', '#E0EEF9', '#FDE8E8'];
                $featIcons = ['heart', 'apple', 'brain', 'flower-2', 'activity', 'baby'];
            @endphp
            @foreach($destacadas as $esp)
            @php
                $bgColor  = $featBg[$loop->index % count($featBg)];
                $iconName = $featIcons[$loop->index % count($featIcons)];
                $doctCount = $esp->doctores_count ?? $esp->doctores->count();
            @endphp
            <div class="feat-card flex items-center gap-5 p-6 rounded-[26px]" style="background-color: {{ $bgColor }};">
                <div class="w-[76px] h-[76px] shrink-0 flex items-center justify-center bg-white rounded-[22px] shadow-sm">
                    <i data-lucide="{{ $iconName }}" class="w-9 h-9 text-brand-emerald"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-xl font-bold font-funnel text-brand-heading truncate">{{ $esp->nombre }}</h3>
                    <p class="text-sm text-brand-muted mt-1 leading-snug line-clamp-2">
                        {{ $esp->descripcion ?: 'Atención especializada de alta calidad.' }}
                    </p>
                    <p class="text-sm font-semibold text-brand-emerald mt-2">
                        &#9733; 4.9 &middot; {{ $doctCount }} especialistas
                    </p>
                </div>
                <a href="{{ route('registro') }}"
                   class="shrink-0 flex items-center gap-2 px-4 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand-emerald transition-colors whitespace-nowrap">
                    Reservar
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- ============================================================
         CTA — Reserva tu cita
    ============================================================ -->
    <section class="w-full px-4 sm:px-10 py-16 bg-gradient-to-br from-[#0E2218] to-[#1E8E5A] text-center">
        <p class="text-xs font-bold text-brand-light tracking-[2px] uppercase mb-3">EMPIEZA HOY</p>
        <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-white mb-4 max-w-xl mx-auto">
            ¿Listo para agendar tu cita?
        </h2>
        <p class="text-sm text-[#C4D6C8] max-w-md mx-auto mb-8">
            Regístrate gratis y agenda con cualquier especialista en menos de 2 minutos.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('registro') }}"
               class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full bg-brand-light text-brand-dark font-bold text-sm hover:brightness-105 transition-all shadow-lg">
                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                Crear cuenta gratis
            </a>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full border border-white/30 text-white font-semibold text-sm hover:bg-white/10 transition-all">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                Ya tengo cuenta
            </a>
        </div>
    </section>

    <!-- ============================================================
         FOOTER
    ============================================================ -->
    <footer class="w-full bg-[#0A1A0F] text-zinc-400 text-xs px-4 sm:px-10 py-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-emerald-950/80 border border-emerald-800/60 p-1.5 flex items-center justify-center transition-transform group-hover:scale-105">
                    <svg viewBox="0 0 38.717 33.301" class="w-6 h-6 overflow-visible">
                        <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                        <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
                    </svg>
                </div>
                <span class="text-xl font-bold font-funnel tracking-tight text-white">
                    Vida<span class="text-brand-emerald">+</span>
                </span>
            </a>
            <p>&copy; {{ date('Y') }} Sistema de Gestión de Citas Médicas. Todos los derechos reservados.</p>
            <div class="flex items-center gap-5">
                <a href="#" class="hover:text-zinc-300 transition-colors">Privacidad</a>
                <a href="#" class="hover:text-zinc-300 transition-colors">Términos</a>
                <a href="{{ route('landing') }}#contacto" class="hover:text-zinc-300 transition-colors">Soporte</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        if (window.lucide) lucide.createIcons();

        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu    = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }

        function filtrarTarjetas(query) {
            const q     = query.trim().toLowerCase();
            const cards = document.querySelectorAll('.card-item');
            const grid  = document.getElementById('cards-grid');
            const noRes = document.getElementById('no-results');
            let visible = 0;

            cards.forEach(card => {
                const nombre = card.dataset.nombre || '';
                const match  = nombre.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (noRes) noRes.classList.toggle('hidden', visible > 0);
            if (grid)  grid.classList.toggle('hidden', visible === 0);
        }
    </script>
</body>
</html>
