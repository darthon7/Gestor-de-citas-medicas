@extends('layouts.auth')
@section('titulo', 'Registro de Pacientes')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="width: 100%; max-width: 620px;">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i data-lucide="user-plus" class="fs-2"></i>
            </div>
            <h2 class="fw-bold text-dark h4 mb-1">Crear Cuenta de Paciente</h2>
            <p class="text-secondary small mb-0">Completa el formulario para registrarte en el sistema de citas médicas.</p>
        </div>

        <form method="POST" action="{{ route('registro') }}" class="row g-3">
            @csrf
            <div class="col-12">
                <label for="txt_nombre" class="form-label small fw-semibold text-secondary">Nombre Completo *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="user" class="text-secondary"></i></span>
                    <input type="text" id="txt_nombre" name="nombre" class="form-control border-start-0 ps-0 bg-light" value="{{ old('nombre') }}" placeholder="Juan García Hernández" required>
                </div>
            </div>

            <div class="col-md-6">
                <label for="txt_curp" class="form-label small fw-semibold text-secondary">CURP *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="id-card" class="text-secondary"></i></span>
                    <input type="text" id="txt_curp" name="curp" class="form-control border-start-0 ps-0 bg-light text-uppercase" value="{{ old('curp') }}" placeholder="18 caracteres" maxlength="18" required>
                </div>
            </div>

            <div class="col-md-6">
                <label for="txt_email" class="form-label small fw-semibold text-secondary">Correo Electrónico *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="mail" class="text-secondary"></i></span>
                    <input type="email" id="txt_email" name="email" class="form-control border-start-0 ps-0 bg-light" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                </div>
            </div>

            <div class="col-md-6">
                <label for="txt_telefono" class="form-label small fw-semibold text-secondary">Teléfono *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i data-lucide="phone" class="text-secondary"></i></span>
                    <input type="tel" id="txt_telefono" name="telefono" class="form-control border-start-0 ps-0 bg-light" value="{{ old('telefono') }}" placeholder="10 dígitos" maxlength="10" required>
                </div>
            </div>

            <div class="col-md-6">
                <label for="inp_fecha_nac" class="form-label small fw-semibold text-secondary">Fecha de Nacimiento</label>
                <input type="date" id="inp_fecha_nac" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="form-control bg-light">
            </div>

            <div class="col-md-6">
                <label for="sel_sexo" class="form-label small fw-semibold text-secondary">Sexo</label>
                <select id="sel_sexo" name="sexo" class="form-select bg-light">
                    <option value="">Seleccione...</option>
                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="txt_nss" class="form-label small fw-semibold text-secondary">NSS (Opcional)</label>
                <input type="text" id="txt_nss" name="nss" class="form-control bg-light" value="{{ old('nss') }}" placeholder="Número de Seg. Social">
            </div>

            <div class="col-md-6">
                <label for="txt_password" class="form-label small fw-semibold text-secondary">Contraseña *</label>
                <input type="password" id="txt_password" name="password" class="form-control bg-light" placeholder="Mínimo 8 caracteres" minlength="8" required>
            </div>

            <div class="col-md-6">
                <label for="txt_password_confirm" class="form-label small fw-semibold text-secondary">Confirmar Contraseña *</label>
                <input type="password" id="txt_password_confirm" name="password_confirmation" class="form-control bg-light" placeholder="Repite contraseña" minlength="8" required>
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                    Registrar Cuenta
                </button>
            </div>
        </form>
    </div>

    <div class="card-footer bg-light border-0 py-3 text-center small text-secondary">
        ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none">Inicia sesión aquí</a>
    </div>
</div>
@endsection
