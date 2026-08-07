@extends('layouts.app')
@section('titulo', 'Catálogo de Especialidades')

@section('content')
<!-- Header Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Catálogo de Especialidades</h1>
        <p class="text-xs text-text-secondary mt-0.5">Especialidades configuradas para los servicios del centro médico</p>
    </div>
    <button type="button" onclick="abrirModalEspecialidad()" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs flex items-center justify-center gap-2 shadow-md hover:bg-primary-dark active:scale-[0.99] transition-all">
        <span class="material-symbols-outlined text-lg">add</span>
        <span>Nueva Especialidad</span>
    </button>
</div>

<!-- Especialidades Table Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border overflow-hidden max-w-4xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-4"># ID</th>
                    <th class="px-6 py-4">Nombre de la Especialidad</th>
                    <th class="px-6 py-4">Descripción</th>
                    <th class="px-6 py-4 text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($especialidades as $esp)
                    <tr class="hover:bg-background/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary text-xs whitespace-nowrap">#{{ $esp['id'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-primary-light/40 text-primary-dark font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($esp['nombre'] ?? 'E', 0, 1)) }}
                                </div>
                                <span class="font-semibold text-text-primary text-xs">{{ $esp['nombre'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary">{{ $esp['descripcion'] ?? 'Sin descripción' }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                Activo
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-1 block text-text-muted">stethoscope</span>
                            No hay especialidades registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Especialidad -->
<div id="modal_especialidad" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Nueva Especialidad Médica</h3>
            <button type="button" onclick="cerrarModalEspecialidad()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('especialidades.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="txt_nombre_esp" class="text-xs font-semibold text-text-secondary block">Nombre de la Especialidad *</label>
                <input type="text" id="txt_nombre_esp" name="nombre" required placeholder="Ej: Cardiología, Pediatría" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="space-y-1">
                <label for="txt_desc_esp" class="text-xs font-semibold text-text-secondary block">Descripción</label>
                <input type="text" id="txt_desc_esp" name="descripcion" placeholder="Descripción opcional..." class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalEspecialidad()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalEspecialidad() { document.getElementById('modal_especialidad').classList.remove('hidden'); }
    function cerrarModalEspecialidad() { document.getElementById('modal_especialidad').classList.add('hidden'); }
</script>
@endsection