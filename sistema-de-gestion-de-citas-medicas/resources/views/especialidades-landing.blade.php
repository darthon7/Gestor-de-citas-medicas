<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explora el catálogo completo de especialidades médicas de Vida+. Filtra, compara y encuentra especialistas certificados.">
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

        /* Card hover lift & border */
        .spec-card {
            transition: all 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .spec-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(30, 142, 90, 0.10);
            border-color: rgba(30, 142, 90, 0.35);
        }

        /* Featured card hover */
        .feat-card {
            transition: all 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .feat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(30, 142, 90, 0.12);
        }

        /* Fade-in animation for cards */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeInUp {
            animation: fadeInUp 0.35s ease-out both;
        }
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
         HERO CON DOCTOR GOGO A LA DERECHA
    ============================================================ -->
    <section class="w-full relative overflow-hidden px-4 sm:px-10 lg:px-16 pt-10 sm:pt-14 pb-0"
             style="background-image: linear-gradient(135deg, #0E2218 0%, #12402A 55%, #1E8E5A 100%);">

        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-6 lg:gap-8 min-h-[420px] lg:min-h-[460px]">
            
            <!-- Columna Izquierda: Textos y Buscador (Buscador intacto) -->
            <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left gap-4 sm:gap-5 py-6 sm:py-10 max-w-2xl z-10">
                


                <h1 class="text-3xl sm:text-5xl font-bold font-funnel text-white leading-tight">
                    Encuentra al especialista<br class="hidden sm:inline"> perfecto para ti
                </h1>

                <p class="text-sm sm:text-base text-[#C4D6C8] leading-relaxed max-w-lg">
                    Explora el catálogo completo de especialidades de Vida+. Filtra, compara y reserva tu cita en segundos.
                </p>

                <!-- Buscador (Diseño y estructura original intacta) -->
                <div class="w-full max-w-xl flex items-center gap-2 bg-white rounded-full shadow-2xl px-4 py-1.5">
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

                <!-- Info rápida de disponibilidad -->
                <div class="flex items-center gap-4 text-xs text-[#C4D6C8]/80 pt-1">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#72D350]"></span>
                        Atención médica de alta calidad y especialistas certificados
                    </span>
                </div>
            </div>

            <!-- Columna Derecha: Imagen de Doctor (gogo.png) - Aumentada de tamaño -->
            <div class="w-full lg:w-auto shrink-0 flex justify-center lg:justify-end self-end z-0 mt-2 lg:mt-0">
                <div class="relative max-w-[360px] sm:max-w-[460px] lg:max-w-[540px] xl:max-w-[580px] flex items-end">
                    <img
                        src="{{ asset('assets/gogo.png') }}"
                        alt="Especialista Médico Vida+"
                        class="w-auto max-h-[360px] sm:max-h-[440px] lg:max-h-[500px] xl:max-h-[540px] object-contain object-bottom drop-shadow-2xl pointer-events-none select-none -mb-1"
                    >
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CATÁLOGO COMPLETO
    ============================================================ -->
    <section id="catalogo" class="w-full bg-brand-bg px-4 sm:px-10 lg:px-14 py-12">

        <div class="max-w-7xl mx-auto">
            
            <!-- Encabezado de Sección -->
            <div class="mb-8">
                <p class="text-xs font-bold text-brand-emerald tracking-[2px] uppercase mb-1">CATÁLOGO COMPLETO</p>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Explora todas las especialidades</h2>
                <p class="text-sm text-brand-muted mt-1" id="results-count">
                    Mostrando especialidades con médicos activos
                </p>
            </div>

            @if($especialidades->count() > 0)
                <!-- ============================================================
                     VISTA CUADRÍCULA DE ESPECIALIDADES
                ============================================================ -->
                <div id="cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($especialidades as $index => $esp)
                        @php
                            $cnt = $esp->doctores_count ?? ($esp->doctores ? $esp->doctores->count() : 0);
                            $hasDoctors = $cnt > 0;
                        @endphp

                        <div class="spec-card card-item flex flex-col justify-between p-5 bg-white rounded-2xl border border-brand-border/80 shadow-xs animate-fadeInUp group
                                    {{ !$hasDoctors ? 'hidden-initially' : '' }}"
                             style="{{ !$hasDoctors ? 'display: none;' : '' }} animation-delay: {{ min($index * 40, 400) }}ms;"
                             data-nombre="{{ strtolower($esp->nombre) }}"
                             data-desc="{{ strtolower($esp->descripcion ?? '') }}"
                             data-has-doctors="{{ $hasDoctors ? '1' : '0' }}">

                            <div>
                                <!-- Encabezado de Tarjeta: Estado de Médicos -->
                                <div class="flex items-center justify-end mb-2.5">
                                    @if($hasDoctors)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $cnt }} {{ $cnt === 1 ? 'médico' : 'médicos' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-50 text-zinc-500 border border-zinc-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>
                                            Próximamente
                                        </span>
                                    @endif
                                </div>

                                <!-- Nombre de la Especialidad -->
                                <h3 class="text-lg font-bold font-funnel text-brand-heading leading-snug group-hover:text-brand-emerald transition-colors">
                                    {{ $esp->nombre }}
                                </h3>

                                <!-- Descripción -->
                                <p class="text-xs text-brand-muted leading-relaxed mt-2 line-clamp-3">
                                    {{ $esp->descripcion ?: 'Atención médica especializada, diagnóstico clínico y seguimiento para tu bienestar integral.' }}
                                </p>
                            </div>

                            <!-- Pie de Tarjeta: Información -->
                            <div class="mt-4 pt-3 border-t border-brand-border/60 text-xs text-brand-subtle">
                                <span class="text-[11.5px] font-medium text-brand-muted">
                                    {{ $hasDoctors ? 'Atención disponible' : 'En incorporación' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Sin resultados de búsqueda -->
                <div id="no-results" class="hidden text-center py-16 bg-white rounded-2xl border border-brand-border">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-sectionBg flex items-center justify-center text-brand-emerald">
                        <i data-lucide="search-x" class="w-7 h-7"></i>
                    </div>
                    <p class="text-lg font-bold text-brand-heading">No encontramos resultados</p>
                    <p class="text-xs sm:text-sm text-brand-muted mt-1 max-w-sm mx-auto">
                        Intenta con otro término o borra el texto del buscador para ver el catálogo completo.
                    </p>
                    <button
                        type="button"
                        onclick="limpiarBuscador()"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-brand-emerald text-white text-xs font-semibold rounded-full hover:bg-emerald-700 transition-colors"
                    >
                        Ver todas las especialidades
                    </button>
                </div>

            @else
                <!-- Estado vacío -->
                <div class="text-center py-20 bg-white rounded-2xl border border-brand-border">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-brand-sectionBg flex items-center justify-center text-brand-emerald">
                        <i data-lucide="stethoscope" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold text-brand-heading mb-2">Catálogo en construcción</h3>
                    <p class="text-sm text-brand-muted max-w-md mx-auto">
                        Estamos incorporando nuestras especialidades médicas. Vuelve pronto o contáctanos para más información.
                    </p>
                    <a href="{{ route('landing') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-2.5 bg-brand-emerald text-white font-semibold rounded-full hover:bg-emerald-700 transition-colors text-xs">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Volver al inicio
                    </a>
                </div>
            @endif
        </div>
    </section>



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
    @include('components.landing-footer')

    <!-- ============================================================
         SCRIPTS
    ============================================================ -->
    <script>
        if (window.lucide) lucide.createIcons();

        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu    = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
        }

        function aplicarFiltros() {
            const searchInput = document.getElementById('search-input');
            const query       = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const cards       = document.querySelectorAll('.card-item');
            const noRes       = document.getElementById('no-results');
            const grid        = document.getElementById('cards-grid');
            const resultsCount= document.getElementById('results-count');

            const isSearching = query.length > 0;
            let visibleCount  = 0;

            cards.forEach(card => {
                const nombre    = card.dataset.nombre || '';
                const desc      = card.dataset.desc || '';
                const hasDoctor = card.dataset.hasDoctors === '1';

                // 1. Coincidencia de texto
                const textMatch = !isSearching || nombre.includes(query) || desc.includes(query);

                // 2. Regla de visualización de médicos:
                // - Si el usuario BUSCA texto: se buscan todas (incluso las de 0 médicos)
                // - Si el usuario NO busca texto: solo se muestran las que SÍ tienen médicos (hasDoctor === true)
                const doctorMatch = isSearching ? true : hasDoctor;

                const match = textMatch && doctorMatch;
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            // Visibilidad de contenedores
            if (noRes) noRes.classList.toggle('hidden', visibleCount > 0);
            if (grid)  grid.classList.toggle('hidden', visibleCount === 0);

            // Contador de resultados
            if (resultsCount) {
                if (isSearching) {
                    resultsCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'especialidad encontrada' : 'especialidades encontradas'} para "${query}"`;
                } else {
                    resultsCount.textContent = `Mostrando ${visibleCount} especialidades con médicos activos`;
                }
            }
        }

        function filtrarTarjetas(query) {
            aplicarFiltros();
        }

        function limpiarBuscador() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
            }
            aplicarFiltros();
        }

        // Ejecutar filtro inicial al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            aplicarFiltros();
        });
    </script>
</body>
</html>
