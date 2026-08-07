@extends('layouts.app')
@section('titulo', 'Mi Perfil')

@section('content')
<!-- Header Controls -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary-dark">Configuración de Mi Perfil</h1>
    <p class="text-xs text-text-secondary mt-0.5">Actualiza tus datos personales y seguridad de acceso</p>
</div>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Profile Hero Header -->
    <div class="rounded-2xl p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="background: linear-gradient(135deg, #0F4C6B 0%, #1B6B93 100%);">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/10 border-2 border-white/30 text-white font-bold flex items-center justify-center text-2xl flex-shrink-0">
                {{ strtoupper(substr($usuario->nombre ?? 'U', 0, 2)) }}
            </div>
            <div>
                <h2 class="font-bold text-lg">{{ $usuario->nombre }}</h2>
                <span class="inline-block mt-1 px-3 py-0.5 rounded-full bg-white/10 border border-white/20 text-xs font-semibold capitalize">{{ $usuario->rol }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('perfil.foto') }}" enctype="multipart/form-data" class="self-start sm:self-auto">
            @csrf
            <label for="inp_foto_perfil" class="px-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-xs font-semibold cursor-pointer hover:bg-white/20 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">photo_camera</span>
                <span>Actualizar Foto</span>
            </label>
            <input type="file" id="inp_foto_perfil" name="foto" accept="image/*" class="hidden" onchange="this.form.submit()">
        </form>
    </div>

    <!-- Personal Info Form -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6">
        <h3 class="font-bold text-text-primary text-base mb-5 pb-3 border-b border-border flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">badge</span>
            <span>Datos Personales</span>
        </h3>
        <form method="POST" action="{{ route('perfil.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-text-secondary block">Nombre Completo</label>
                    <input type="text" value="{{ $usuario->nombre }}" disabled class="w-full px-4 py-2.5 bg-background border border-border rounded-xl text-sm text-text-secondary cursor-not-allowed">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-text-secondary block">Correo Electrónico</label>
                    <input type="email" value="{{ $usuario->email }}" disabled class="w-full px-4 py-2.5 bg-background border border-border rounded-xl text-sm text-text-secondary cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_telefono" class="text-xs font-semibold text-text-secondary block">Teléfono de Contacto *</label>
                    <input type="tel" id="txt_telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" maxlength="10" required placeholder="10 dígitos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_curp" class="text-xs font-semibold text-text-secondary block">CURP</label>
                    <input type="text" id="txt_curp" name="curp" value="{{ old('curp', $usuario->curp) }}" placeholder="18 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="pt-4 border-t border-border flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    <span>Actualizar Datos</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Password Change Form -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6">
        <h3 class="font-bold text-danger text-base mb-5 pb-3 border-b border-border flex items-center gap-2">
            <span class="material-symbols-outlined text-xl">lock</span>
            <span>Cambiar Contraseña</span>
        </h3>
        <form method="POST" action="{{ route('perfil.password') }}" class="space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="txt_pass_actual" class="text-xs font-semibold text-text-secondary block">Contraseña Actual *</label>
                <input type="password" id="txt_pass_actual" name="password_actual" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="txt_pass_nueva" class="text-xs font-semibold text-text-secondary block">Nueva Contraseña *</label>
                    <input type="password" id="txt_pass_nueva" name="password" minlength="8" required placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="txt_pass_conf" class="text-xs font-semibold text-text-secondary block">Confirmar Nueva Contraseña *</label>
                    <input type="password" id="txt_pass_conf" name="password_confirmation" minlength="8" required placeholder="Repetir contraseña" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
                </div>
            </div>
            <div class="pt-4 border-t border-border flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl border border-danger text-danger text-xs font-semibold hover:bg-danger-light/50 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">key</span>
                    <span>Cambiar Contraseña</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection