@extends('layouts.auth')
@section('titulo', 'Restablecer Contraseña')

@section('content')
<div class="auth-card">
    <div class="auth-card__header">
        <div class="auth-card__icon">
            <i data-lucide="lock"></i>
        </div>
        <h1 class="auth-card__title">Nueva Contraseña</h1>
        <p class="auth-card__subtitle">Ingresa tu nueva contraseña para actualizar el acceso.</p>
    </div>

    <form method="POST" action="{{ route('restablecer') }}" class="auth-card__form">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="codigo" value="{{ $codigo }}">

        <div class="form-group">
            <label for="txt_new_pass" class="form-label">Nueva Contraseña</label>
            <div class="auth-card__input-wrapper">
                <i data-lucide="key" class="leading-icon"></i>
                <input type="password" id="txt_new_pass" name="password" class="form-input" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
        </div>

        <div class="form-group">
            <label for="txt_confirm_pass" class="form-label">Confirmar Nueva Contraseña</label>
            <div class="auth-card__input-wrapper">
                <i data-lucide="check-circle" class="leading-icon"></i>
                <input type="password" id="txt_confirm_pass" name="password_confirmation" class="form-input" placeholder="Repite la contraseña" required minlength="8">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; height: 48px; margin-top: 12px;">
            <span>Guardar Nueva Contraseña</span>
        </button>
    </form>
</div>
@endsection
