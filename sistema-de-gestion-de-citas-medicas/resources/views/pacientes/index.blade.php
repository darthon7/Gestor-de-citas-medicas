@extends('layouts.app')
@section('titulo', 'Gestión de Pacientes')

@section('content')
<!-- Header Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Gestión de Pacientes</h1>
        <p class="text-xs text-text-secondary mt-1">Administra el expediente e historial clínico de los pacientes</p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('pacientes.index') }}" class="relative w-full sm:w-[320px]">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">search</span>
            <input type="text" name="buscar" value="{{ $query }}" placeholder="Buscar por nombre, CURP o exp..." class="w-full pl-10 pr-4 py-2.5 bg-surface border border-border rounded-xl text-xs text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
        </form>

        <!-- New Patient Button -->
        <button type="button" onclick="prepararNuevoPaciente()" class="w-full sm:w-auto h-[42px] px-5 bg-primary text-white rounded-xl font-semibold text-xs flex items-center justify-center gap-2 shadow-md hover:bg-primary-dark active:scale-[0.99] transition-all">
            <span class="material-symbols-outlined text-lg">person_add</span>
            <span>Nuevo Paciente</span>
        </button>
    </div>
</div>

<!-- Pacientes Table Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-4"># Expediente</th>
                    <th class="px-6 py-4">Nombre Completo</th>
                    <th class="px-6 py-4">CURP</th>
                    <th class="px-6 py-4">Teléfono</th>
                    <th class="px-6 py-4">Correo</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($pacientes as $paciente)
                    <tr class="hover:bg-background/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary text-xs whitespace-nowrap">
                            {{ $paciente->numero_expediente ?? 'EXP-' . str_pad($paciente->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-light/40 text-primary-dark font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($paciente->usuario?->nombre ?? 'P', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-text-primary text-xs">{{ $paciente->usuario?->nombre ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-text-secondary uppercase whitespace-nowrap">
                            {{ $paciente->usuario?->curp ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary whitespace-nowrap">
                            {{ $paciente->usuario?->telefono ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary whitespace-nowrap">
                            {{ $paciente->usuario?->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estado = strtolower($paciente->usuario?->estado ?? 'activo');
                            @endphp
                            @if($estado === 'activo')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('pacientes.show', $paciente->id) }}" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Ver Expediente">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </a>
                                <button type="button" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Editar" onclick="editarPaciente({{ json_encode([
                                    'id' => $paciente->id,
                                    'nombre' => $paciente->usuario?->nombre,
                                    'fecha_nacimiento' => $paciente->fecha_nacimiento,
                                    'sexo' => $paciente->sexo,
                                    'curp' => $paciente->usuario?->curp,
                                    'telefono' => $paciente->usuario?->telefono,
                                    'email' => $paciente->usuario?->email,
                                    'direccion' => $paciente->direccion
                                ]) }})">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                @php
                                    $esActivo = ($paciente->usuario?->estado ?? 'activo') === 'activo';
                                @endphp
                                <form method="POST" action="{{ route('pacientes.desactivar', $paciente->id) }}" onsubmit="return confirm('¿Está seguro de que desea {{ $esActivo ? 'desactivar' : 'activar' }} este paciente?');" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-2 {{ $esActivo ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-colors" title="{{ $esActivo ? 'Desactivar paciente' : 'Activar paciente' }}">
                                        <span class="material-symbols-outlined text-lg">{{ $esActivo ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-1 block text-text-muted">person_search</span>
                            No se encontraron pacientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-surface border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
        <span class="text-xs text-text-secondary">Mostrando <strong>{{ $pacientes->total() }}</strong> pacientes en total</span>
        <div>{{ $pacientes->links() }}</div>
    </div>
</div>

<!-- Modal Registro / Edición Paciente -->
<div id="modal_paciente" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base" id="modal_paciente_title">Registrar Nuevo Paciente</h3>
            <button type="button" onclick="cerrarModalPaciente()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <form id="form_paciente_modal" method="POST" action="{{ route('pacientes.store') }}" class="p-6 space-y-4">
            @csrf
            <div id="method_field_container"></div>

            <div class="space-y-1">
                <label for="txt_nombre_pac" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
                <input type="text" id="txt_nombre_pac" name="nombre" required placeholder="Ej: Juan García Hernández" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="inp_fecha_nac" class="text-xs font-semibold text-text-secondary block">Fecha de Nacimiento *</label>
                    <input type="date" id="inp_fecha_nac" name="fecha_nacimiento" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="sel_sexo" class="text-xs font-semibold text-text-secondary block">Sexo *</label>
                    <select id="sel_sexo" name="sexo" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_curp" class="text-xs font-semibold text-text-secondary block">CURP *</label>
                    <input type="text" id="txt_curp" name="curp" maxlength="18" required placeholder="18 CARACTERES" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_telefono_pac" class="text-xs font-semibold text-text-secondary block">Teléfono *</label>
                    <input type="tel" id="txt_telefono_pac" name="telefono" maxlength="10" required placeholder="10 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_email_pac" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                    <input type="email" id="txt_email_pac" name="email" required placeholder="paciente@email.com" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_direccion" class="text-xs font-semibold text-text-secondary block">Dirección</label>
                    <input type="text" id="txt_direccion" name="direccion" placeholder="Calle, número, colonia" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalPaciente()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                    Cancelar
                </button>
                <button type="submit" id="btn_guardar_paciente" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">
                    Registrar Paciente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalPaciente() {
        document.getElementById('modal_paciente').classList.remove('hidden');
    }

    function cerrarModalPaciente() {
        document.getElementById('modal_paciente').classList.add('hidden');
    }

    function prepararNuevoPaciente() {
        document.getElementById('form_paciente_modal').reset();
        document.getElementById('form_paciente_modal').action = "{{ route('pacientes.store') }}";
        document.getElementById('method_field_container').innerHTML = '';
        document.getElementById('modal_paciente_title').textContent = 'Registrar Nuevo Paciente';
        document.getElementById('btn_guardar_paciente').textContent = 'Registrar Paciente';
        abrirModalPaciente();
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
        abrirModalPaciente();
    }
</script>
@endsection
