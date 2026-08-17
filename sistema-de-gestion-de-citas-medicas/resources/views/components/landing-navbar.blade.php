<!-- ============================================================
     PUBLIC NAVBAR (Vida+ Landing & Auth Header - Dark Emerald Theme)
============================================================ -->
<style>
    .navbar-glass-dark {
        background-color: rgba(14, 34, 24, 0.96);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
</style>

<header class="public-navbar sticky top-0 navbar-glass-dark bg-gradient-to-r from-[#0E2218] via-[#165838] to-[#1E8E5A] border-b border-emerald-800/60 z-40 transition-all duration-300 w-full text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[78px] flex items-center justify-between relative">
        
        <!-- Marca / Logo -->
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
            <div class="w-10 h-10 rounded-xl bg-emerald-950/80 border border-emerald-700/60 p-1.5 flex items-center justify-center transition-transform group-hover:scale-105">
                <svg viewBox="0 0 38.717 33.301" class="w-7 h-7 overflow-visible">
                    <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                    <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
                </svg>
            </div>
            <span class="text-2xl font-bold font-funnel tracking-tight text-white">
                Vida<span class="text-emerald-300">+</span>
            </span>
        </a>

        <!-- Navegación Principal Desktop (Enlaces estáticos) -->
        <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-white">
            <a href="{{ route('landing') }}#inicio" class="hover:text-emerald-300 transition-colors py-2">Inicio</a>
            <a href="{{ route('landing') }}#caracteristicas" class="hover:text-emerald-300 transition-colors py-2">Servicios</a>
            <a href="{{ route('especialidades.publicas') }}" class="hover:text-emerald-300 transition-colors py-2">Especialidades</a>
            
        </nav>

        <!-- Acciones / Auth Buttons -->
        <div class="hidden sm:flex items-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-[#0E2218] font-bold text-sm hover:bg-emerald-100 shadow-md transition-all hover:scale-[1.02]">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Ir al Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-200 hover:text-white transition-colors px-2 py-1">
                    Iniciar sesión
                </a>
                <a href="{{ route('registro') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-[#0E2218] font-bold text-sm hover:bg-emerald-100 shadow-md transition-all hover:scale-[1.02]">
                    <i data-lucide="user-plus" class="w-4 h-4 text-emerald-800"></i>
                    <span>Registrarse</span>
                </a>
            @endauth
        </div>

        <!-- Botón Hamburguesa Móvil -->
        <button id="mobileMenuBtn" type="button" class="lg:hidden p-2 text-white hover:text-emerald-300 focus:outline-none" aria-label="Abrir menú">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Menú Móvil Desplegable -->
    <div id="mobileMenu" class="hidden lg:hidden bg-[#0A1D13] border-b border-emerald-800/80 px-4 py-6 space-y-4">
        <a href="{{ route('landing') }}#inicio" class="block text-base font-semibold text-white hover:text-emerald-300">Inicio</a>
        <a href="{{ route('landing') }}#caracteristicas" class="block text-base font-semibold text-white hover:text-emerald-300">Servicios</a>
        <a href="{{ route('especialidades.publicas') }}" class="block text-base font-semibold text-white hover:text-emerald-300">Especialidades</a>
        
        <div class="pt-4 border-t border-emerald-800/60 flex flex-col gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full text-center py-3 rounded-xl bg-white text-[#0E2218] font-bold">Ir al Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-emerald-400 text-emerald-200 font-semibold">Iniciar sesión</a>
                <a href="{{ route('registro') }}" class="w-full text-center py-2.5 rounded-xl bg-white text-[#0E2218] font-bold">Registrarse</a>
            @endauth
        </div>
    </div>
</header>
