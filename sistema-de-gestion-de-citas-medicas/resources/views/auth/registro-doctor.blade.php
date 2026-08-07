@extends('layouts.auth')
@section('titulo', 'Registro de Doctor')

@section('content')
<div class="bg-surface rounded-2xl p-8 md:p-10 shadow-xl border border-border flex flex-col items-center w-full max-w-xl">
    {{-- Header --}}
    <div class="mb-6 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-3 border border-primary/20 shadow-inner">
            <span class="material-symbols-outlined text-3xl">stethoscope</span>
        </div>
        <h1 class="text-2xl font-bold text-primary-dark tracking-tight">Solicitud de Registro Medico</h1>
        <p class="text-xs text-text-secondary mt-1 max-w-sm">
            Completa tu informacion profesional. Tu cuenta quedara <strong>pendiente de validacion</strong>
            por el administrador antes de poder acceder al sistema.
        </p>
    </div>

    {{-- Aviso de flujo --}}
    <div class="w-full mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
        <span class="material-symbols-outlined text-amber-600 text-xl flex-shrink-0 mt-0.5">info</span>
        <div class="text-xs text-amber-800 leading-relaxed">
            <strong class="block mb-1">Como funciona el proceso:</strong>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Llenas y envias este formulario.</li>
                <li>El administrador recibe tu solicitud y la revisa.</li>
                <li>Una vez aprobada, podras iniciar sesion con tu correo y contrasena.</li>
            </ol>
        </div>
    </div>

    {{-- Registration Form --}}
    <form method="POST" action="{{ route('registro.doctor') }}" class="w-full space-y-4">
        @csrf

        {{-- Nombre --}}
        <div class="space-y-1">
            <label for="txt_nombre_doc" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">person</span>
                <input type="text" id="txt_nombre_doc" name="nombre" value="{{ old('nombre') }}" required
                       placeholder="Nombre y apellidos"
                       class="w-full pl-10 pr-4 py-2.5 bg-white border @error('nombre') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            @error('nombre')
                <p class="text-xs text-danger mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- CURP & Email --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_curp_doc" class="text-xs font-semibold text-text-secondary block">CURP *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">badge</span>
                    <input type="text" id="txt_curp_doc" name="curp" value="{{ old('curp') }}" maxlength="18" required
                           placeholder="18 CARACTERES"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('curp') border-danger @else border-border @enderror rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                @error('curp')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_email_doc" class="text-xs font-semibold text-text-secondary block">Correo Electronico *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                    <input type="email" id="txt_email_doc" name="email" value="{{ old('email') }}" required
                           placeholder="doctor@ejemplo.com"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('email') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                @error('email')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Telefono --}}
        <div class="space-y-1">
            <label for="txt_tel_doc" class="text-xs font-semibold text-text-secondary block">Telefono (opcional)</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">call</span>
                <input type="tel" id="txt_tel_doc" name="telefono" value="{{ old('telefono') }}" maxlength="10"
                       placeholder="10 digitos"
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        {{-- Cedulas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_cedula_prof" class="text-xs font-semibold text-text-secondary block">Cedula Profesional *</label>
                <input type="text" id="txt_cedula_prof" name="cedula_profesional" value="{{ old('cedula_profesional') }}" required
                       maxlength="10" placeholder="Num. cedula SEP"
                       class="w-full px-4 py-2.5 bg-white border @error('cedula_profesional') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                @error('cedula_profesional')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_cedula_esp" class="text-xs font-semibold text-text-secondary block">Cedula de Especialidad <span class="font-normal text-text-muted">(opcional)</span></label>
                <input type="text" id="txt_cedula_esp" name="cedula_especialidad" value="{{ old('cedula_especialidad') }}"
                       maxlength="10" placeholder="Si aplica"
                       class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        {{-- Especialidad --}}
        <div class="space-y-1">
            <label for="sel_especialidad_doc" class="text-xs font-semibold text-text-secondary block">Especialidad Medica</label>
            <div class="relative">
                <select id="sel_especialidad_doc" name="especialidades[]"
                        class="w-full appearance-none px-4 pr-10 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all cursor-pointer">
                    <option value="">Seleccione su especialidad...</option>
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp->id }}" {{ old('especialidades.0') == $esp->id ? 'selected' : '' }}>
                            {{ $esp->nombre }}
                        </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-text-muted text-lg">expand_more</span>
            </div>
        </div>

        {{-- Password --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_pwd_doc" class="text-xs font-semibold text-text-secondary block">Contrasena *</label>
                <input type="password" id="txt_pwd_doc" name="password" required minlength="8"
                       placeholder="Minimo 8 caracteres"
                       class="w-full px-4 py-2.5 bg-white border @error('password') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                @error('password')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="txt_pwd_doc_confirm" class="text-xs font-semibold text-text-secondary block">Confirmar Contrasena *</label>
                <input type="password" id="txt_pwd_doc_confirm" name="password_confirmation" required minlength="8"
                       placeholder="Repite la contrasena"
                       class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 px-6 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2 mt-4">
            <span>Enviar Solicitud de Registro</span>
            <span class="material-symbols-outlined text-xl">send</span>
        </button>
    </form>

    {{-- Footer links --}}
    <div class="mt-6 pt-4 border-t border-border w-full flex flex-col items-center gap-2 text-center">
        <p class="text-xs text-text-secondary">
            Ya tienes cuenta aprobada?
            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Inicia sesion aqui</a>
        </p>
        <p class="text-xs text-text-secondary">
            Eres paciente?
            <a href="{{ route('registro') }}" class="text-primary font-bold hover:underline">Registrate como paciente</a>
        </p>
    </div>
</div>
@endsection
