@extends('layouts.app')
@section('titulo', 'Gestión de Recepcionistas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Gestión de Recepcionistas</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_recep">
        <i data-lucide="user-plus" class="me-1"></i> + Registrar Recepcionista
    </button>
</div>

<p class="text-secondary mb-4">Personal administrativo con permisos para agendar citas y gestionar pacientes.</p>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre Completo</th>
                        <th>Correo Institucional</th>
                        <th>Teléfono / CURP</th>
                        <th class="text-end pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recepcionistas as $recep)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $recep->nombre }}</td>
                            <td>{{ $recep->email }}</td>
                            <td class="small text-secondary">{{ $recep->telefono ?? 'N/A' }} / <span class="font-monospace">{{ $recep->curp ?? 'N/A' }}</span></td>
                            <td class="text-end pe-3">
                                <span class="badge {{ $recep->estado === 'activo' ? 'bg-success' : 'bg-danger' }} text-capitalize">
                                    {{ $recep->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay recepcionistas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registro Recepcionista Nativo Bootstrap -->
<div class="modal fade" id="modal_recep" tabindex="-1" aria-labelledby="modal_recep_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_recep_title">Registrar Nueva Recepcionista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('recepcionistas.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_recep" class="form-label fw-medium">Nombre Completo *</label>
                        <input type="text" id="txt_nombre_recep" name="nombre" class="form-control" placeholder="María López Hernández" required>
                    </div>

                    <div class="mb-3">
                        <label for="txt_email_recep" class="form-label fw-medium">Correo Institucional *</label>
                        <input type="email" id="txt_email_recep" name="email" class="form-control" placeholder="recepcion@clinicamedica.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="txt_curp_recep" class="form-label fw-medium">CURP *</label>
                            <input type="text" id="txt_curp_recep" name="curp" class="form-control text-uppercase" placeholder="18 caracteres" maxlength="18" required>
                        </div>
                        <div class="col-6">
                            <label for="txt_tel_recep" class="form-label fw-medium">Teléfono *</label>
                            <input type="tel" id="txt_tel_recep" name="telefono" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="txt_pass_recep" class="form-label fw-medium">Contraseña Inicial *</label>
                        <input type="password" id="txt_pass_recep" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="txt_pass_conf_recep" class="form-label fw-medium">Confirmar Contraseña *</label>
                        <input type="password" id="txt_pass_conf_recep" name="password_confirmation" class="form-control" placeholder="Repetir contraseña" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
