@extends('layouts.app')
@section('titulo', 'Catálogo de Especialidades')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Catálogo de Especialidades</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_especialidad">
        <i data-lucide="plus" class="me-1"></i> Nueva Especialidad
    </button>
</div>

<p class="text-secondary mb-4">Especialidades configuradas para los servicios del centro médico.</p>

<div class="card border-0 shadow-sm rounded-3" style="max-width: 840px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># ID</th>
                        <th>Nombre de la Especialidad</th>
                        <th>Descripción</th>
                        <th class="text-end pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($especialidades as $esp)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">#{{ $esp['id'] }}</td>
                            <td class="fw-semibold">{{ $esp['nombre'] }}</td>
                            <td class="text-secondary small">{{ $esp['descripcion'] ?? 'Sin descripción' }}</td>
                            <td class="text-end pe-3">
                                <span class="badge bg-success">Activo</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay especialidades registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Especialidad Nativo Bootstrap -->
<div class="modal fade" id="modal_especialidad" tabindex="-1" aria-labelledby="modal_esp_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_esp_title">Nueva Especialidad Médica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('especialidades.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_esp" class="form-label fw-medium">Nombre de la Especialidad *</label>
                        <input type="text" id="txt_nombre_esp" name="nombre" class="form-control" placeholder="Ej: Cardiología, Pediatría" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_desc_esp" class="form-label fw-medium">Descripción</label>
                        <input type="text" id="txt_desc_esp" name="descripcion" class="form-control" placeholder="Descripción opcional...">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
