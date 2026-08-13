@extends('layouts.auth')
@section('titulo', 'Registro de Doctor')
@section('ancho', 'max-w-xl')

@section('content')
<div class="bg-surface rounded-3xl p-8 md:p-10 shadow-2xl border border-border flex flex-col items-center w-full">
    <!-- Brand Identity Logo -->
    <x-vida-logo class="mb-5" />

    {{-- Header --}}
    <div class="mb-4 flex flex-col items-center text-center">
        <h1 class="text-2xl font-bold font-funnel text-brand-heading tracking-tight">Solicitud de Registro Médico</h1>
        <p class="text-xs text-text-secondary mt-1 max-w-sm">
            Completa tu información profesional. Tu cuenta quedará <strong>pendiente de validación</strong>
            por el administrador antes de poder acceder al sistema.
        </p>
    </div>

    <!-- Línea de vida -->
    <div class="w-full flex items-center gap-3 mb-6" aria-hidden="true">
        <div class="h-px flex-1 bg-border"></div>
        <svg class="ecg-line h-5 w-24 text-brand-emerald" viewBox="0 0 96 20" fill="none">
            <path d="M0 10 H22 L27 6.5 L32 10 H40 L45 2.5 L50 17.5 L55 4.5 L59 10 H74 L79 7.5 L84 10 H96" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="h-px flex-1 bg-border"></div>
    </div>

    {{-- Aviso de flujo --}}
    <div class="w-full mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80">
        <div class="flex items-center gap-2 mb-2.5">
            <span class="material-symbols-outlined text-brand-emerald text-xl">info</span>
            <strong class="text-[11px] font-bold uppercase tracking-wider text-brand-emerald">Cómo funciona el proceso</strong>
        </div>
        <ol class="space-y-1.5">
            <li class="flex items-start gap-2.5 text-xs text-brand-muted">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-emerald/10 text-brand-emerald text-[10px] font-bold flex items-center justify-center mt-px">1</span>
                Llenas y envías este formulario.
            </li>
            <li class="flex items-start gap-2.5 text-xs text-brand-muted">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-emerald/10 text-brand-emerald text-[10px] font-bold flex items-center justify-center mt-px">2</span>
                El administrador recibe tu solicitud y la revisa.
            </li>
            <li class="flex items-start gap-2.5 text-xs text-brand-muted">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-emerald/10 text-brand-emerald text-[10px] font-bold flex items-center justify-center mt-px">3</span>
                Una vez aprobada, podrás iniciar sesión con tu correo y contraseña.
            </li>
        </ol>
    </div>

    {{-- Registration Form --}}
    <form id="form_registro_doctor" method="POST" action="{{ route('registro.doctor') }}" class="w-full space-y-4">
        @csrf

        {{-- ===== Datos personales ===== --}}
        <div class="flex items-center gap-2 pt-1">
            <span class="material-symbols-outlined text-brand-emerald text-lg">id_card</span>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-text-secondary">Datos personales</h2>
            <div class="h-px flex-1 bg-border"></div>
        </div>

        {{-- Nombre --}}
        <div class="space-y-1">
            <label for="txt_nombre_doc" class="text-xs font-semibold text-text-secondary block">Nombre Completo *</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">person</span>
                <input type="text" id="txt_nombre_doc" name="nombre" value="{{ old('nombre') }}" required autocomplete="name"
                       placeholder="Nombre y apellidos"
                       class="w-full pl-10 pr-4 py-2.5 bg-white border @error('nombre') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
            @error('nombre')
                <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
            @enderror
        </div>

        {{-- CURP & Email --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_curp_doc" class="text-xs font-semibold text-text-secondary block">CURP *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">badge</span>
                    <input type="text" id="txt_curp_doc" name="curp" value="{{ old('curp') }}" maxlength="18" required pattern="[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]" title="La CURP debe tener 18 caracteres con el formato oficial (4 letras, 6 dígitos, H/M, 5 letras, dígito/letra, dígito)"
                           placeholder="18 CARACTERES" autocomplete="off"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('curp') border-danger @else border-border @enderror rounded-xl text-sm uppercase text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                <p id="curp_hint" class="text-[11px] text-text-muted mt-1 transition-colors">0/18 caracteres</p>
                @error('curp')
                    <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_email_doc" class="text-xs font-semibold text-text-secondary block">Correo Electrónico *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">mail</span>
                    <input type="email" id="txt_email_doc" name="email" value="{{ old('email') }}" required autocomplete="email"
                           placeholder="doctor@ejemplo.com"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('email') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                @error('email')
                    <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Telefono --}}
        <div class="space-y-1">
            <label for="txt_tel_doc" class="text-xs font-semibold text-text-secondary block">Teléfono <span class="font-normal text-text-muted">(opcional)</span></label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">call</span>
                <input type="tel" id="txt_tel_doc" name="telefono" value="{{ old('telefono') }}" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" autocomplete="tel" title="El teléfono debe contener exactamente 10 dígitos"
                       placeholder="10 dígitos"
                       class="w-full pl-10 pr-4 py-2.5 bg-white border @error('telefono') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
            </div>
            @error('telefono')
                <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
            @enderror
        </div>

        {{-- ===== Información profesional ===== --}}
        <div class="flex items-center gap-2 pt-2">
            <span class="material-symbols-outlined text-brand-emerald text-lg">stethoscope</span>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-text-secondary">Información profesional</h2>
            <div class="h-px flex-1 bg-border"></div>
        </div>

        {{-- Cédulas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_cedula_prof" class="text-xs font-semibold text-text-secondary block">Cédula Profesional *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">assignment_ind</span>
                    <input type="text" id="txt_cedula_prof" name="cedula_profesional" value="{{ old('cedula_profesional') }}" required
                           maxlength="8" pattern="[0-9]{7,8}" inputmode="numeric" title="La cédula profesional debe contener de 7 a 8 dígitos numéricos" placeholder="Núm. cédula SEP"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('cedula_profesional') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                @error('cedula_profesional')
                    <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="txt_cedula_esp" class="text-xs font-semibold text-text-secondary block">Cédula de Especialidad <span class="font-normal text-text-muted">(opcional)</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">assignment_ind</span>
                    <input type="text" id="txt_cedula_esp" name="cedula_especialidad" value="{{ old('cedula_especialidad') }}"
                           maxlength="8" pattern="[0-9]{7,8}" inputmode="numeric" title="La cédula de especialidad debe contener de 7 a 8 dígitos numéricos" placeholder="Si aplica"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border @error('cedula_especialidad') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                </div>
                @error('cedula_especialidad')
                    <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
                @enderror
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
            @if($errors->has('especialidades') || $errors->has('especialidades.0'))
                <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $errors->first('especialidades') ?: $errors->first('especialidades.0') }}</p>
            @endif
        </div>

        {{-- ===== Seguridad ===== --}}
        <div class="flex items-center gap-2 pt-2">
            <span class="material-symbols-outlined text-brand-emerald text-lg">lock</span>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-text-secondary">Seguridad</h2>
            <div class="h-px flex-1 bg-border"></div>
        </div>

        {{-- Password --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label for="txt_pwd_doc" class="text-xs font-semibold text-text-secondary block">Contraseña *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                    <input type="password" id="txt_pwd_doc" name="password" required minlength="8" autocomplete="new-password"
                           placeholder="Mínimo 8 caracteres"
                           class="w-full pl-10 pr-11 py-2.5 bg-white border @error('password') border-danger @else border-border @enderror rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                    <button type="button" data-toggle-password="txt_pwd_doc" aria-label="Mostrar u ocultar contraseña" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-brand-emerald transition-colors">
                        <span class="material-symbols-outlined text-xl">visibility</span>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-danger mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="txt_pwd_doc_confirm" class="text-xs font-semibold text-text-secondary block">Confirmar Contraseña *</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xl">lock</span>
                    <input type="password" id="txt_pwd_doc_confirm" name="password_confirmation" required minlength="8" autocomplete="new-password"
                           placeholder="Repite la contraseña"
                           class="w-full pl-10 pr-11 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-brand-emerald focus:ring-2 focus:ring-brand-emerald/20 transition-all">
                    <button type="button" data-toggle-password="txt_pwd_doc_confirm" aria-label="Mostrar u ocultar contraseña" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-brand-emerald transition-colors">
                        <span class="material-symbols-outlined text-xl">visibility</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Checklist en vivo (informativo, la validación final es del servidor) --}}
        <ul class="grid grid-cols-2 gap-1 text-[11px]">
            <li id="hint_len" class="flex items-center gap-1 text-text-muted transition-colors">
                <span class="material-symbols-outlined text-sm">radio_button_unchecked</span> Mínimo 8 caracteres
            </li>
            <li id="hint_match" class="flex items-center gap-1 text-text-muted transition-colors">
                <span class="material-symbols-outlined text-sm">radio_button_unchecked</span> Las contraseñas coinciden
            </li>
        </ul>

        {{-- Submit --}}
        <button type="submit" data-loading-text="Enviando solicitud..."
                class="w-full bg-brand-emerald hover:bg-emerald-700 text-white py-3.5 px-6 rounded-full font-bold text-sm shadow-md hover:shadow-lg active:scale-[0.99] transition-all flex items-center justify-center space-x-2 mt-4 disabled:opacity-80 disabled:cursor-not-allowed disabled:hover:bg-brand-emerald">
            <span data-btn-text>Enviar Solicitud de Registro</span>
            <span data-btn-icon class="material-symbols-outlined text-xl">send</span>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mostrar / ocultar contraseñas
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.togglePassword);
                const icon  = btn.querySelector('.material-symbols-outlined');
                const mostrar = input.type === 'password';
                input.type = mostrar ? 'text' : 'password';
                icon.textContent = mostrar ? 'visibility_off' : 'visibility';
            });
        });

        // CURP: mayúsculas automáticas + conteo en vivo (informativo, no bloquea el envío)
        const curp = document.getElementById('txt_curp_doc');
        const curpHint = document.getElementById('curp_hint');
        if (curp && curpHint) {
            const actualizarCurp = () => {
                curp.value = curp.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18);
                const valida = /^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/.test(curp.value);
                curpHint.textContent = valida ? 'Formato de CURP válido' : curp.value.length + '/18 caracteres';
                curpHint.classList.toggle('text-brand-emerald', valida);
                curpHint.classList.toggle('font-semibold', valida);
                curpHint.classList.toggle('text-text-muted', !valida);
            };
            curp.addEventListener('input', actualizarCurp);
            actualizarCurp();
        }

        // Teléfono y cédulas: solo dígitos
        [['txt_tel_doc', 10], ['txt_cedula_prof', 8], ['txt_cedula_esp', 8]].forEach(([id, max]) => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => { el.value = el.value.replace(/\D/g, '').slice(0, max); });
        });

        // Checklist de contraseña en vivo
        const pwd = document.getElementById('txt_pwd_doc');
        const pwdConfirm = document.getElementById('txt_pwd_doc_confirm');
        const marcar = (id, ok) => {
            const li = document.getElementById(id);
            li.classList.toggle('text-brand-emerald', ok);
            li.classList.toggle('text-text-muted', !ok);
            li.querySelector('.material-symbols-outlined').textContent = ok ? 'check_circle' : 'radio_button_unchecked';
        };
        const revisarPassword = () => {
            marcar('hint_len', pwd.value.length >= 8);
            marcar('hint_match', pwdConfirm.value.length > 0 && pwd.value === pwdConfirm.value);
        };
        if (pwd && pwdConfirm) {
            pwd.addEventListener('input', revisarPassword);
            pwdConfirm.addEventListener('input', revisarPassword);
        }

        // Estado de carga al enviar (evita doble envío)
        const form = document.getElementById('form_registro_doctor');
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.querySelector('[data-btn-text]').textContent = btn.dataset.loadingText;
            const icon = btn.querySelector('[data-btn-icon]');
            icon.textContent = 'progress_activity';
            icon.classList.add('animate-spin');
        });
    });
</script>
@endsection
