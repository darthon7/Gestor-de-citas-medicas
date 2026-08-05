<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BloqueosController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\HorariosController;
use App\Http\Controllers\NotasConsultaController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API - Sistema de Gestión de Citas Médicas
|--------------------------------------------------------------------------
*/

// Rutas Públicas de Autenticación y Registro
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/registrarPaciente', [AuthController::class, 'registrarPaciente']);
    Route::post('/registrarMedico', [AuthController::class, 'registrarMedico']);
    Route::post('/solicitarRecuperacion', [AuthController::class, 'solicitarRecuperacion']);
    Route::post('/verificarCodigo', [AuthController::class, 'verificarCodigo']);
    Route::post('/restablecerPassword', [AuthController::class, 'restablecerPassword']);
});

// Especialidades (Público / Consulta general)
Route::get('/obtenerEspecialidades', [EspecialidadesController::class, 'obtenerEspecialidades']);
Route::get('/obtenerDoctores', [DoctoresController::class, 'obtenerDoctores']);
Route::get('/obtenerDoctor/{id}', [DoctoresController::class, 'obtenerDoctor']);
Route::get('/obtenerDisponibilidad/{doctorId}', [DisponibilidadController::class, 'obtenerDisponibilidad']);

// Rutas Protegidas por Sanctum y Estado de Cuenta
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {

    // Auth
    Route::post('/auth/cerrarSesion', [AuthController::class, 'cerrarSesion']);

    // Mi Perfil y Funciones de Paciente Móvil
    Route::get('/miPerfil', [PerfilController::class, 'miPerfil']);
    Route::put('/actualizarMiPerfil', [PerfilController::class, 'actualizarMiPerfil']);
    Route::post('/cambiarPassword', [PerfilController::class, 'cambiarPassword']);
    Route::post('/actualizarFoto', [PerfilController::class, 'actualizarFoto']);

    // Rutas para Paciente (Móvil)
   Route::middleware(['role:paciente'])->group(function () {
        Route::get('/misCitas', [CitasController::class, 'misCitas']);
        Route::post('/agendarCita', [CitasController::class, 'agendarCita']);
        Route::get('/miCita/{id}', [CitasController::class, 'miCita']);
        Route::patch('/cancelarMiCita/{id}', [CitasController::class, 'cancelarMiCita']);
        Route::get('/miHistorial', [PerfilController::class, 'miHistorial']);
        Route::get('/miConsulta/{id}', [PerfilController::class, 'miConsulta']);
    });
       
    // Rutas para Médico
    Route::middleware(['role:doctor'])->group(function () {
        Route::post('/registrarNota/{citaId}', [NotasConsultaController::class, 'registrarNota']);
        Route::get('/obtenerNotas/{citaId}', [NotasConsultaController::class, 'obtenerNotas']);
        Route::patch('/iniciarConsulta/{id}', [CitasController::class, 'iniciarConsulta']);
        Route::patch('/completarCita/{id}', [CitasController::class, 'completarCita']);
    });

    // Rutas para Recepcionista y Administrador
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        // Pacientes
        Route::get('/obtenerPacientes', [PacientesController::class, 'obtenerPacientes']);
        Route::post('/registrarPaciente', [PacientesController::class, 'registrarPaciente']);
        Route::get('/obtenerPaciente/{id}', [PacientesController::class, 'obtenerPaciente']);
        Route::put('/actualizarPaciente/{id}', [PacientesController::class, 'actualizarPaciente']);
        Route::patch('/desactivarPaciente/{id}', [PacientesController::class, 'desactivarPaciente']);

        // Citas (Gestión completa)
        Route::get('/obtenerCitas', [CitasController::class, 'obtenerCitas']);
        Route::post('/registrarCita', [CitasController::class, 'registrarCita']);
        Route::get('/obtenerCita/{id}', [CitasController::class, 'obtenerCita']);
        Route::put('/reprogramarCita/{id}', [CitasController::class, 'reprogramarCita']);
        Route::patch('/cancelarCita/{id}', [CitasController::class, 'cancelarCita']);
        Route::patch('/checkInCita/{id}', [CitasController::class, 'checkInCita']);
    });

    // Rutas Exclusivas de Administrador
    Route::middleware(['role:admin'])->group(function () {
        // Gestión de Recepcionistas
        Route::post('/auth/registrarRecepcionista', [AuthController::class, 'registrarRecepcionista']);

        // Gestión de Doctores y Validación
        Route::post('/registrarDoctor', [DoctoresController::class, 'registrarDoctor']);
        Route::put('/actualizarDoctor/{id}', [DoctoresController::class, 'actualizarDoctor']);
        Route::patch('/validarDoctor/{id}', [DoctoresController::class, 'validarDoctor']);

        // Especialidades
        Route::post('/registrarEspecialidad', [EspecialidadesController::class, 'registrarEspecialidad']);

        // Horarios del Doctor
        Route::get('/obtenerHorarios/{doctorId}', [HorariosController::class, 'obtenerHorarios']);
        Route::post('/registrarHorario/{doctorId}', [HorariosController::class, 'registrarHorario']);
        Route::put('/actualizarHorario/{id}', [HorariosController::class, 'actualizarHorario']);
        Route::delete('/eliminarHorario/{id}', [HorariosController::class, 'eliminarHorario']);

        // Bloqueos de Horario
        Route::get('/obtenerBloqueos/{doctorId}', [BloqueosController::class, 'obtenerBloqueos']);
        Route::post('/registrarBloqueo/{doctorId}', [BloqueosController::class, 'registrarBloqueo']);
        Route::delete('/eliminarBloqueo/{id}', [BloqueosController::class, 'eliminarBloqueo']);

        // Reportes y Estadísticas
        Route::get('/reporteCitas', [ReportesController::class, 'reporteCitas']);
        Route::get('/reporteDoctores', [ReportesController::class, 'reporteDoctores']);
        Route::get('/reporteEspecialidades', [ReportesController::class, 'reporteEspecialidades']);
        Route::get('/reportePacientes', [ReportesController::class, 'reportePacientes']);
        Route::get('/resumenDiario', [ReportesController::class, 'resumenDiario']);
        Route::get('/exportarReporte/{tipo}', [ReportesController::class, 'exportarReporte']);
    });
});
