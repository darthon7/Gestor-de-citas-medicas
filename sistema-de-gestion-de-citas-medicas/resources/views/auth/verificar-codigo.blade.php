@extends('layouts.auth')
@section('titulo', 'Verificar Código')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full">
    <div class="w-14 h-14 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mb-4 border border-teal-200">
        <span class="material-symbols-outlined text-3xl">verified_user</span>
    </div>

    <h1 class="text-2xl font-bold text-primary-dark tracking-tight text-center">Verificar Código</h1>
    <p class="text-xs text-text-secondary mt-1 text-center mb-6">
        Ingresa el código enviado a <strong class="text-primary">{{ $email ?? 'tu correo' }}</strong>.
    </p>

    <form method="POST" action="{{ route('verificar.codigo') }}" class="w-full space-y-5">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="space-y-1.5">
            <label for="txt_codigo" class="text-xs font-semibold text-text-secondary block">Código de 6 dígitos</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">pin</span>
                <input type="text" id="txt_codigo" name="codigo" required placeholder="123456" maxlength="6" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-lg font-bold tracking-widest text-center text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2">
            <span>Verificar Código</span>
            <span class="material-symbols-outlined text-xl">check_circle</span>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-border w-full text-center">
        <a href="{{ route('recuperar') }}" class="text-xs font-semibold text-primary hover:underline">Reenviar código o cambiar correo</a>
    </div>
</div>
@endsection
