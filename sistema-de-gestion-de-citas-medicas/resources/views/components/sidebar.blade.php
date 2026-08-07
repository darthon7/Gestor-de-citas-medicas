<aside class="w-[260px] h-screen fixed left-0 top-0 bg-primary-dark shadow-xl flex flex-col py-6 z-50 overflow-y-auto hidden md:flex">
    <!-- Brand Header -->
    <div class="px-6 mb-8 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/10 text-white font-bold flex items-center justify-center text-lg border border-white/20 shadow-inner">
            AM
        </div>
        <div>
            <span class="font-bold text-lg text-white tracking-tight block leading-tight">MediAdmin</span>
            <span class="text-[11px] text-white/60">Hospital Central</span>
        </div>
    </div>

    <!-- Navigation List -->
    <nav class="flex-1 space-y-1.5 px-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
            <span class="material-symbols-outlined text-xl">dashboard</span>
            <span>Inicio</span>
        </a>

        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
            <a href="{{ route('pacientes.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('pacientes.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">group</span>
                <span>Pacientes</span>
            </a>
            <a href="{{ route('citas.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('citas.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">calendar_month</span>
                <span>Citas</span>
            </a>
            <a href="{{ route('doctores.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('doctores.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">medical_services</span>
                <span>Doctores</span>
            </a>
        @endif

        @if(Auth::user()->rol === 'paciente')
            <a href="{{ route('citas.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('citas.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">event_upcoming</span>
                <span>Mis Citas</span>
            </a>
        @endif

        @if(Auth::user()->rol === 'doctor')
            <a href="{{ route('doctor.agenda') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('doctor.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">calendar_today</span>
                <span>Mi Agenda</span>
            </a>
        @endif

        @if(Auth::user()->rol === 'admin')
            <a href="{{ route('especialidades.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('especialidades.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">stethoscope</span>
                <span>Especialidades</span>
            </a>
            <a href="{{ route('recepcionistas.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('recepcionistas.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">badge</span>
                <span>Recepcionistas</span>
            </a>
            <a href="{{ route('reportes.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('reportes.*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">analytics</span>
                <span>Reportes</span>
            </a>
        @endif
    </nav>

    <!-- Footer Action / Logout -->
    <div class="px-4 pt-4 border-t border-white/10 space-y-2">
        <a href="{{ route('perfil') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
            <span class="material-symbols-outlined text-xl">account_circle</span>
            <div class="flex-1 truncate text-xs">
                <p class="font-medium text-white truncate">{{ Auth::user()->nombre }}</p>
                <p class="text-white/60 capitalize">{{ Auth::user()->rol }}</p>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-white/60 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                <span class="material-symbols-outlined text-lg">logout</span>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>
