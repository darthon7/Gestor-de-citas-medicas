@extends('layouts.app')
@section('titulo', 'Gestión de Doctores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Gestión de Doctores</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_doctor">
        <i data-lucide="user-plus" class="me-1"></i> + Registrar Doctor
    </button>
</div>

<!-- Doctors Cards Grid -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    @forelse($doctores as $doc)
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; font-size: 18px;">
                        {{ strtoupper(substr($doc['nombre'] ?? 'D', 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="fw-bold mb-1 text-truncate">Dr. {{ $doc['nombre'] }}</h5>
                        <span class="badge bg-info text-dark">{{ $doc['especialidad'] ?? 'General' }}</span>
                    </div>
                </div>

                <div class="small text-secondary mb-3 d-flex flex-column gap-1">
                    <div>Cédula: <strong class="text-dark">{{ $doc['cedula'] ?? 'N/A' }}</strong></div>
                    <div>Teléfono: <strong class="text-dark">{{ $doc['telefono'] ?? 'N/A' }}</strong></div>
                    <div class="text-truncate">Correo: <strong class="text-dark">{{ $doc['email'] ?? 'N/A' }}</strong></div>
                    <div>
                        Validación:
                        @php
                            $valEstado = strtolower($doc['estado_validacion'] ?? 'pendiente');
                            $valBadge = match($valEstado) {
                                'validado' => 'bg-success',
                                'rechazado' => 'bg-danger',
                                default => 'bg-warning text-dark'
                            };
                        @endphp
                        <span class="badge {{ $valBadge }} text-capitalize ms-1">{{ $valEstado }}</span>
                    </div>
                </div>

                <div class="pt-3 border-top mt-auto d-flex gap-2">
                    <a href="{{ route('doctores.horarios', $doc['id']) }}" class="btn btn-sm btn-outline-primary flex-grow-1 text-center">
                        <i data-lucide="clock" class="me-1"></i> Horarios
                    </a>

                    @if(($doc['estado_validacion'] ?? '') !== 'validado')
                        <form method="POST" action="{{ route('doctores.validar', $doc['id']) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado_validacion" value="validado">
                            <button type="submit" class="btn btn-sm btn-success" title="Aprobar Doctor">
                                <i data-lucide="check"></i> Validar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p class="text-muted text-center py-4">No se encontraron médicos registrados.</p>
        </div>
    @endforelse
</div>

<!-- Modal Registrar Doctor Nativo Bootstrap -->
<div class="modal fade" id="modal_doctor" tabindex="-1" aria-labelledby="modal_doctor_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_doctor_title">Registrar Nuevo Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('doctores.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_doc" class="form-label fw-medium">Nombre Completo *</label>
                        <input type="text" id="txt_nombre_doc" name="nombre" class="form-control" placeholder="Ej: Roberto Sánchez" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sel_especialidad_doc" class="form-label fw-medium">Especialidad *</label>
                            <select id="sel_especialidad_doc" name="especialidad_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($especialidades as $esp)
                                    <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="txt_cedula" class="form-label fw-medium">Cédula Profesional *</label>
                            <input type="text" id="txt_cedula" name="cedula_profesional" class="form-control" placeholder="8 dígitos" maxlength="10" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="txt_telefono_doc" class="form-label fw-medium">Teléfono *</label>
                            <input type="tel" id="txt_telefono_doc" name="telefono" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                        </div>
                        <div class="col-md-6">
                            <label for="txt_email_doc" class="form-label fw-medium">Correo Electrónico *</label>
                            <input type="email" id="txt_email_doc" name="email" class="form-control" placeholder="doctor@clinicamedica.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="txt_password_doc" class="form-label fw-medium">Contraseña de Acceso *</label>
                        <input type="password" id="txt_password_doc" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
