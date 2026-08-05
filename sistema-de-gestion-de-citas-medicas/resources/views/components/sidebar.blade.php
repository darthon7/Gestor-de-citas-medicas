<aside class="sidebar-wrapper d-flex flex-column p-3 text-white">
    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none mb-4 ps-2">
        <div class="rounded-3 bg-primary text-white font-weight-bold d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; font-weight: 700;">
            AM
        </div>
        <span class="fs-5 fw-bold">Agenda Médica</span>
    </a>

    <hr class="border-secondary opacity-25 mt-0 mb-3">

    <!-- Navigation List -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active bg-primary' : 'opacity-75 text-hover-white' }}">
                    <i data-lucide="home"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pacientes.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('pacientes.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="users"></i>
                    <span>Pacientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('citas.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('citas.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="calendar"></i>
                    <span>Citas</span>
                </a>
            </li>
        @endif

        @if(Auth::user()->rol === 'doctor')
            <li class="nav-item">
                <a href="{{ route('doctor.agenda') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('doctor.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="calendar"></i>
                    <span>Mi Agenda</span>
                </a>
            </li>
        @endif

        @if(Auth::user()->rol === 'admin')
            <li class="nav-item">
                <a href="{{ route('doctores.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('doctores.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="user-cog"></i>
                    <span>Doctores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('especialidades.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('especialidades.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="stethoscope"></i>
                    <span>Especialidades</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('recepcionistas.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('recepcionistas.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="user-check"></i>
                    <span>Recepcionistas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('reportes.index') }}" class="nav-link text-white d-flex align-items-center gap-2 {{ request()->routeIs('reportes.*') ? 'active bg-primary' : 'opacity-75' }}">
                    <i data-lucide="bar-chart-3"></i>
                    <span>Reportes</span>
                </a>
            </li>
        @endif
    </ul>

    <hr class="border-secondary opacity-25">

    <!-- User Profile & Logout Footer -->
    <div class="d-flex align-items-center justify-content-between pt-2">
        <a href="{{ route('perfil') }}" class="d-flex align-items-center text-white text-decoration-none overflow-hidden me-2">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-2 flex-shrink-0" style="width: 36px; height: 36px;">
                {{ strtoupper(substr(Auth::user()->nombre ?? 'U', 0, 1)) }}
            </div>
            <div class="text-truncate" style="max-width: 130px;">
                <div class="fw-semibold text-truncate small">{{ Auth::user()->nombre }}</div>
                <div class="text-white-50 extra-small text-capitalize" style="font-size: 11px;">{{ Auth::user()->rol }}</div>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light border-0 text-white-50 p-1" title="Cerrar Sesión">
                <i data-lucide="log-out"></i>
            </button>
        </form>
    </div>
</aside>
