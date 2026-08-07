<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Agenda Médica — Sistema de Gestión de Citas Médicas. Agenda, consulta y controla tus citas médicas en línea, sin filas y sin llamadas. Accede como paciente o como personal clínico.">
    <title>Agenda Médica — Sistema de Gestión de Citas Médicas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos propios de la Landing -->
    <link rel="stylesheet" href="{{ asset('css/pages/landing.css') }}">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <!-- ============================================================
         NAVBAR FIJA — Glassmorphic Header
    ============================================================ -->
    <nav class="navbar navbar-expand-lg navbar-dark landing-nav fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2.5 fw-bold text-white" href="{{ route('landing') }}">
                <img src="{{ asset('assets/logo-am.svg') }}" alt="Logo Agenda Médica" width="36" height="36" class="landing-logo">
                <span class="fs-5 tracking-tight">Agenda <span class="text-landing-light fw-normal">Médica</span></span>
            </a>

            <button class="navbar-toggler border-0 text-white p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navLanding" aria-controls="navLanding" aria-expanded="false" aria-label="Abrir menú">
                <i data-lucide="menu" class="text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navLanding">
                <ul class="navbar-nav mx-auto gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#perfiles">Elige tu Perfil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beneficios">Beneficios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cta-final">Empieza Hoy</a></li>
                </ul>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-landing-cta d-flex align-items-center gap-2">
                        <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i> Ir al Dashboard
                    </a>
                @else
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-landing-cta d-flex align-items-center gap-2">
                            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i> Iniciar Sesión
                        </a>
                        <a href="{{ route('registro') }}" class="btn btn-landing-outline d-none d-sm-inline-flex align-items-center gap-2">
                            <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Regístrate
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- ============================================================
         HERO — Título + Subtítulo + CTAs Principales
    ============================================================ -->
    <header id="inicio" class="landing-hero d-flex align-items-center">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="landing-badge d-inline-flex align-items-center gap-2 mb-3">
                        <i data-lucide="activity" class="landing-pulse-icon" style="width: 16px; height: 16px;"></i> Plataforma de Salud Digital 2026
                    </span>
                    <h1 class="landing-hero-title mb-3">
                        Tu salud y tus citas, <span class="highlight-gradient">a un toque</span> de distancia
                    </h1>
                    <p class="landing-hero-subtitle mb-4">
                        Agenda, consulta y controla tu atención médica sin filas y sin llamadas.
                        Plataforma inteligente diseñada para conectar pacientes y especialistas de forma ágil y transparente.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                        <a href="{{ route('login') }}" class="btn btn-landing-primary btn-lg d-flex align-items-center justify-content-center gap-2">
                            <i data-lucide="stethoscope" style="width: 20px; height: 20px;"></i>
                            Acceso Personal Médico
                        </a>
                        <a href="{{ route('registro') }}" class="btn btn-landing-accent btn-lg d-flex align-items-center justify-content-center gap-2">
                            <i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i>
                            Portal del Paciente / Regístrate
                        </a>
                    </div>

                    <p class="landing-hero-note d-flex align-items-center gap-2 mb-0">
                        <i data-lucide="shield-check" class="text-landing-secondary" style="width: 18px; height: 18px;"></i>
                        Acceso seguro SSL · Confirmación al instante · Disponible 24/7
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="landing-hero-visual p-4 p-lg-5 text-center">
                        <div class="d-inline-flex p-3 rounded-circle bg-light shadow-sm mb-3">
                            <img src="{{ asset('assets/logo-am.svg') }}" alt="Agenda Médica" style="width: 80px; height: 80px; filter: drop-shadow(0 6px 18px rgba(0,82,117,0.3));">
                        </div>
                        <h3 class="fw-bold text-landing-primary mb-1">Agenda Médica</h3>
                        <p class="text-landing-secondary fw-medium mb-4">Centro Digital de Gestión Clínica</p>

                        <div class="row g-3 text-start">
                            <div class="col-6">
                                <div class="landing-mini-stat">
                                    <i data-lucide="calendar-check" class="text-landing-secondary" style="width: 22px; height: 22px;"></i>
                                    <strong>Agendamiento</strong>
                                    <span>100% autónomo</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="landing-mini-stat">
                                    <i data-lucide="clock" class="text-landing-accent" style="width: 22px; height: 22px;"></i>
                                    <strong>Menos espera</strong>
                                    <span>Control en tiempo real</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="landing-mini-stat">
                                    <i data-lucide="file-text" class="text-landing-primary" style="width: 22px; height: 22px;"></i>
                                    <strong>Expedientes</strong>
                                    <span>Historial médico digital</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="landing-mini-stat">
                                    <i data-lucide="shield-check" class="text-landing-secondary" style="width: 22px; height: 22px;"></i>
                                    <strong>Cero duplicados</strong>
                                    <span>Información verificada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============================================================
         ELIGE TU PERFIL — Pacientes vs Personal Clínico
    ============================================================ -->
    <section id="perfiles" class="landing-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="landing-section-title">Elige tu Perfil de Acceso</h2>
                <p class="landing-section-subtitle mx-auto">
                    Una sola plataforma, dos experiencias a tu medida. Inicia sesión o regístrate según tu rol.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Tarjeta Paciente -->
                <div class="col-lg-6">
                    <div class="landing-card landing-card-patient h-100">
                        <div class="landing-card-icon">
                            <i data-lucide="smartphone"></i>
                        </div>
                        <span class="landing-chip landing-chip-teal mb-3">Portal del Paciente</span>
                        <h3 class="landing-card-title">Para Pacientes</h3>
                        <p class="landing-card-text">
                            Gestiona tu salud en línea sin intermediarios, de forma rápida y confidencial desde cualquier dispositivo.
                        </p>
                        <ul class="landing-card-list">
                            <li><i data-lucide="search" class="text-landing-secondary"></i> Búsqueda rápida de doctores por especialidad</li>
                            <li><i data-lucide="calendar-plus" class="text-landing-secondary"></i> Agendamiento directo disponible las 24 horas</li>
                            <li><i data-lucide="history" class="text-landing-secondary"></i> Consulta tu historial de citas y diagnósticos</li>
                            <li><i data-lucide="bell-ring" class="text-landing-secondary"></i> Cancelación y reprogramación simple</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('registro') }}" class="btn btn-landing-secondary-outline w-100">
                                Crear Cuenta de Paciente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Personal Clínico -->
                <div class="col-lg-6">
                    <div class="landing-card landing-card-staff h-100">
                        <div class="landing-card-icon">
                            <i data-lucide="monitor"></i>
                        </div>
                        <span class="landing-chip landing-chip-blue mb-3">Sistema Web · Personal Clínico</span>
                        <h3 class="landing-card-title">Para Personal Clínico</h3>
                        <p class="landing-card-text">
                            Panel integral para Médicos, Recepcionistas y Administradores. Control total del flujo de atención.
                        </p>
                        <ul class="landing-card-list">
                            <li><i data-lucide="calendar-days" class="text-landing-primary"></i> Gestión de agendas y bloqueos de disponibilidad</li>
                            <li><i data-lucide="folder-open" class="text-landing-primary"></i> Expedientes y notas de consulta médica</li>
                            <li><i data-lucide="workflow" class="text-landing-primary"></i> Control de check-in y estado de consultas en tiempo real</li>
                            <li><i data-lucide="bar-chart-3" class="text-landing-primary"></i> Reportes exportables y estadísticas del centro</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('login') }}" class="btn btn-landing-primary w-100">
                                Acceso al Panel Web
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </a>
                            <p class="landing-download-note mt-3 mb-0">
                                <i data-lucide="lock" style="width: 14px; height: 14px;"></i>
                                Acceso seguro con credenciales institucionales
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         BENEFICIOS — Por qué dar el paso
    ============================================================ -->
    <section id="beneficios" class="landing-section landing-section-tinted">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="landing-section-title">¿Por qué dar el paso a la salud digital?</h2>
                <p class="landing-section-subtitle mx-auto">
                    Optimización del tiempo, cero confusiones y un centro médico eficiente de principio a fin.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="landing-benefit">
                        <div class="landing-benefit-icon bg-landing-primary">
                            <i data-lucide="phone-off"></i>
                        </div>
                        <h4>Sin llamadas largas</h4>
                        <p>Agenda, cancela o reprograma tus citas de forma autónoma sin intermediarios.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="landing-benefit">
                        <div class="landing-benefit-icon bg-landing-secondary">
                            <i data-lucide="users"></i>
                        </div>
                        <h4>Cero filas presenciales</h4>
                        <p>Acudir al centro solo para pedir una cita quedó atrás. Agendamiento en línea las 24 horas.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="landing-benefit">
                        <div class="landing-benefit-icon bg-landing-accent">
                            <i data-lucide="gauge"></i>
                        </div>
                        <h4>Monitoreo en tiempo real</h4>
                        <p>El personal clínico revisa el estado del consultorio y recepciones en un solo panel.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="landing-benefit">
                        <div class="landing-benefit-icon bg-landing-danger">
                            <i data-lucide="shield-alert"></i>
                        </div>
                        <h4>Información unificada</h4>
                        <p>Elimina empalmes de citas y desorden en archivos con una base de datos centralizada.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CTA FINAL
    ============================================================ -->
    <section id="cta-final" class="landing-section">
        <div class="container">
            <div class="landing-cta-final rounded-4 text-center">
                <div class="landing-cta-icon mx-auto mb-3">
                    <i data-lucide="heart-pulse" class="landing-pulse-icon" style="width: 36px; height: 36px;"></i>
                </div>
                <h2 class="landing-section-title text-white mb-2">Tu próxima cita médica está a un clic</h2>
                <p class="landing-cta-text mx-auto mb-4">
                    Accede ahora mismo a la plataforma web. Crear tu cuenta toma menos de 2 minutos.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ route('registro') }}" class="btn btn-landing-accent btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="user-plus" style="width: 20px; height: 20px;"></i>
                        Crear mi cuenta ahora
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-landing-ghost btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="log-in" style="width: 20px; height: 20px;"></i>
                        Entrar al panel web
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FOOTER
    ============================================================ -->
    <footer class="landing-footer py-4">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 text-center">
            <div class="d-flex align-items-center gap-2 fw-bold text-white">
                <img src="{{ asset('assets/logo-am.svg') }}" alt="Logo Agenda Médica" width="26" height="26">
                Agenda Médica
            </div>
            <p class="mb-0 text-white-50 small">© 2026 Agenda Médica — Centro de Salud. Todos los derechos reservados.</p>
            <div class="d-flex gap-3 small flex-wrap justify-content-center">
                <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Acceso Personal</a>
                <a href="{{ route('registro') }}" class="text-white-50 text-decoration-none">Registro Pacientes</a>
                <a href="{{ route('registro.doctor') }}" class="text-white-50 text-decoration-none">
                    ¿Eres doctor? Regístrate aquí
                </a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>

