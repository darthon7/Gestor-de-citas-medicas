@extends('layouts.auth')
@section('titulo', 'Recuperar Contraseña')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full">
    <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-4 border border-amber-200">
        <span class="material-symbols-outlined text-3xl">lock_reset</span>
    </div>

    <h1 class="text-2xl font-bold text-primary-dark tracking-tight text-center">Recuperar Contraseña</h1>
    <p class="text-xs text-text-secondary mt-1 text-center mb-6">
        Ingresa tu correo electrónico registrado y te enviaremos un código para restablecer tu contraseña.
    </p>

    <form method="POST" action="{{ route('recuperar') }}" class="w-full space-y-5">
        @csrf
        <div class="space-y-1.5">
            <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required placeholder="ejemplo@correo.com" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2">
            <span>Enviar código de recuperación</span>
            <span class="material-symbols-outlined text-xl">send</span>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-border w-full text-center">
        <a href="{{ route('login') }}" class="text-xs font-semibold text-primary hover:underline flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Volver al inicio de sesión</span>
        </a>
    </div>
</div>
@endsection
