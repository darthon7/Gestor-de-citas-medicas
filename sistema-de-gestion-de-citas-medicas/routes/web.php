<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CitasWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\DoctoresWebController;
use App\Http\Controllers\Web\DoctorWebController;
use App\Http\Controllers\Web\EspecialidadesWebController;
use App\Http\Controllers\Web\LandingWebController;
use App\Http\Controllers\Web\PacientesWebController;
use App\Http\Controllers\Web\PerfilWebController;
use App\Http\Controllers\Web\RecepcionistasWebController;
use App\Http\Controllers\Web\ReportesWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web (Blade SSR) - Sistema de Gestión de Citas Médicas
|--------------------------------------------------------------------------
*/

// Landing Page Pública (no requiere autenticación)
Route::get('/inicio', [LandingWebController::class, 'index'])->name('landing');

// Ruta raíz: landing pública para visitantes / dashboard para usuarios autenticados
Route::get('/', [LandingWebController::class, 'home'])->name('dashboard');

// Página pública de Especialidades (sin autenticación)
Route::get('/especialidades-medicas', [LandingWebController::class, 'especialidades'])->name('especialidades.publicas');


// Rutas públicas de Autenticación (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);

    Route::get('/registro', [AuthWebController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [AuthWebController::class, 'registrar']);

    // Registro público de Doctor (solicitud pendiente de validación admin)
    Route::get('/registro-doctor', [AuthWebController::class, 'showRegistroDoctor'])->name('registro.doctor');
    Route::post('/registro-doctor', [AuthWebController::class, 'registrarDoctor']);

    Route::get('/recuperar-password', [AuthWebController::class, 'showRecuperar'])->name('recuperar');
    Route::post('/recuperar-password', [AuthWebController::class, 'solicitarRecuperacion']);

    Route::get('/verificar-codigo', [AuthWebController::class, 'showVerificarCodigo'])->name('verificar.codigo');
    Route::post('/verificar-codigo', [AuthWebController::class, 'verificarCodigo']);

    Route::get('/restablecer-password', [AuthWebController::class, 'showRestablecer'])->name('restablecer');
    Route::post('/restablecer-password', [AuthWebController::class, 'restablecerPassword']);
});

// Rutas Protegidas por Autenticación Web y Estado de Cuenta
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    // Mi Perfil
    Route::get('/mi-perfil', [PerfilWebController::class, 'index'])->name('perfil');
    Route::put('/mi-perfil', [PerfilWebController::class, 'update'])->name('perfil.update');
    Route::post('/cambiar-password', [PerfilWebController::class, 'cambiarPassword'])->name('perfil.password');
    Route::post('/actualizar-foto', [PerfilWebController::class, 'actualizarFoto'])->name('perfil.foto');

    // Módulo de Consulta de Citas (Admin, Recepcionista, Doctor, Paciente)
    Route::middleware(['role:admin,recepcionista,doctor,paciente'])->group(function () {
        Route::get('/citas', [CitasWebController::class, 'index'])->name('citas.index');
        Route::get('/citas/{id}', [CitasWebController::class, 'show'])->name('citas.show');
    });

    // Módulo de Agendamiento y Cancelación de Citas (Admin, Recepcionista, Paciente)
    Route::middleware(['role:admin,recepcionista,paciente'])->group(function () {
        Route::get('/citas/agendar', [CitasWebController::class, 'crear'])->name('citas.crear');
        Route::post('/citas', [CitasWebController::class, 'store'])->name('citas.store');
        Route::patch('/citas/{id}/cancelar', [CitasWebController::class, 'cancelar'])->name('citas.cancelar');
    });

    // Módulo de Pacientes & Gestión de Citas (Admin y Recepcionista)
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        // Pacientes
        Route::get('/pacientes', [PacientesWebController::class, 'index'])->name('pacientes.index');
        Route::post('/pacientes', [PacientesWebController::class, 'store'])->name('pacientes.store');
        Route::get('/pacientes/{id}', [PacientesWebController::class, 'show'])->name('pacientes.show');
        Route::put('/pacientes/{id}', [PacientesWebController::class, 'update'])->name('pacientes.update');
        Route::patch('/pacientes/{id}/desactivar', [PacientesWebController::class, 'desactivar'])->name('pacientes.desactivar');

        // Citas (Gestión avanzada)
        Route::put('/citas/{id}/reprogramar', [CitasWebController::class, 'reprogramar'])->name('citas.reprogramar');
        Route::patch('/citas/{id}/checkin', [CitasWebController::class, 'checkIn'])->name('citas.checkin');
    });

    // Módulo de Doctores: consulta (Admin y Recepcionista)
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        Route::get('/doctores', [DoctoresWebController::class, 'index'])->name('doctores.index');
        Route::get('/doctores/{id}/horarios', [DoctoresWebController::class, 'horarios'])->name('doctores.horarios');
        Route::post('/doctores/{id}/horarios', [DoctoresWebController::class, 'storeHorario'])->name('horarios.store');
        Route::put('/horarios/{id}', [DoctoresWebController::class, 'updateHorario'])->name('horarios.update');
        Route::delete('/horarios/{id}', [DoctoresWebController::class, 'deleteHorario'])->name('horarios.destroy');
        Route::post('/doctores/{id}/bloqueos', [DoctoresWebController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/bloqueos/{id}', [DoctoresWebController::class, 'deleteBloqueo'])->name('bloqueos.destroy');
    });

    // Rutas Exclusivas de Administrador
    Route::middleware(['role:admin'])->group(function () {
        // Doctores (escritura)
        Route::post('/doctores', [DoctoresWebController::class, 'store'])->name('doctores.store');
        Route::put('/doctores/{id}', [DoctoresWebController::class, 'update'])->name('doctores.update');
        Route::patch('/doctores/{id}/validar', [DoctoresWebController::class, 'validar'])->name('doctores.validar');

        // Especialidades
        Route::get('/especialidades', [EspecialidadesWebController::class, 'index'])->name('especialidades.index');
        Route::post('/especialidades', [EspecialidadesWebController::class, 'store'])->name('especialidades.store');

        // Recepcionistas
        Route::get('/recepcionistas', [RecepcionistasWebController::class, 'index'])->name('recepcionistas.index');
        Route::post('/recepcionistas', [RecepcionistasWebController::class, 'store'])->name('recepcionistas.store');

        // Reportes
        Route::get('/reportes', [ReportesWebController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar/{tipo}', [ReportesWebController::class, 'exportar'])->name('reportes.exportar');
    });

    // Rutas Exclusivas de Médico
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/mi-agenda', [DoctorWebController::class, 'agenda'])->name('doctor.agenda');
        Route::get('/mi-horario', [DoctorWebController::class, 'horario'])->name('doctor.horario');
        Route::post('/mi-horario', [DoctorWebController::class, 'storeHorario'])->name('doctor.horario.store');
        Route::put('/mi-horario/{id}', [DoctorWebController::class, 'updateHorario'])->name('doctor.horario.update');
        Route::delete('/mi-horario/{id}', [DoctorWebController::class, 'deleteHorario'])->name('doctor.horario.destroy');

        Route::get('/diagnostico/{citaId}', [DoctorWebController::class, 'diagnostico'])->name('doctor.diagnostico');
        Route::patch('/citas/{id}/iniciar', [DoctorWebController::class, 'iniciarConsulta'])->name('citas.iniciar');
        Route::patch('/citas/{id}/completar', [DoctorWebController::class, 'completarCita'])->name('citas.completar');
        Route::post('/citas/{citaId}/nota', [DoctorWebController::class, 'registrarNota'])->name('notas.store');
    });
});
