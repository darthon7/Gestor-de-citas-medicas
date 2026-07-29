@extends('layouts.app')
@section('titulo', 'Mi Perfil')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Configuración de Mi Perfil</h1>
</div>

<div class="mx-auto" style="max-width: 720px;">
    <!-- Profile Hero Header -->
    <div class="card border-0 shadow-sm rounded-4 text-white mb-4 p-4" style="background: linear-gradient(135deg, #1d3557 0%, #2a9d8f 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-25 text-white fw-bold d-flex align-items-center justify-content-center border border-white border-opacity-50" style="width: 72px; height: 72px; font-size: 28px;">
                {{ strtoupper(substr($usuario->nombre ?? 'U', 0, 2)) }}
            </div>
            <div>
                <h2 class="h4 fw-bold mb-1 text-white">{{ $usuario->nombre }}</h2>
                <span class="badge bg-success text-capitalize fs-6">{{ $usuario->rol }}</span>
            </div>
        </div>
    </div>

    <!-- Personal Info Form -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2">Datos Personales</h5>
        <form method="POST" action="{{ route('perfil.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Nombre Completo</label>
                <input type="text" value="{{ $usuario->nombre }}" class="form-control bg-light" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Correo Electrónico</label>
                <input type="email" value="{{ $usuario->email }}" class="form-control bg-light" disabled>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="txt_telefono" class="form-label text-secondary small fw-semibold">Teléfono de Contacto *</label>
                    <input type="tel" id="txt_telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                </div>

                <div class="col-md-6">
                    <label for="txt_curp" class="form-label text-secondary small fw-semibold">CURP</label>
                    <input type="text" id="txt_curp" name="curp" value="{{ old('curp', $usuario->curp) }}" class="form-control text-uppercase" placeholder="18 caracteres">
                </div>
            </div>

            <div class="text-end pt-2">
                <button type="submit" class="btn btn-primary fw-semibold px-4">Actualizar Datos</button>
            </div>
        </form>
    </div>

    <!-- Password Change Form -->
    <div class="card border-0 shadow-sm rounded-3 p-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-danger">Cambiar Contraseña</h5>
        <form method="POST" action="{{ route('perfil.password') }}">
            @csrf
            <div class="mb-3">
                <label for="txt_pass_actual" class="form-label text-secondary small fw-semibold">Contraseña Actual *</label>
                <input type="password" id="txt_pass_actual" name="password_actual" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="txt_pass_nueva" class="form-label text-secondary small fw-semibold">Nueva Contraseña *</label>
                <input type="password" id="txt_pass_nueva" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required>
            </div>

            <div class="mb-3">
                <label for="txt_pass_conf" class="form-label text-secondary small fw-semibold">Confirmar Nueva Contraseña *</label>
                <input type="password" id="txt_pass_conf" name="password_confirmation" class="form-control" placeholder="Repetir contraseña" minlength="8" required>
            </div>

            <div class="text-end pt-2">
                <button type="submit" class="btn btn-outline-danger fw-semibold px-4">Cambiar Contraseña</button>
            </div>
        </form>
    </div>
</div>
@endsection
