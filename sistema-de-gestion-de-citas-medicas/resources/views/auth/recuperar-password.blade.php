@extends('layouts.auth')
@section('titulo', 'Recuperar Contraseña')

@section('content')
<div class="auth-card">
    <div class="auth-card__header">
        <div class="auth-card__icon" style="background-color: rgba(233, 163, 25, 0.12); color: var(--color-accent);">
            <i data-lucide="key-round"></i>
        </div>
        <h1 class="auth-card__title">Recuperar Contraseña</h1>
        <p class="auth-card__subtitle">Ingresa tu correo electrónico registrado y te enviaremos un código de recuperación.</p>
    </div>

    <form method="POST" action="{{ route('recuperar') }}" class="auth-card__form">
        @csrf
        <div class="form-group">
            <label for="txt_email" class="form-label">Correo Electrónico</label>
            <div class="auth-card__input-wrapper">
                <i data-lucide="mail" class="leading-icon"></i>
                <input type="email" id="txt_email" name="email" class="form-input" value="{{ old('email') }}" placeholder="ejemplo@clinicamedica.com" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; height: 48px; margin-top: 12px;">
            <span>Enviar Código de Recuperación</span>
        </button>
    </form>

    <div class="auth-card__footer" style="margin-top: 20px;">
        <a href="{{ route('login') }}" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left" style="font-size: 14px;"></i> Volver al inicio de sesión
        </a>
    </div>
</div>
@endsection
