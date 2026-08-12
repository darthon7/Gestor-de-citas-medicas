@extends('layouts.auth')
@section('titulo', 'Inicio de Sesión')

@section('content')
<div class="bg-surface rounded-3xl p-8 md:p-10 shadow-2xl border border-border flex flex-col items-center w-full">
    <!-- Brand Identity Logo -->
    <a href="{{ route('landing') }}" class="mb-6 flex items-center gap-2.5 group">
        <div class="w-11 h-11 rounded-xl bg-emerald-100/70 p-1.5 flex items-center justify-center transition-transform group-hover:scale-105">
            <svg viewBox="0 0 38.717 33.301" class="w-8 h-8 overflow-visible">
                <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
            </svg>
        </div>
        <span class="text-3xl font-bold font-funnel tracking-tight text-brand-heading">
            Vida<span class="text-brand-emerald">+</span>
        </span>
    </a>

    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold font-funnel text-brand-heading tracking-tight">Iniciar Sesión</h1>
        <p class="text-xs text-text-secondary mt-1">Acceso seguro al sistema de agendamiento médico</p>
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="w-full space-y-5">
        @csrf

        <!-- Email Field -->
        <div class="space-y-1.5">
            <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="usuario@correo.com" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
            <div class="flex justify-between items-center">
                <label for="txt_password" class="text-xs font-semibold text-text-secondary">Contraseña</label>
                <a href="{{ route('recuperar') }}" class="text-xs font-semibold text-brand-emerald hover:underline transition-colors">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                <input type="password" id="txt_password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full pl-10 pr-10 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                <button type="button" onclick="const pass = document.getElementById('txt_password'); pass.type = pass.type === 'password' ? 'text' : 'password';" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-brand-emerald transition-colors">
                    <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
            </div>
        </div>

        <!-- Action Button -->
        <button type="submit" class="w-full bg-brand-emerald hover:bg-emerald-700 text-white py-3.5 px-6 rounded-full font-bold text-sm shadow-md hover:shadow-lg active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
            <span>Ingresar</span>
            <span class="material-symbols-outlined text-xl">login</span>
        </button>
    </form>

    <!-- Footer Secondary Actions -->
    <div class="mt-8 pt-6 border-t border-border w-full text-center space-y-2">
        <p class="text-xs text-text-secondary">
            ¿No tienes una cuenta? 
            <a href="{{ route('registro') }}" class="text-brand-emerald font-bold hover:underline">Regístrate aquí</a>
        </p>
        <p class="text-xs text-text-secondary pt-1">
            <a href="{{ route('landing') }}" class="text-text-muted hover:text-brand-emerald transition-colors inline-flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Volver a la página principal
            </a>
        </p>
    </div>
</div>

<!-- System Status Indicator -->
<div class="mt-6 flex items-center justify-center space-x-2 text-xs text-white/80">
    <div class="w-2 h-2 rounded-full bg-brand-light animate-pulse"></div>
    <span>Sistema Vida+ en línea &middot; Acceso Seguro</span>
</div>
@endsection

