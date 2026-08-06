@extends('layouts.app')
@section('titulo', 'Gestión de Doctores')

@section('content')
<!-- Action Header -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Gestión de Doctores</h1>
        <p class="text-xs text-text-secondary mt-0.5">Administra el directorio de personal médico y sus especialidades.</p>
    </div>

    <!-- Search & Filters -->
    <form method="GET" action="{{ route('doctores.index') }}" id="filter_form" class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg">search</span>
            <input type="text" name="buscar" value="{{ request()->query('buscar') }}" placeholder="Buscar doctor por nombre o especialidad..."
                   class="w-full min-w-[220px] sm:w-64 pl-10 pr-4 py-2 bg-surface border border-border rounded-xl text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
        </div>

        <div class="relative">
            <select name="especialidad_id" onchange="document.getElementById('filter_form').submit();" class="appearance-none pl-4 pr-10 py-2 bg-surface border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary cursor-pointer transition-all">
                <option value="">Especialidad</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp['id'] }}" {{ request()->query('especialidad_id') == $esp['id'] ? 'selected' : '' }}>
                        {{ $esp['nombre'] }}
                    </option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-text-muted text-lg">expand_more</span>
        </div>

        @if(request()->hasAny(['buscar', 'especialidad_id', 'estado_validacion']))
            <a href="{{ route('doctores.index') }}" class="px-3 py-2 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                Limpiar
            </a>
        @endif

        @if(Auth::user()->rol === 'admin')
        <button type="button" onclick="abrirModalDoctor()" class="bg-primary text-white px-5 py-2 rounded-xl font-semibold text-xs flex items-center gap-2 hover:bg-primary-dark transition-colors shadow-sm active:scale-95">
            <span class="material-symbols-outlined text-lg">add</span>
            <span>Registrar Doctor</span>
        </button>
        @endif
    </form>
</div>

<!-- Doctor Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($doctores as $doc)
        @php
            $nombre = $doc['usuario']['nombre'] ?? 'Médico';
            $nombreCompleto = preg_match('/^(dr|dra)\.?\s/i', $nombre) ? $nombre : 'Dr. ' . $nombre;
            $iniciales = strtoupper(substr(preg_replace('/\s+/', '', $nombre), 0, 2) ?: 'D');
            $valEstado = strtolower($doc['estado_validacion'] ?? 'pendiente');
            $inactivo = $valEstado === 'rechazado' || strtolower($doc['usuario']['estado'] ?? 'activo') === 'inactivo';
            $espNombres = collect($doc['especialidades'] ?? [])->pluck('nombre');
            $primeraEsp = $espNombres->first() ?? 'General';
            $valBadge = match($valEstado) {
                'validado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'rechazado' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-amber-50 text-amber-700 border-amber-200'
            };
            $valIcon = match($valEstado) {
                'validado' => 'verified_user',
                'rechazado' => 'gpp_bad',
                default => 'pending_actions'
            };
            $cardClass = $inactivo
                ? 'border border-error opacity-60'
                : 'border-l-4 border-secondary';
        @endphp

        <div class="relative bg-surface rounded-xl card-shadow card-shadow-hover transition-all {{ $cardClass }} flex flex-col items-center text-center p-6">
            <div class="w-16 h-16 rounded-full {{ $inactivo ? 'bg-surface-dim text-on-surface-variant' : 'bg-primary-light text-primary-dark' }} font-bold flex items-center justify-center mb-4 text-lg">
                {{ $iniciales }}
            </div>

            <h3 class="font-semibold text-[16px] text-text-primary mb-1 truncate w-full">{{ $nombreCompleto }}</h3>
            <span class="{{ $inactivo ? 'bg-surface-variant text-on-surface-variant' : 'bg-secondary-light text-on-secondary-container' }} px-3 py-1 rounded-full text-[12px] font-medium mb-2">
                {{ $primeraEsp }}
            </span>
            <p class="text-text-muted text-[12px] mb-4">Céd. Prof. {{ $doc['cedula_profesional'] ?? 'N/A' }}</p>

            <div class="flex items-center justify-center gap-4 mb-4">
                @if($inactivo)
                    <span class="text-error font-semibold text-caption uppercase tracking-wider">Inactivo</span>
                @else
                    @if($doc['usuario']['telefono'] ?? null)
                        <a href="tel:{{ $doc['usuario']['telefono'] }}" class="p-2 text-text-secondary hover:text-primary transition-colors" title="Llamar">
                            <span class="material-symbols-outlined text-xl">call</span>
                        </a>
                    @endif
                    @if($doc['usuario']['email'] ?? null)
                        <a href="mailto:{{ $doc['usuario']['email'] }}" class="p-2 text-text-secondary hover:text-primary transition-colors" title="Correo">
                            <span class="material-symbols-outlined text-xl">mail</span>
                        </a>
                    @endif
                @endif
            </div>

            <div class="flex items-center justify-center mb-4 min-h-[24px]">
                @if(!$inactivo)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $valBadge }} capitalize">
                        <span class="material-symbols-outlined text-sm">{{ $valIcon }}</span>
                        {{ $valEstado }}
                    </span>
                @endif
            </div>

            <div class="w-full border-t border-outline-variant pt-4 flex items-center justify-between gap-2">
                <a href="{{ route('doctores.horarios', $doc['id']) }}" class="flex items-center gap-1 text-primary font-medium text-caption hover:underline {{ $inactivo ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' }}">
                    <span class="material-symbols-outlined text-lg">calendar_month</span>
                    Disponibilidad
                </a>

                @if(Auth::user()->rol === 'admin' && !$inactivo && ($doc['estado_validacion'] ?? '') !== 'validado')
                    <form method="POST" action="{{ route('doctores.validar', $doc['id']) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado_validacion" value="validado">
                        <button type="submit" class="flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium text-caption" title="Aprobar Doctor">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            Validar
                        </button>
                    </form>
                @endif

                @if(Auth::user()->rol === 'admin')
                <button type="button" onclick="abrirEditarDoctor(this)"
                        data-id="{{ $doc['id'] }}"
                        data-nombre="{{ $doc['usuario']['nombre'] ?? '' }}"
                        data-email="{{ $doc['usuario']['email'] ?? '' }}"
                        data-telefono="{{ $doc['usuario']['telefono'] ?? '' }}"
                        data-cedula="{{ $doc['cedula_profesional'] ?? '' }}"
                        data-cedula-esp="{{ $doc['cedula_especialidad'] ?? '' }}"
                        data-especialidades="{{ collect($doc['especialidades'] ?? [])->pluck('id')->implode(',') }}"
                        class="flex items-center gap-1 text-warning-gold font-medium text-caption hover:underline" title="Editar perfil">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    Editar
                </button>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full bg-surface rounded-2xl card-shadow border border-border p-10 text-center text-xs text-text-muted">
            <span class="material-symbols-outlined text-4xl mb-2 block text-text-muted">medical_services</span>
            No se encontraron médicos registrados.
        </div>
    @endforelse
