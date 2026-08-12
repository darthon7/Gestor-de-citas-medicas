@extends('layouts.auth')
@section('titulo', 'Recuperar Contraseña')

@section('content')
<div class="bg-surface rounded-3xl p-8 md:p-10 shadow-2xl border border-border flex flex-col items-center w-full">
    <!-- Brand Identity Logo -->
    <a href="{{ route('landing') }}" class="mb-5 flex items-center gap-2.5 group">
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

    <h1 class="text-2xl font-bold font-funnel text-brand-heading tracking-tight text-center">Recuperar Contraseña</h1>
    <p class="text-xs text-text-secondary mt-1 text-center mb-6">
        Ingresa tu correo electrónico registrado y te enviaremos un código para restablecer tu contraseña.
    </p>

    <form method="POST" action="{{ route('recuperar') }}" class="w-full space-y-5">
        @csrf
        <div class="space-y-1.5">
            <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required placeholder="ejemplo@correo.com" class="w-full pl-10 pr-4 py-3 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
        </div>

        <button type="submit" class="w-full bg-brand-emerald hover:bg-emerald-700 text-white py-3.5 px-6 rounded-full font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2">
            <span>Enviar código de recuperación</span>
            <span class="material-symbols-outlined text-xl">send</span>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-border w-full text-center">
        <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-emerald hover:underline flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Volver al inicio de sesión</span>
        </a>
    </div>
</div>
@endsection

