@extends('layouts.auth')
@section('titulo', 'Registro de Pacientes')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full max-w-xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-2xl bg-secondary/10 text-secondary flex items-center justify-center mb-3 border border-secondary/20 shadow-inner">
            <span class="material-symbols-outlined text-3xl">person_add</span>
        </div>
        <h1 class="text-2xl font-bold text-primary-dark tracking-tight">Crear Cuenta de Paciente</h1>
        <p class="text-xs text-text-secondary mt-1">Completa tus datos para agendar consultas en línea</p>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('registro') }}" class="w-full space-y-4">
        @csrf

        <!-- Full Name -->
        <div class="space-y-1">
            <label for="txt_nombre" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">person</span>
                <input type="text" id="txt_nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Escriba su nombre y apellidos" class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <!-- CURP & Email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_curp" class="text-xs font-semibold text-text-secondary block">CURP *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">badge</span>
                    <input type="text" id="txt_curp" name="curp" value="{{ old('curp') }}" maxlength="18" required placeholder="18 CARACTERES" class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="txt_email" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                    <input type="email" id="txt_email" name="email" value="{{ old('email') }}" required placeholder="ejemplo@correo.com" class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>
        </div>

        <!-- Phone & Birthdate -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_telefono" class="text-xs font-semibold text-text-secondary block">Teléfono *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">call</span>
                    <input type="tel" id="txt_telefono" name="telefono" value="{{ old('telefono') }}" maxlength="10" required placeholder="10 dígitos" class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="inp_fecha_nac" class="text-xs font-semibold text-text-secondary block">Fecha de Nacimiento</label>
                <input type="date" id="inp_fecha_nac" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <!-- Sexo & NSS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="sel_sexo" class="text-xs font-semibold text-text-secondary block">Sexo</label>
                <select id="sel_sexo" name="sexo" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                    <option value="">Seleccionar...</option>
                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                </select>
            </div>

            <div class="space-y-1">
                <label for="txt_nss" class="text-xs font-semibold text-text-secondary block">NSS (Opcional)</label>
                <input type="text" id="txt_nss" name="nss" value="{{ old('nss') }}" placeholder="Núm. Seguro Social" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <!-- Password & Confirm -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_password" class="text-xs font-semibold text-text-secondary block">Contraseña *</label>
                <input type="password" id="txt_password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>

            <div class="space-y-1">
                <label for="txt_password_confirm" class="text-xs font-semibold text-text-secondary block">Confirmar Contraseña *</label>
                <input type="password" id="txt_password_confirm" name="password_confirmation" required minlength="8" placeholder="Repite la contraseña" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2 mt-4">
            <span>Registrar Paciente</span>
            <span class="material-symbols-outlined text-xl">how_to_reg</span>
        </button>
    </form>

    <!-- Footer link -->
    <div class="mt-6 pt-4 border-t border-border w-full text-center space-y-2">
        <p class="text-xs text-text-secondary">
            ¿Ya tienes una cuenta? 
            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Inicia sesión aquí</a>
        </p>
        <p class="text-xs text-text-secondary">
            ¿Eres médico?
            <a href="{{ route('registro.doctor') }}" class="text-primary font-bold hover:underline">Regístrate como doctor aquí</a>
        </p>
    </div>
</div>
@endsection
