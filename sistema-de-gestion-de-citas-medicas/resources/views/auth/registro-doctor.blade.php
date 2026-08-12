@extends('layouts.auth')
@section('titulo', 'Registro de Doctor')

@section('content')
<div class="bg-surface rounded-3xl p-8 md:p-10 shadow-2xl border border-border flex flex-col items-center w-full max-w-xl">
    <!-- Brand Identity Logo -->
    <a href="{{ route('landing') }}" class="mb-5 flex items-center gap-2.5 group">
        <div class="w-11 h-11 rounded-xl bg-emerald-100/70 p-1.5 flex items-center justify-center transition-transform group-hover:scale-105">
            <svg viewBox="0 0 38.717 33.301" class="w-8 h-8 overflow-visible">
                <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
            </svg>
        </div>
        <span class="text-3xl font-bold font-funnel tracking-tight text-brand-heading">
            Vida<span class="text-brand-emerald">+</span>
        </span>
    </a>

    {{-- Header --}}
    <div class="mb-6 flex flex-col items-center text-center">
        <h1 class="text-2xl font-bold font-funnel text-brand-heading tracking-tight">Solicitud de Registro Médico</h1>
        <p class="text-xs text-text-secondary mt-1 max-w-sm">
            Completa tu información profesional. Tu cuenta quedará <strong>pendiente de validación</strong>
            por el administrador antes de poder acceder al sistema.
        </p>
    </div>

    {{-- Aviso de flujo --}}
    <div class="w-full mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-start gap-3">
        <span class="material-symbols-outlined text-brand-emerald text-xl flex-shrink-0 mt-0.5">info</span>
        <div class="text-xs text-brand-heading leading-relaxed">
            <strong class="block mb-1 text-brand-emerald">Cómo funciona el proceso:</strong>
            <ol class="list-decimal list-inside space-y-0.5 text-brand-muted">
                <li>Llenas y envías este formulario.</li>
                <li>El administrador recibe tu solicitud y la revisa.</li>
                <li>Una vez aprobada, podrás iniciar sesión con tu correo y contraseña.</li>
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
                       class="w-full pl-10 pr-4 py-2.5 bg-white border @error('nombre') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
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
                    <input type="text" id="txt_curp_doc" name="curp" value="{{ old('curp') }}" maxlength="18" required pattern="[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]" title="La CURP debe tener 18 caracteres con el formato oficial (4 letras, 6 dígitos, H/M, 5 letras, dígito/letra, dígito)"
                           placeholder="18 CARACTERES"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('curp') border-danger @else border-border @enderror rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                @error('curp')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_email_doc" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                    <input type="email" id="txt_email_doc" name="email" value="{{ old('email') }}" required
                           placeholder="doctor@ejemplo.com"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('email') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                @error('email')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Telefono --}}
        <div class="space-y-1">
            <label for="txt_tel_doc" class="text-xs font-semibold text-text-secondary block">Teléfono (opcional)</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">call</span>
                <input type="tel" id="txt_tel_doc" name="telefono" value="{{ old('telefono') }}" maxlength="10" pattern="[0-9]{10}" title="El teléfono debe contener exactamente 10 dígitos"
                       placeholder="10 dígitos"
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
        </div>

        {{-- Cédulas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_cedula_prof" class="text-xs font-semibold text-text-secondary block">Cédula Profesional *</label>
                <input type="text" id="txt_cedula_prof" name="cedula_profesional" value="{{ old('cedula_profesional') }}" required
                       maxlength="10" pattern="[0-9]{7,8}" title="La cédula profesional debe contener de 7 a 8 dígitos numéricos" placeholder="Núm. cédula SEP"
                       class="w-full px-4 py-2.5 bg-white border @error('cedula_profesional') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                @error('cedula_profesional')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_cedula_esp" class="text-xs font-semibold text-text-secondary block">Cédula de Especialidad <span class="font-normal text-text-muted">(opcional)</span></label>
                <input type="text" id="txt_cedula_esp" name="cedula_especialidad" value="{{ old('cedula_especialidad') }}"
                       maxlength="10" pattern="[0-9]{7,8}" title="La cédula de especialidad debe contener de 7 a 8 dígitos numéricos" placeholder="Si aplica"
                       class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
        </div>

        {{-- Especialidad --}}
        <div class="space-y-1">
            <label for="sel_especialidad_doc" class="text-xs font-semibold text-text-secondary block">Especialidad Médica</label>
            <div class="relative">
                <select id="sel_especialidad_doc" name="especialidades[]"
                        class="w-full appearance-none px-4 pr-10 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all cursor-pointer">
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
                <label for="txt_pwd_doc" class="text-xs font-semibold text-text-secondary block">Contraseña *</label>
                <input type="password" id="txt_pwd_doc" name="password" required minlength="8"
                       placeholder="Mínimo 8 caracteres"
                       class="w-full px-4 py-2.5 bg-white border @error('password') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                @error('password')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="txt_pwd_doc_confirm" class="text-xs font-semibold text-text-secondary block">Confirmar Contraseña *</label>
                <input type="password" id="txt_pwd_doc_confirm" name="password_confirmation" required minlength="8"
                       placeholder="Repite la contraseña"
                       class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-brand-emerald hover:bg-emerald-700 text-white py-3.5 px-6 rounded-full font-bold text-sm shadow-md hover:shadow-lg active:scale-[0.99] transition-all flex items-center justify-center space-x-2 mt-4">
            <span>Enviar Solicitud de Registro</span>
            <span class="material-symbols-outlined text-xl">send</span>
        </button>
    </form>

    {{-- Footer links --}}
    <div class="mt-6 pt-4 border-t border-border w-full flex flex-col items-center gap-2 text-center">
        <p class="text-xs text-text-secondary">
            ¿Ya tienes cuenta aprobada?
            <a href="{{ route('login') }}" class="text-brand-emerald font-bold hover:underline">Inicia sesión aquí</a>
        </p>
        <p class="text-xs text-text-secondary">
            ¿Eres paciente?
            <a href="{{ route('registro') }}" class="text-brand-emerald font-bold hover:underline">Regístrate como paciente</a>
        </p>
        <p class="text-xs text-text-secondary pt-1">
            <a href="{{ route('landing') }}" class="text-text-muted hover:text-brand-emerald transition-colors inline-flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Volver a la página principal
            </a>
        </p>
    </div>
</div>
@endsection

