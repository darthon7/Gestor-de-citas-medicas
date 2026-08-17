@extends('layouts.app')
@section('titulo', 'Gestión de Recepcionistas')

@section('content')
<!-- Header Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Gestión de Recepcionistas</h1>
        <p class="text-xs text-text-secondary mt-0.5">Personal administrativo con permisos para agendar citas y gestionar pacientes</p>
    </div>
    <button type="button" onclick="abrirModalRecep()" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs flex items-center justify-center gap-2 shadow-md hover:bg-primary-dark active:scale-[0.99] transition-all">
        <span class="material-symbols-outlined text-lg">person_add</span>
        <span>Registrar Recepcionista</span>
    </button>
</div>

<!-- Recepcionistas Table Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-4">Nombre Completo</th>
                    <th class="px-6 py-4">Correo Institucional</th>
                    <th class="px-6 py-4">Teléfono / CURP</th>
                    <th class="px-6 py-4 text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($recepcionistas as $recep)
                    <tr class="hover:bg-background/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if(!empty($recep->foto_perfil))
                                    <img src="{{ asset('storage/' . $recep->foto_perfil) }}" alt="{{ $recep->nombre }}" class="w-9 h-9 rounded-full object-cover border border-primary/20 flex-shrink-0 bg-background">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-primary-light/40 text-primary-dark font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($recep->nombre ?? 'R', 0, 2)) }}
                                    </div>
                                @endif
                                <span class="font-semibold text-text-primary text-xs">{{ $recep->nombre }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary">{{ $recep->email }}</td>
                        <td class="px-6 py-4 text-xs text-text-secondary whitespace-nowrap">
                            {{ $recep->telefono ?? 'N/A' }} / <span class="font-mono uppercase">{{ $recep->curp ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($recep->estado === 'activo')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>
                                    {{ ucfirst($recep->estado) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-1 block text-text-muted">badge</span>
                            No hay recepcionistas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Registro Recepcionista -->
<div id="modal_recep" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Registrar Nueva Recepcionista</h3>
            <button type="button" onclick="cerrarModalRecep()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('recepcionistas.store') }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div class="space-y-1">
                <label for="txt_nombre_recep" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
                <input type="text" id="txt_nombre_recep" name="nombre" required placeholder="María López Hernández" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="space-y-1">
                <label for="txt_email_recep" class="text-xs font-semibold text-text-secondary block">Correo Institucional *</label>
                <input type="email" id="txt_email_recep" name="email" required placeholder="recepcion@clinicamedica.com" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_curp_recep" class="text-xs font-semibold text-text-secondary block">CURP *</label>
                    <input type="text" id="txt_curp_recep" name="curp" maxlength="18" required placeholder="18 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_tel_recep" class="text-xs font-semibold text-text-secondary block">Teléfono *</label>
                    <input type="tel" id="txt_tel_recep" name="telefono" maxlength="10" required placeholder="10 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_pass_recep" class="text-xs font-semibold text-text-secondary block">Contraseña Inicial *</label>
                    <input type="password" id="txt_pass_recep" name="password" minlength="8" required placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_pass_conf_recep" class="text-xs font-semibold text-text-secondary block">Confirmar Contraseña *</label>
                    <input type="password" id="txt_pass_conf_recep" name="password_confirmation" minlength="8" required placeholder="Repetir contraseña" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalRecep()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">Registrar Cuenta</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalRecep() { document.getElementById('modal_recep').classList.remove('hidden'); }
    function cerrarModalRecep() { document.getElementById('modal_recep').classList.add('hidden'); }
</script>
@endsection