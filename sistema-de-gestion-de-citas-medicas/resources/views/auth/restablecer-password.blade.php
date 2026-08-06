@extends('layouts.auth')
@section('titulo', 'Restablecer Contraseña')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full">
    <div class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-4 border border-primary/20">
        <span class="material-symbols-outlined text-3xl">key</span>
    </div>

    <h1 class="text-2xl font-bold text-primary-dark tracking-tight text-center">Nueva Contraseña</h1>
    <p class="text-xs text-text-secondary mt-1 text-center mb-6">
        Ingresa tu nueva contraseña para actualizar el acceso a tu cuenta.
    </p>

    <form method="POST" action="{{ route('restablecer') }}" class="w-full space-y-5">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="codigo" value="{{ $codigo }}">

        <div class="space-y-1.5">
            <label for="txt_new_pass" class="text-xs font-semibold text-text-secondary block">Nueva Contraseña</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                <input type="password" id="txt_new_pass" name="password" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="txt_confirm_pass" class="text-xs font-semibold text-text-secondary block">Confirmar Nueva Contraseña</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">check_circle</span>
                <input type="password" id="txt_confirm_pass" name="password_confirmation" required minlength="8" placeholder="Repite la contraseña" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2">
            <span>Guardar Nueva Contraseña</span>
            <span class="material-symbols-outlined text-xl">save</span>
        </button>
    </form>
</div>
@endsection
