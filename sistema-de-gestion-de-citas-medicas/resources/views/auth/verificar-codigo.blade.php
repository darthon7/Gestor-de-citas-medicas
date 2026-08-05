@extends('layouts.auth')
@section('titulo', 'Verificar Código')

@section('content')
<div class="auth-card">
    <div class="auth-card__header">
        <div class="auth-card__icon" style="background-color: rgba(42, 157, 143, 0.12); color: var(--color-secondary);">
            <i data-lucide="shield-check"></i>
        </div>
        <h1 class="auth-card__title">Verificar Código</h1>
        <p class="auth-card__subtitle">Ingresa el código enviado a {{ $email ?? 'tu correo' }}.</p>
    </div>

    <form method="POST" action="{{ route('verificar.codigo') }}" class="auth-card__form">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        
        <div class="form-group">
            <label for="txt_codigo" class="form-label">Código de 6 dígitos</label>
            <div class="auth-card__input-wrapper">
                <i data-lucide="hash" class="leading-icon"></i>
                <input type="text" id="txt_codigo" name="codigo" class="form-input" placeholder="123456" maxlength="6" required style="letter-spacing: 4px; font-weight: 700;">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; height: 48px; margin-top: 12px;">
            <span>Verificar Código</span>
        </button>
    </form>

    <div class="auth-card__footer" style="margin-top: 20px;">
        <a href="{{ route('recuperar') }}">Reenviar código o cambiar correo</a>
    </div>
</div>
@endsection
