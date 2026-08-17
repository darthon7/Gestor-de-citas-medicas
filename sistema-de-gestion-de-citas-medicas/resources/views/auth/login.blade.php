@extends('layouts.auth')
@section('titulo', 'Inicio de Sesión')

@section('content')
<div class="bg-surface rounded-3xl p-8 md:p-10 shadow-2xl border border-border flex flex-col items-center w-full">
    <!-- Brand Identity Logo -->
    <x-vida-logo class="mb-6" />

    <div class="mb-4 text-center">
        <h1 class="text-2xl font-bold font-funnel text-brand-heading tracking-tight">Iniciar Sesión</h1>
        <p class="text-xs text-text-secondary mt-1">Acceso seguro al sistema de agendamiento médico</p>
    </div>

    <!-- Línea de vida -->
    <div class="w-full flex items-center gap-3 mb-6" aria-hidden="true">
        <div class="h-px flex-1 bg-border"></div>
        <svg class="ecg-line h-5 w-24 text-brand-emerald" viewBox="0 0 96 20" fill="none">
            <path d="M0 10 H22 L27 6.5 L32 10 H40 L45 2.5 L50 17.5 L55 4.5 L59 10 H74 L79 7.5 L84 10 H96" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="h-px flex-1 bg-border"></div>
    </div>

    <!-- Login Form -->
    <form id="form_login" method="POST" action="{{ route('login') }}" class="w-full space-y-5">
        @csrf

        <!-- Email Field -->
        <div class="space-y-1.5">
            <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="usuario@correo.com" class="w-full pl-10 pr-4 py-3 bg-white border @error('email') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
            @error('email')
                <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
            <label for="txt_password" class="text-xs font-semibold text-text-secondary block">Contraseña</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                <input type="password" id="txt_password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full pl-10 pr-11 py-3 bg-white border @error('password') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                <button type="button" data-toggle-password="txt_password" aria-label="Mostrar u ocultar contraseña" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-brand-emerald transition-colors">
                    <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember + Recover -->
        <div class="flex items-center justify-between">
            <label for="chk_remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="chk_remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="rounded border-border text-brand-emerald focus:ring-brand-emerald/30">
                <span class="text-xs font-medium text-text-secondary">Recuérdame</span>
            </label>
            <a href="{{ route('recuperar') }}" class="text-xs font-semibold text-brand-emerald hover:underline transition-colors">¿Olvidaste tu contraseña?</a>
        </div>

        <!-- Action Button -->
        <button type="submit" data-loading-text="Verificando..." class="w-full bg-brand-emerald hover:bg-emerald-700 text-white py-3.5 px-6 rounded-full font-bold text-sm shadow-md hover:shadow-lg active:scale-[0.99] transition-all flex items-center justify-center space-x-2 disabled:opacity-80 disabled:cursor-not-allowed disabled:hover:bg-brand-emerald">
            <span data-btn-text>Ingresar</span>
            <span data-btn-icon class="material-symbols-outlined text-xl">login</span>
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

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mostrar / ocultar contraseña
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.togglePassword);
                const icon  = btn.querySelector('.material-symbols-outlined');
                const mostrar = input.type === 'password';
                input.type = mostrar ? 'text' : 'password';
                icon.textContent = mostrar ? 'visibility_off' : 'visibility';
            });
        });

        // Estado de carga al enviar (evita doble envío)
        const form = document.getElementById('form_login');
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.querySelector('[data-btn-text]').textContent = btn.dataset.loadingText;
            const icon = btn.querySelector('[data-btn-icon]');
            icon.textContent = 'progress_activity';
            icon.classList.add('animate-spin');
        });
    });
</script>
@endsection
