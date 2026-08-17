<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vida+ Agenda Médica — Sistema de Gestión de Citas Médicas. Agenda tu atención médica sin filas ni llamadas, recordatorios inteligentes y recetas digitales.">
    <title>Vida+ Agenda Médica — Gestión de Citas & Salud Digital</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#0E2218',
                            emerald: '#1E8E5A',
                            light: '#72D350',
                            bg: '#F6FBF4',
                            heading: '#10231A',
                            muted: '#5B6B62',
                            cardBg: '#E4F5E9',
                            sectionBg: '#F0F7F1',
                            border: '#E3EDE5',
                            subtle: '#8FA198'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        funnel: ['Funnel Sans', 'sans-serif'],
                        geist: ['Geist', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Sans:wght@300..800&family=Geist:wght@100..900&family=Inter:wght@300..900&family=Plus+Jakarta+Sans:wght@400..800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Modern dropdown & subtle visual animations */
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

        /* Glassmorphic Navbar effect */
        .navbar-glass {
            background-color: rgba(246, 251, 244, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-brand-bg text-brand-heading font-sans antialiased overflow-x-hidden selection:bg-brand-emerald selection:text-white">

    <!-- ============================================================
         1. ANUNCIO SUPERIOR (STICKY / PROMO DESECHABLE)
    ============================================================ -->
    @include('components.announcement-banner')

    <!-- ============================================================
         2. NAVBAR CON MENÚS DROPDOWN CORREGIDOS Y ACCIONES
    ============================================================ -->
    @include('components.landing-navbar')

    <!-- ============================================================
         3. HERO PRINCIPAL CON MOCKUP DE CELULAR (IMAGEN ENTREGADA CON BORDE REDONDO)
    ============================================================ -->
    <section id="inicio" class="relative py-12 lg:py-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Columna Izquierda: Texto y CTAs -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    


                    <!-- Título Principal -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-funnel tracking-tight text-brand-heading leading-[1.1]">
                        Tu salud y tus citas, <br class="hidden sm:inline"/>
                        <span class="text-brand-emerald underline decoration-brand-light/60 decoration-wavy underline-offset-4">a un toque</span> de distancia
                    </h1>

                    <!-- Subtítulo -->
                    <p class="text-lg text-brand-muted font-normal max-w-2xl leading-relaxed">
                        Agenda, consulta y controla tu atención médica sin filas y sin llamadas. Plataforma inteligente diseñada para conectar pacientes y especialistas de forma ágil y transparente.
                    </p>

                    <!-- CTAs de Entrada y App Móvil -->
                    <div class="pt-2 flex flex-col items-stretch w-full sm:w-fit gap-4">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                            <a href="{{ route('login') }}" class="px-7 py-4 rounded-full bg-brand-emerald hover:bg-emerald-700 text-white font-bold text-base shadow-lg shadow-emerald-700/25 transition-all hover:scale-[1.02] flex items-center justify-center gap-2.5">
                                <i data-lucide="stethoscope" class="w-5 h-5"></i>
                                <span>Acceso Personal Médico</span>
                            </a>
                            <a href="{{ route('registro') }}" class="px-7 py-4 rounded-full bg-brand-light hover:bg-lime-400 text-brand-dark font-bold text-base shadow-md transition-all hover:scale-[1.02] flex items-center justify-center gap-2.5">
                                <i data-lucide="user-plus" class="w-5 h-5"></i>
                                <span>Portal Paciente / Registro</span>
                            </a>
                        </div>


                    </div>
                </div>

                <!-- Columna Derecha: Mockup de Celular usando la Imagen del usuario con borde redondo -->
                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    <div class="relative w-[300px] sm:w-[340px]">
                        <!-- Resplandor de fondo decorativo -->
                        <div class="absolute -inset-4 bg-gradient-to-tr from-brand-emerald/30 to-brand-light/40 rounded-[56px] blur-2xl opacity-70"></div>

                        <!-- Marco del Celular -->
                        <div class="relative bg-brand-dark p-3.5 rounded-[46px] shadow-2xl border-4 border-emerald-900/40">
                            
                            <!-- Barra superior del teléfono (Notch/Bocina) -->
                            <div class="w-28 h-4 bg-black rounded-full mx-auto mb-2 flex items-center justify-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-900/80"></div>
                                <div class="w-10 h-1.5 rounded-full bg-zinc-800"></div>
                            </div>

                            <!-- Imagen de la App Móvil entregada por el Usuario con Bordes Redondeados -->
                            <div class="overflow-hidden rounded-[32px] border border-white/10 shadow-inner bg-white">
                                <img src="{{ asset('assets/app-mobile-screen.png') }}" 
                                     alt="App Móvil Agenda Médica" 
                                     class="w-full h-auto object-cover rounded-[32px]">
                            </div>

                            <!-- Botón Home indicador inferior -->
                            <div class="w-32 h-1 bg-zinc-700/80 rounded-full mx-auto mt-3"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         4. BARRA DE ESTADÍSTICAS / MÉTRICAS DE CONFIANZA
    ============================================================ -->
    <section class="py-8 bg-white border-y border-brand-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-brand-border">
                <div class="py-4 md:py-0">
                    <div class="text-4xl lg:text-5xl font-bold font-geist text-brand-emerald">+50,000</div>
                    <div class="text-sm font-medium text-brand-muted mt-1">citas agendadas con éxito</div>
                </div>
                <div class="py-4 md:py-0">
                    <div class="text-4xl lg:text-5xl font-bold font-geist text-brand-emerald">98%</div>
                    <div class="text-sm font-medium text-brand-muted mt-1">pacientes satisfechos</div>
                </div>
                <div class="py-4 md:py-0">
                    <div class="text-4xl lg:text-5xl font-bold font-geist text-brand-emerald">24/7</div>
                    <div class="text-sm font-medium text-brand-muted mt-1">soporte y agendamiento activo</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         5. CARACTERÍSTICAS PRINCIPALES (POR QUÉ VIDA+)
    ============================================================ -->
    <section id="caracteristicas" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-14">
                <span class="text-xs font-bold tracking-widest text-brand-emerald uppercase">POR QUÉ VIDA+</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Cuidado médico, simplificado</h2>
                <p class="text-base text-brand-muted">Diseñada para que cuidar tu salud sea parte natural de tu día, no una carga.</p>
            </div>

            <!-- Fila 1 de Tarjetas de Funciones -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                
                <div class="p-7 rounded-2xl bg-white border-2 border-brand-border/80 hover:border-brand-emerald/50 transition-all hover:shadow-lg space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-cardBg flex items-center justify-center text-brand-emerald">
                        <i data-lucide="calendar-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-funnel text-brand-heading">Reserva en segundos</h3>
                    <p class="text-sm text-brand-muted leading-relaxed">Elige especialista, fecha y hora en menos de un minuto. Sin llamadas ni filas.</p>
                </div>

                <div class="p-7 rounded-2xl bg-white border-2 border-brand-border/80 hover:border-brand-emerald/50 transition-all hover:shadow-lg space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-cardBg flex items-center justify-center text-brand-emerald">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-funnel text-brand-heading">Recordatorios inteligentes</h3>
                    <p class="text-sm text-brand-muted leading-relaxed">Te avisamos antes de cada cita y de tus hábitos de salud diarios.</p>
                </div>

                <div class="p-7 rounded-2xl bg-white border-2 border-brand-border/80 hover:border-brand-emerald/50 transition-all hover:shadow-lg space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-cardBg flex items-center justify-center text-brand-emerald">
                        <i data-lucide="video" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-funnel text-brand-heading">Telemedicina segura</h3>
                    <p class="text-sm text-brand-muted leading-relaxed">Consulta con tu doctor por videollamada, con receta digital incluida.</p>
                </div>

            </div>

            <!-- Fila 2 de Tarjetas con Tono Suave -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="p-6 rounded-2xl bg-brand-sectionBg border border-brand-border space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-200/60 flex items-center justify-center text-brand-emerald">
                        <i data-lucide="file-heart" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-bold font-funnel text-brand-heading">Historial en línea</h4>
                    <p class="text-xs sm:text-sm text-brand-muted leading-relaxed">Tu historial clínico y recetas, siempre a la mano y organizados.</p>
                </div>

                <div class="p-6 rounded-2xl bg-brand-sectionBg border border-brand-border space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-200/60 flex items-center justify-center text-brand-emerald">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-bold font-funnel text-brand-heading">Datos protegidos</h4>
                    <p class="text-xs sm:text-sm text-brand-muted leading-relaxed">Cifrado de extremo a extremo y privacidad médica garantizada.</p>
                </div>

                <div class="p-6 rounded-2xl bg-brand-sectionBg border border-brand-border space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-200/60 flex items-center justify-center text-brand-emerald">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-bold font-funnel text-brand-heading">Tu familia, cubierta</h4>
                    <p class="text-xs sm:text-sm text-brand-muted leading-relaxed">Crea perfiles para tus hijos y adultos mayores desde una misma cuenta.</p>
                </div>

                <div class="p-6 rounded-2xl bg-brand-sectionBg border border-brand-border space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-200/60 flex items-center justify-center text-brand-emerald">
                        <i data-lucide="star" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-bold font-funnel text-brand-heading">Experiencia impecable</h4>
                    <p class="text-xs sm:text-sm text-brand-muted leading-relaxed">Diseñada con médicos para que inviertas menos tiempo en gestionar tu salud.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================
         6. SECCIÓN SHOWCASE / CÓMO FUNCIONA + DESTACADO MÉDICO CON LA IMAGEN DE DR. HOUSE
    ============================================================ -->
    <section class="py-16 lg:py-20 bg-brand-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-brand-dark rounded-3xl p-6 sm:p-10 lg:p-12 text-white shadow-2xl grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- 3 Pasos Informativos -->
                <div class="lg:col-span-7 space-y-8">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-brand-light uppercase">CÓMO FUNCIONA</span>
                        <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-white mt-1">Del malestar a la mejoría en 3 pasos</h2>
                        <p class="text-sm sm:text-base text-zinc-300 mt-2">Olvídate de las llamadas interminables. Vida+ te acompaña en cada etapa de tu atención médica.</p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-brand-light text-brand-dark font-bold flex items-center justify-center shrink-0 text-base">1</div>
                            <div>
                                <h3 class="text-lg font-bold font-funnel text-white">Elige tu especialidad</h3>
                                <p class="text-xs sm:text-sm text-zinc-400">Cardiólogos, nutriólogos, pediatras y más de 30 especialidades certificadas.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-brand-light text-brand-dark font-bold flex items-center justify-center shrink-0 text-base">2</div>
                            <div>
                                <h3 class="text-lg font-bold font-funnel text-white">Reserva en un toque</h3>
                                <p class="text-xs sm:text-sm text-zinc-400">Horarios en tiempo real y confirmación inmediata sin tiempos muertos.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-brand-light text-brand-dark font-bold flex items-center justify-center shrink-0 text-base">3</div>
                            <div>
                                <h3 class="text-lg font-bold font-funnel text-white">Recibe seguimiento</h3>
                                <p class="text-xs sm:text-sm text-zinc-400">Recordatorios, recetas digitales y hábitos personalizados tras cada consulta.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Especialista Destacado (Imagen Dr. House con bordes redondeados) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-2xl p-5 text-brand-heading shadow-xl space-y-4">
                        
                        <!-- Imagen con efecto borde redondo -->
                        <div class="relative overflow-hidden rounded-xl border border-brand-border">
                            <img src="{{ asset('assets/doctor-house.jpg') }}" 
                                 alt="Dr. Gregory House - Especialista Clínico" 
                                 class="w-full h-56 object-cover object-top hover:scale-105 transition-transform duration-500 rounded-xl">
                            <span class="absolute top-3 left-3 bg-brand-emerald text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-md">
                                Especialista Destacado
                            </span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold font-funnel text-brand-heading">Dr. Gregory House</h3>
                                <span class="flex items-center gap-1 text-xs font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                    ★ 4.9
                                </span>
                            </div>
                            <p class="text-xs font-medium text-brand-emerald">Diagnóstico Clínico & Medicina Interna</p>
                            <p class="text-xs text-brand-muted mt-1">Atención presencial y consulta de telemedicina especializada.</p>
                        </div>

                        <div class="pt-2 border-t border-brand-border flex items-center justify-between gap-3">
                            <div class="text-xs font-medium text-brand-muted">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                Horarios disponibles hoy
                            </div>
                            <a href="{{ route('registro') }}" class="px-4 py-2 rounded-full bg-brand-emerald hover:bg-emerald-700 text-white font-semibold text-xs transition-colors">
                                Reservar Cita
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         7. SECCIÓN ESPECIALIDADES
    ============================================================ -->
    <section id="especialidades" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-bold tracking-widest text-brand-emerald uppercase">ESPECIALIDADES</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Especialistas que cuidan de ti</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Cardiología -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="heart" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Cardiología</h3>
                        <p class="text-xs text-brand-muted">Cuidado del corazón y prevención cardiovascular.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

                <!-- Pediatría -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="baby" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Pediatría</h3>
                        <p class="text-xs text-brand-muted">Salud y crecimiento de tus hijos de 0 a 18 años.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

                <!-- Nutrición -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="apple" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Nutrición</h3>
                        <p class="text-xs text-brand-muted">Planes de alimentación adaptados a tu estilo de vida.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

                <!-- Psicología -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="brain" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Psicología</h3>
                        <p class="text-xs text-brand-muted">Apoyo emocional con terapeutas profesionales.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

                <!-- Ginecología -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="flower-2" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Ginecología</h3>
                        <p class="text-xs text-brand-muted">Salud integral femenina y acompañamiento médico.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

                <!-- Dermatología -->
                <div class="p-5 rounded-2xl border-2 border-brand-border hover:border-brand-emerald transition-all hover:shadow-md flex items-center gap-4 bg-white">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cardBg flex items-center justify-center text-brand-emerald shrink-0">
                        <i data-lucide="smile" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold font-funnel text-brand-heading">Dermatología</h3>
                        <p class="text-xs text-brand-muted">Diagnóstico y tratamiento de la salud cutánea.</p>
                        <a href="{{ route('registro') }}" class="inline-flex items-center text-xs font-semibold text-brand-emerald hover:underline pt-1">
                            Reservar →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         8. SECCIÓN HÁBITOS DE SALUD Y BIENESTAR
    ============================================================ -->
    <section id="habitos" class="py-16 lg:py-20 bg-brand-sectionBg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-bold tracking-widest text-brand-emerald uppercase">HÁBITOS SALUDABLES</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Hábitos que transforman tu día</h2>
                <p class="text-sm text-brand-muted">Registra tu progreso y recibe recordatorios para mantenerte motivado.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="p-6 rounded-2xl bg-white border border-brand-border space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">💧</span>
                        <span class="text-xs font-bold text-brand-emerald bg-brand-cardBg px-2.5 py-1 rounded-full">8/8 vasos</span>
                    </div>
                    <h3 class="text-lg font-bold font-funnel text-brand-heading">Hidratación diaria</h3>
                    <div class="w-full bg-zinc-100 rounded-full h-2">
                        <div class="bg-brand-emerald h-2 rounded-full w-full"></div>
                    </div>
                    <p class="text-xs text-brand-muted">Mantente con energía tomando 2L de agua.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-brand-border space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🏃‍♂️</span>
                        <span class="text-xs font-bold text-brand-emerald bg-brand-cardBg px-2.5 py-1 rounded-full">7,500 pas</span>
                    </div>
                    <h3 class="text-lg font-bold font-funnel text-brand-heading">Actividad física</h3>
                    <div class="w-full bg-zinc-100 rounded-full h-2">
                        <div class="bg-brand-emerald h-2 rounded-full w-3/4"></div>
                    </div>
                    <p class="text-xs text-brand-muted">Camina al menos 10,000 pasos al día.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-brand-border space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🧘</span>
                        <span class="text-xs font-bold text-brand-emerald bg-brand-cardBg px-2.5 py-1 rounded-full">5 min</span>
                    </div>
                    <h3 class="text-lg font-bold font-funnel text-brand-heading">Pausas conscientes</h3>
                    <div class="w-full bg-zinc-100 rounded-full h-2">
                        <div class="bg-brand-emerald h-2 rounded-full w-4/5"></div>
                    </div>
                    <p class="text-xs text-brand-muted">Ejercicios de respiración y relajación.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-brand-border space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">😴</span>
                        <span class="text-xs font-bold text-brand-emerald bg-brand-cardBg px-2.5 py-1 rounded-full">7.5 hrs</span>
                    </div>
                    <h3 class="text-lg font-bold font-funnel text-brand-heading">Sueño reparador</h3>
                    <div class="w-full bg-zinc-100 rounded-full h-2">
                        <div class="bg-brand-emerald h-2 rounded-full w-11/12"></div>
                    </div>
                    <p class="text-xs text-brand-muted">Descanso óptimo para recuperar vitalidad.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================
         9. SECCIÓN TESTIMONIOS
    ============================================================ -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-bold tracking-widest text-brand-emerald uppercase">HISTORIAS REALES</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-funnel text-brand-heading">Pacientes que ya cuidan de sí</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="p-7 rounded-2xl border border-brand-border bg-white space-y-4 shadow-sm">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    </div>
                    <p class="text-sm text-brand-muted leading-relaxed">
                        "Agendar citas para mis hijos antes era un caos por las llamadas. Con Vida+ elijo horario al instante y recibo recordatorios en mi celular."
                    </p>
                    <div class="pt-2 border-t border-brand-border/60">
                        <div class="text-sm font-bold text-brand-heading">Laura González</div>
                        <div class="text-xs text-brand-subtle">Paciente de Pediatría</div>
                    </div>
                </div>

                <div class="p-7 rounded-2xl border border-brand-border bg-white space-y-4 shadow-sm">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    </div>
                    <p class="text-sm text-brand-muted leading-relaxed">
                        "Como profesional con poco tiempo, la opción de telemedicina y consulta de recetas digitales ha sido una maravilla total."
                    </p>
                    <div class="pt-2 border-t border-brand-border/60">
                        <div class="text-sm font-bold text-brand-heading">Carlos Mendoza</div>
                        <div class="text-xs text-brand-subtle">Paciente de Cardiología</div>
                    </div>
                </div>

                <div class="p-7 rounded-2xl border border-brand-border bg-white space-y-4 shadow-sm">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    </div>
                    <p class="text-sm text-brand-muted leading-relaxed">
                        "Tengo mi historial ordenado y la atención del personal médico es impecable. Siento que realmente controlo mi salud."
                    </p>
                    <div class="pt-2 border-t border-brand-border/60">
                        <div class="text-sm font-bold text-brand-heading">Sofía Ramírez</div>
                        <div class="text-xs text-brand-subtle">Paciente de Nutrición</div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================
         10. BANNER CTA FINAL
    ============================================================ -->
    <section id="contacto" class="py-16 lg:py-20 bg-brand-dark text-white text-center relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">
            
            <div class="w-16 h-16 rounded-full bg-brand-emerald/20 text-brand-light flex items-center justify-center mx-auto shadow-inner">
                <i data-lucide="heart-pulse" class="w-8 h-8 animate-pulse"></i>
            </div>

            <h2 class="text-3xl sm:text-5xl font-bold font-funnel tracking-tight">Tu próxima cita médica está a un clic</h2>
            
            <p class="text-base sm:text-lg text-zinc-300 max-w-2xl mx-auto">
                Accede ahora mismo a la plataforma web o descarga la app móvil. Crear tu cuenta toma menos de 2 minutos.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="{{ route('registro') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand-light hover:bg-lime-400 text-brand-dark font-bold text-base shadow-xl transition-transform hover:scale-105 flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span>Crear mi cuenta ahora</span>
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-full border-2 border-white/40 hover:border-white text-white font-bold text-base transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span>Entrar al panel web</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
         11. FOOTER
    ============================================================ -->
    @include('components.landing-footer')

    <!-- Scripts de Interacción -->
    <script>
        // Inicialización de Lucide Icons
        if (window.lucide) {
            lucide.createIcons();
        }

        // Toggle del Menú Móvil
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
