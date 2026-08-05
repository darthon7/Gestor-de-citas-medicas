@extends('layouts.auth')
@section('titulo', 'Inicio de Sesión')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="width: 100%; max-width: 440px;">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i data-lucide="activity" class="fs-2"></i>
            </div>
            <h2 class="fw-bold text-dark h4 mb-1">Agenda Médica</h2>
            <p class="text-secondary small mb-0">Sistema de Gestión de Citas Médicas</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="txt_email" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="mail" class="text-secondary"></i></span>
                    <input type="email" id="txt_email" name="email" class="form-control border-start-0 ps-0 bg-light" value="{{ old('email') }}" placeholder="ejemplo@clinicamedica.com" required autocomplete="email">
                </div>
            </div>

            <div class="mb-3">
                <label for="txt_password" class="form-label small fw-semibold text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="lock" class="text-secondary"></i></span>
                    <input type="password" id="txt_password" name="password" class="form-control border-start-0 border-end-0 ps-0 bg-light" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" onclick="const pass = document.getElementById('txt_password'); pass.type = pass.type === 'password' ? 'text' : 'password';" class="btn btn-light border border-start-0 text-secondary" tabindex="-1">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('recuperar') }}" class="small text-decoration-none text-primary fw-semibold">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                Ingresar
            </button>
        </form>
    </div>

    <div class="card-footer bg-light border-0 py-3 text-center small text-secondary">
        ¿No tienes una cuenta? <a href="{{ route('registro') }}" class="fw-bold text-primary text-decoration-none">Regístrate aquí</a>
    </div>
</div>
@endsection