</div>

<!-- Modal Editar Doctor -->
<div id="modal_editar_doctor" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Editar Perfil de Doctor</h3>
            <button type="button" onclick="cerrarEditarDoctor()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <form id="form_editar_doctor" method="POST" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            @method('PUT')
            <div class="space-y-1">
                <label for="txt_nombre_doc_edit" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
                <input type="text" id="txt_nombre_doc_edit" name="nombre" required placeholder="Ej: Roberto Sánchez" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="sel_especialidad_doc_edit" class="text-xs font-semibold text-text-secondary block">Especialidad *</label>
                    <select id="sel_especialidad_doc_edit" name="especialidades[]" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="">Seleccione...</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="txt_cedula_doc_edit" class="text-xs font-semibold text-text-secondary block">Cédula Profesional *</label>
                    <input type="text" id="txt_cedula_doc_edit" name="cedula_profesional" required maxlength="10" placeholder="8 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_telefono_doc_edit" class="text-xs font-semibold text-text-secondary block">Teléfono *</label>
                    <input type="tel" id="txt_telefono_doc_edit" name="telefono" required maxlength="10" placeholder="10 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_email_doc_edit" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                    <input type="email" id="txt_email_doc_edit" name="email" required placeholder="doctor@clinicamedica.com" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="txt_cedula_esp_doc_edit" class="text-xs font-semibold text-text-secondary block">Cédula de Especialidad</label>
                <input type="text" id="txt_cedula_esp_doc_edit" name="cedula_especialidad" maxlength="10" placeholder="Opcional" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarEditarDoctor()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Doctor -->
<div id="modal_doctor" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Registrar Nuevo Doctor</h3>
            <button type="button" onclick="cerrarModalDoctor()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('doctores.store') }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div class="space-y-1">
                <label for="txt_nombre_doc" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
                <input type="text" id="txt_nombre_doc" name="nombre" required placeholder="Ej: Roberto Sánchez" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="sel_especialidad_doc" class="text-xs font-semibold text-text-secondary block">Especialidad *</label>
                    <select id="sel_especialidad_doc" name="especialidades[]" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="">Seleccione...</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="txt_cedula" class="text-xs font-semibold text-text-secondary block">Cédula Profesional *</label>
                    <input type="text" id="txt_cedula" name="cedula_profesional" required maxlength="10" placeholder="8 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_telefono_doc" class="text-xs font-semibold text-text-secondary block">Teléfono *</label>
                    <input type="tel" id="txt_telefono_doc" name="telefono" required maxlength="10" placeholder="10 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_email_doc" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                    <input type="email" id="txt_email_doc" name="email" required placeholder="doctor@clinicamedica.com" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="txt_password_doc" class="text-xs font-semibold text-text-secondary block">Contraseña de Acceso *</label>
                <input type="password" id="txt_password_doc" name="password" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalDoctor()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">
                    Registrar Doctor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalDoctor() {
        document.getElementById('modal_doctor').classList.remove('hidden');
    }
    function cerrarModalDoctor() {
        document.getElementById('modal_doctor').classList.add('hidden');
    }

    function abrirEditarDoctor(btn) {
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('form_editar_doctor');
        form.action = '/doctores/' + id;

        document.getElementById('txt_nombre_doc_edit').value = btn.getAttribute('data-nombre') || '';
        document.getElementById('txt_email_doc_edit').value = btn.getAttribute('data-email') || '';
        document.getElementById('txt_telefono_doc_edit').value = btn.getAttribute('data-telefono') || '';
        document.getElementById('txt_cedula_doc_edit').value = btn.getAttribute('data-cedula') || '';
        document.getElementById('txt_cedula_esp_doc_edit').value = btn.getAttribute('data-cedula-esp') || '';

        const espSel = document.getElementById('sel_especialidad_doc_edit');
        const espId = (btn.getAttribute('data-especialidades') || '').split(',').filter(Boolean)[0] || '';
        espSel.value = espId;

        document.getElementById('modal_editar_doctor').classList.remove('hidden');
    }

    function cerrarEditarDoctor() {
        document.getElementById('modal_editar_doctor').classList.add('hidden');
    }
</script>
@endsection