@extends('layouts.app')
@section('titulo', 'Gestión de Pacientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Gestión de Pacientes</h1>
</div>

<!-- Controls Bar -->
<div class="row g-3 mb-4 align-items-center">
    <div class="col-md-6 col-lg-5">
        <form method="GET" action="{{ route('pacientes.index') }}" class="input-group">
            <span class="input-group-text bg-white border-end-0"><i data-lucide="search" class="text-muted"></i></span>
            <input type="text" name="buscar" value="{{ $query }}" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, CURP o expediente...">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </form>
    </div>
    <div class="col-md-6 col-lg-7 text-md-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_paciente" onclick="prepararNuevoPaciente()">
            <i data-lucide="user-plus" class="me-1"></i> + Nuevo Paciente
        </button>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># Expediente</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pacientes as $paciente)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $paciente->numero_expediente ?? 'EXP-' . str_pad($paciente->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-semibold">{{ $paciente->usuario?->nombre ?? 'N/A' }}</td>
                            <td class="font-monospace small">{{ $paciente->usuario?->curp ?? 'N/A' }}</td>
                            <td>{{ $paciente->usuario?->telefono ?? 'N/A' }}</td>
                            <td>{{ $paciente->usuario?->email ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $estado = strtolower($paciente->usuario?->estado ?? 'activo');
                                    $badgeClass = match($estado) {
                                        'activo' => 'bg-success',
                                        'inactivo' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-capitalize">{{ $estado }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('pacientes.show', $paciente->id) }}" class="btn btn-outline-secondary" title="Ver Perfil">
                                        <i data-lucide="eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" title="Editar" onclick="editarPaciente({{ json_encode([
                                        'id' => $paciente->id,
                                        'nombre' => $paciente->usuario?->nombre,
                                        'fecha_nacimiento' => $paciente->fecha_nacimiento,
                                        'sexo' => $paciente->sexo,
                                        'curp' => $paciente->usuario?->curp,
                                        'telefono' => $paciente->usuario?->telefono,
                                        'email' => $paciente->usuario?->email,
                                        'direccion' => $paciente->direccion
                                    ]) }})">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('pacientes.desactivar', $paciente->id) }}" onsubmit="return confirm('¿Está seguro de que desea desactivar este paciente?');" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger" title="Desactivar">
                                            <i data-lucide="user-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron pacientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Mostrando {{ $pacientes->total() }} pacientes registrados</span>
        {{ $pacientes->links() }}
    </div>
</div>

<!-- Modal Nativo Bootstrap 5 -->
<div class="modal fade" id="modal_paciente" tabindex="-1" aria-labelledby="modal_paciente_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_paciente_title">Registrar Nuevo Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form_paciente_modal" method="POST" action="{{ route('pacientes.store') }}">
                @csrf
                <div id="method_field_container"></div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_pac" class="form-label fw-medium">Nombre Completo *</label>
                        <input type="text" id="txt_nombre_pac" name="nombre" class="form-control" placeholder="Ej: Juan García Hernández" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="inp_fecha_nac" class="form-label fw-medium">Fecha de Nacimiento *</label>
                            <input type="date" id="inp_fecha_nac" name="fecha_nacimiento" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sel_sexo" class="form-label fw-medium">Sexo *</label>
                            <select id="sel_sexo" name="sexo" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="txt_curp" class="form-label fw-medium">CURP *</label>
                            <input type="text" id="txt_curp" name="curp" class="form-control text-uppercase" placeholder="18 caracteres" maxlength="18" required>
                        </div>
                        <div class="col-md-6">
                            <label for="txt_telefono_pac" class="form-label fw-medium">Teléfono *</label>
                            <input type="tel" id="txt_telefono_pac" name="telefono" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="txt_email_pac" class="form-label fw-medium">Correo Electrónico *</label>
                            <input type="email" id="txt_email_pac" name="email" class="form-control" placeholder="paciente@email.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="txt_direccion" class="form-label fw-medium">Dirección</label>
                            <input type="text" id="txt_direccion" name="direccion" class="form-control" placeholder="Calle, número, colonia">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_paciente" class="btn btn-primary">Registrar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function prepararNuevoPaciente() {
        document.getElementById('form_paciente_modal').reset();
        document.getElementById('form_paciente_modal').action = "{{ route('pacientes.store') }}";
        document.getElementById('method_field_container').innerHTML = '';
        document.getElementById('modal_paciente_title').textContent = 'Registrar Nuevo Paciente';
        document.getElementById('btn_guardar_paciente').textContent = 'Registrar Paciente';
    }

    function editarPaciente(paciente) {
        document.getElementById('form_paciente_modal').action = "/pacientes/" + paciente.id;
        document.getElementById('method_field_container').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('txt_nombre_pac').value = paciente.nombre || '';
        document.getElementById('inp_fecha_nac').value = paciente.fecha_nacimiento || '';
        document.getElementById('sel_sexo').value = paciente.sexo || '';
        document.getElementById('txt_curp').value = paciente.curp || '';
        document.getElementById('txt_telefono_pac').value = paciente.telefono || '';
        document.getElementById('txt_email_pac').value = paciente.email || '';
        document.getElementById('txt_direccion').value = paciente.direccion || '';

        document.getElementById('modal_paciente_title').textContent = 'Editar Paciente';
        document.getElementById('btn_guardar_paciente').textContent = 'Guardar Cambios';

        const bsModal = new bootstrap.Modal(document.getElementById('modal_paciente'));
        bsModal.show();
    }
</script>
@endsection
