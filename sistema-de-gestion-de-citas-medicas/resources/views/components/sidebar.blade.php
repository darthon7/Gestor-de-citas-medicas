<aside class="w-[260px] h-screen fixed left-0 top-0 bg-primary-dark shadow-xl flex flex-col py-6 z-50 overflow-y-auto hidden md:flex">
    <!-- Brand Header -->
    <div class="px-6 mb-8 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 p-1.5 flex items-center justify-center shadow-inner">
            <svg viewBox="0 0 38.717 33.301" class="w-7 h-7 overflow-visible">
                <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
            </svg>
        </div>
        <div>
            <span class="font-bold text-lg text-white tracking-tight block leading-tight">Vida<span class="text-emerald-300">+</span></span>
            <span class="text-[11px] text-white/60">Agenda Médica</span>
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
            <a href="{{ route('doctor.agenda') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('doctor.agenda') || request()->routeIs('doctor.diagnostico') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">calendar_today</span>
                <span>Mi Agenda</span>
            </a>
            <a href="{{ route('doctor.horario') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('doctor.horario*') ? 'sidebar-item-active text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                <span class="material-symbols-outlined text-xl">schedule</span>
                <span>Mi Horario</span>
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
