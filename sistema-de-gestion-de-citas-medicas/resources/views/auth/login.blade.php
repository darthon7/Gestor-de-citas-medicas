@extends('layouts.auth')
@section('titulo', 'Inicio de Sesión')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full">
    <!-- Brand Identity -->
    <div class="mb-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4 border border-primary/20 shadow-inner">
            <span class="material-symbols-outlined text-4xl">medical_services</span>
        </div>
        <h1 class="text-2xl font-bold text-primary-dark tracking-tight">Agenda Médica</h1>
        <p class="text-xs text-text-secondary mt-1">Acceso centralizado al Hospital Central</p>
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="w-full space-y-5">
        @csrf

        <!-- Email Field -->
        <div class="space-y-1.5">
            <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="usuario@hospitalcentral.com" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
            <div class="flex justify-between items-center">
                <label for="txt_password" class="text-xs font-semibold text-text-secondary">Contraseña</label>
                <a href="{{ route('recuperar') }}" class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                <input type="password" id="txt_password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full pl-10 pr-10 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                <button type="button" onclick="const pass = document.getElementById('txt_password'); pass.type = pass.type === 'password' ? 'text' : 'password';" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-xl">visibility</span>
                </button>
            </div>
        </div>

        <!-- Action Button -->
        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
            <span>Ingresar</span>
            <span class="material-symbols-outlined text-xl">login</span>
        </button>
    </form>

    <!-- Footer Secondary Actions -->
    <div class="mt-8 pt-6 border-t border-border w-full text-center">
        <p class="text-xs text-text-secondary">
            ¿No tienes una cuenta? 
            <a href="{{ route('registro') }}" class="text-primary font-bold hover:underline">Regístrate aquí</a>
        </p>
    </div>
</div>

<!-- System Status Indicator -->
<div class="mt-6 flex items-center justify-center space-x-2 text-xs text-text-secondary">
    <div class="w-2 h-2 rounded-full bg-secondary animate-pulse"></div>
    <span>Servidores en línea - Acceso Seguro SSL</span>
</div>
@endsection
