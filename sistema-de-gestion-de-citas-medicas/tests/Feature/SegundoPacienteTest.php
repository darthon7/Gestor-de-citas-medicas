<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\HorarioDoctor;
use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegundoPacienteTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $pacienteUser;
    protected PerfilPaciente $pacientePerfil;
    protected Usuario $doctorUser;
    protected PerfilDoctor $doctorPerfil;
    protected Especialidad $especialidad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = Usuario::where('email', 'admin@citasmedicas.com')->first();

        // Crear doctor validado
        $this->doctorUser = Usuario::create([
            'nombre'   => 'Dr. Test Roles',
            'email'    => 'doctest@test.com',
            'password' => bcrypt('Doc1234!'),
            'curp'     => 'DOCT900101HDFXXX99',
            'rol'      => 'doctor',
            'estado'   => 'activo',
        ]);
        $this->doctorPerfil = PerfilDoctor::create([
            'usuario_id'         => $this->doctorUser->id,
            'cedula_profesional' => '9999999',
            'estado_validacion'  => 'validado',
        ]);
        $this->especialidad = Especialidad::first();
        $this->doctorPerfil->especialidades()->attach($this->especialidad->id);
        HorarioDoctor::create([
            'perfil_doctor_id'          => $this->doctorPerfil->id,
            'dia_semana'                => 'lunes',
            'hora_inicio'               => '08:00:00',
            'hora_fin'                  => '12:00:00',
            'duracion_consulta_minutos' => 30,
            'activo'                    => true,
        ]);

        // Crear paciente
        $this->pacienteUser = Usuario::create([
            'nombre'   => 'Paciente Seguridad',
            'email'    => 'pseg@test.com',
            'password' => bcrypt('Pac1234!'),
            'curp'     => 'PACS900101HDFXXX99',
            'rol'      => 'paciente',
            'estado'   => 'activo',
        ]);
        $this->pacientePerfil = PerfilPaciente::create([
            'usuario_id'        => $this->pacienteUser->id,
            'numero_expediente' => 'EXP-20260101-9999',
        ]);
    }

    // =========================================================
    // MIDDLEWARE Y AUTORIZACIÓN
    // =========================================================

    public function test_usuario_no_autenticado_no_puede_acceder_a_rutas_protegidas(): void
    {
        $response = $this->getJson('/api/miPerfil');
        $response->assertStatus(401);
    }

    public function test_paciente_no_puede_acceder_a_rutas_de_admin(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->getJson('/api/obtenerCitas');
        $response->assertStatus(403);
    }

    public function test_paciente_no_puede_acceder_a_rutas_de_doctor(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->patchJson("/api/iniciarConsulta/1");
        $response->assertStatus(403);
    }

    public function test_doctor_no_puede_acceder_a_rutas_de_admin(): void
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')
            ->getJson('/api/obtenerCitas');
        $response->assertStatus(403);
    }

    public function test_usuario_bloqueado_con_token_no_puede_operar(): void
    {
        // Obtenemos token antes del bloqueo
        $this->pacienteUser->update(['estado' => 'bloqueado', 'bloqueado_hasta' => Carbon::now()->addMinutes(15)]);

        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->getJson('/api/miPerfil');
        $response->assertStatus(403);
    }

    public function test_usuario_inactivo_con_token_no_puede_operar(): void
    {
        $this->pacienteUser->update(['estado' => 'inactivo']);

        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->getJson('/api/miPerfil');
        $response->assertStatus(403);
    }

    // =========================================================
    // LOGIN — ESCENARIOS NEGATIVOS
    // =========================================================

    public function test_login_con_email_inexistente_retorna_401(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'noexiste@nada.com',
            'password' => 'cualquiera',
        ]);
        $response->assertStatus(401)
            ->assertJsonPath('mensaje', 'Las credenciales ingresadas son incorrectas');
    }

    public function test_login_con_cuenta_inactiva_retorna_403(): void
    {
        $this->admin->update(['estado' => 'inactivo']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'Admin1234!',
        ]);
        $response->assertStatus(403)
            ->assertJsonPath('mensaje', 'Tu cuenta está desactivada. Contacta al administrador.');
    }

    public function test_login_con_cuenta_ya_bloqueada_retorna_403(): void
    {
        $this->admin->update([
            'estado'          => 'bloqueado',
            'bloqueado_hasta' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'Admin1234!',
        ]);
        $response->assertStatus(403);
    }

    public function test_login_validacion_retorna_422_con_clave_mensaje(): void
    {
        // Sin enviar ningún dato — debe fallar validación con clave 'mensaje'
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422)
            ->assertJsonStructure(['mensaje', 'errors']);
    }

    public function test_login_doctor_no_validado_retorna_403(): void
    {
        $this->doctorPerfil->update(['estado_validacion' => 'pendiente']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'doctest@test.com',
            'password' => 'Doc1234!',
        ]);
        $response->assertStatus(403)
            ->assertJsonPath('mensaje', 'Tu cuenta de médico está pendiente de validación por el administrador.');
    }

    // =========================================================
    // RECUPERACIÓN DE CONTRASEÑA — ESCENARIOS NEGATIVOS
    // =========================================================

    public function test_recuperacion_con_codigo_incorrecto_retorna_valido_false(): void
    {
        // Primero solicitar código legítimo
        $this->postJson('/api/auth/solicitarRecuperacion', [
            'email' => 'admin@citasmedicas.com',
        ]);

        // Verificar con código incorrecto
        $response = $this->postJson('/api/auth/verificarCodigo', [
            'email'  => 'admin@citasmedicas.com',
            'codigo' => '000000',
        ]);
        $response->assertStatus(400)
            ->assertJsonPath('valido', false)
            ->assertJsonPath('mensaje', 'El código de verificación es incorrecto.');
    }

    public function test_recuperacion_con_codigo_expirado_retorna_valido_false(): void
    {
        // Insertar código ya expirado (hace 31 minutos)
        \Illuminate\Support\Facades\DB::table('password_resets')->insert([
            'email'      => 'admin@citasmedicas.com',
            'codigo'     => '123456',
            'created_at' => Carbon::now()->subMinutes(31),
        ]);

        $response = $this->postJson('/api/auth/verificarCodigo', [
            'email'  => 'admin@citasmedicas.com',
            'codigo' => '123456',
        ]);
        $response->assertStatus(400)
            ->assertJsonPath('valido', false)
            ->assertJsonPath('mensaje', 'El código de verificación ha expirado. Solicita uno nuevo.');
    }

    public function test_recuperacion_email_no_registrado_retorna_422(): void
    {
        $response = $this->postJson('/api/auth/solicitarRecuperacion', [
            'email' => 'noexiste@test.com',
        ]);
        $response->assertStatus(422);
    }

    // =========================================================
    // CITAS — ESCENARIOS NEGATIVOS
    // =========================================================

    public function test_paciente_no_puede_ver_cita_de_otro_paciente(): void
    {
        $proximoLunes = now()->next('Monday')->format('Y-m-d');

        // Admin registra cita para el paciente
        $otroPaciente = Usuario::create([
            'nombre'   => 'Otro Paciente',
            'email'    => 'otro@test.com',
            'password' => bcrypt('Otro1234!'),
            'curp'     => 'OTRO900101HDFXXX01',
            'rol'      => 'paciente',
            'estado'   => 'activo',
        ]);
        $otroPerfil = PerfilPaciente::create([
            'usuario_id'        => $otroPaciente->id,
            'numero_expediente' => 'EXP-20260101-0099',
        ]);

        $respCita = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarCita', [
                'perfil_paciente_id' => $otroPerfil->id,
                'perfil_doctor_id'   => $this->doctorPerfil->id,
                'especialidad_id'    => $this->especialidad->id,
                'fecha_cita'         => $proximoLunes,
                'hora_cita'          => '08:00:00',
            ]);
        $citaId = $respCita->json('data.id');

        // El pacienteUser intenta ver la cita que no le pertenece
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->getJson("/api/miCita/{$citaId}");
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cita no encontrada');
    }

    // =========================================================
    // PERFIL — TESTS BÁSICOS
    // =========================================================

    public function test_mi_perfil_retorna_datos_del_usuario_autenticado(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->getJson('/api/miPerfil');
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Perfil obtenido correctamente')
            ->assertJsonPath('data.email', 'pseg@test.com');
    }

    public function test_actualizar_mi_perfil(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->putJson('/api/actualizarMiPerfil', [
                'nombre'    => 'Paciente Actualizado',
                'telefono'  => '5500112233',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Perfil actualizado correctamente');

        $this->assertDatabaseHas('usuarios', [
            'id'     => $this->pacienteUser->id,
            'nombre' => 'Paciente Actualizado',
        ]);
    }

    public function test_cambiar_password_con_password_actual_correcta(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->postJson('/api/cambiarPassword', [
                'password_actual'      => 'Pac1234!',
                'password'             => 'NuevoPac1234!',
                'password_confirmation' => 'NuevoPac1234!',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Contraseña actualizada correctamente');
    }

    public function test_cambiar_password_con_password_actual_incorrecta(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->postJson('/api/cambiarPassword', [
                'password_actual'      => 'PasswordMal!',
                'password'             => 'NuevoPac1234!',
                'password_confirmation' => 'NuevoPac1234!',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'La contraseña actual es incorrecta.');
    }

    // =========================================================
    // AUTH — CERRAR SESIÓN
    // =========================================================

    public function test_cerrar_sesion_invalida_token(): void
    {
        $tokenObj = $this->pacienteUser->createToken('auth');
        $plainToken = $tokenObj->plainTextToken;

        // Cerrar sesión exitosamente
        $response = $this->withHeader('Authorization', "Bearer $plainToken")
            ->postJson('/api/auth/cerrarSesion');

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Sesión cerrada correctamente');

        // Verificar que el token fue eliminado de la base de datos
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }

    // =========================================================
    // ADMIN — REGISTRO DE RECEPCIONISTA
    // =========================================================

    public function test_admin_puede_registrar_recepcionista(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/auth/registrarRecepcionista', [
                'nombre'    => 'Recep Test',
                'email'     => 'recep@test.com',
                'password'  => 'Recep1234!',
                'password_confirmation' => 'Recep1234!',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Recepcionista registrada correctamente');

        $this->assertDatabaseHas('usuarios', [
            'email' => 'recep@test.com',
            'rol'   => 'recepcionista',
        ]);
    }

    public function test_paciente_no_puede_registrar_recepcionista(): void
    {
        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->postJson('/api/auth/registrarRecepcionista', [
                'nombre'   => 'Recep Ilegal',
                'email'    => 'recep2@test.com',
                'password' => 'Recep1234!',
                'password_confirmation' => 'Recep1234!',
            ]);
        $response->assertStatus(403);
    }

    // =========================================================
    // ADMIN — VALIDAR DOCTOR
    // =========================================================

    public function test_admin_puede_validar_doctor(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/validarDoctor/{$this->doctorPerfil->id}", [
                'estado_validacion' => 'validado',
                'notas_validacion'  => 'Documentación completa.',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Estado de validación actualizado');
    }

    public function test_admin_puede_rechazar_doctor_y_desactiva_cuenta(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/validarDoctor/{$this->doctorPerfil->id}", [
                'estado_validacion' => 'rechazado',
                'notas_validacion'  => 'Cédula no corresponde.',
            ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('usuarios', [
            'id'     => $this->doctorUser->id,
            'estado' => 'inactivo',
        ]);
    }

    // =========================================================
    // CITAS — CONFLICTOS Y REGLAS DE NEGOCIO
    // =========================================================

    public function test_no_se_pueden_agendar_dos_citas_en_mismo_horario(): void
    {
        $proximoLunes = now()->next('Monday')->format('Y-m-d');

        // Crear segundo paciente para la segunda cita
        $otroPaciente = Usuario::create([
            'nombre'   => 'Segundo Paciente',
            'email'    => 'segundo@test.com',
            'password' => bcrypt('Seg1234!'),
            'curp'     => 'SEGP900101HDFXXX01',
            'rol'      => 'paciente',
            'estado'   => 'activo',
        ]);
        $otroPerfil = PerfilPaciente::create([
            'usuario_id'        => $otroPaciente->id,
            'numero_expediente' => 'EXP-20260101-8888',
        ]);

        // Insertar la primera cita directamente en la BD (slot 08:00 ocupado)
        \App\Models\Cita::create([
            'perfil_paciente_id' => $this->pacientePerfil->id,
            'perfil_doctor_id'   => $this->doctorPerfil->id,
            'especialidad_id'    => $this->especialidad->id,
            'codigo_referencia'  => 'CITA-EXIST01',
            'fecha_cita'         => $proximoLunes,
            'hora_cita'          => '08:00:00',
            'duracion_minutos'   => 30,
            'estado'             => 'agendada',
        ]);

        // Intentar registrar segunda cita en mismo slot — debe rechazarse
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarCita', [
                'perfil_paciente_id' => $otroPerfil->id,
                'perfil_doctor_id'   => $this->doctorPerfil->id,
                'especialidad_id'    => $this->especialidad->id,
                'fecha_cita'         => $proximoLunes,
                'hora_cita'          => '08:00:00',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Ya existe una cita agendada para este doctor en ese horario.');
    }


    public function test_cancelar_cita_menos_de_2_horas_antes_es_rechazada(): void
    {
        // Crear cita en 1 hora
        $fechaHora = Carbon::now()->addHour();
        $cita = \App\Models\Cita::create([
            'perfil_paciente_id' => $this->pacientePerfil->id,
            'perfil_doctor_id'   => $this->doctorPerfil->id,
            'especialidad_id'    => $this->especialidad->id,
            'codigo_referencia'  => 'CITA-TEST01',
            'fecha_cita'         => $fechaHora->format('Y-m-d'),
            'hora_cita'          => $fechaHora->format('H:i:s'),
            'duracion_minutos'   => 30,
            'estado'             => 'agendada',
        ]);

        $response = $this->actingAs($this->pacienteUser, 'sanctum')
            ->patchJson("/api/cancelarMiCita/{$cita->id}", [
                'motivo_cancelacion' => 'Ya no puedo',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Solo puedes cancelar con al menos 2 horas de anticipación a la cita.');
    }

    public function test_reprogramar_cita_exitosa(): void
    {
        $proximoLunes = now()->next('Monday')->format('Y-m-d');

        $respCita = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarCita', [
                'perfil_paciente_id' => $this->pacientePerfil->id,
                'perfil_doctor_id'   => $this->doctorPerfil->id,
                'especialidad_id'    => $this->especialidad->id,
                'fecha_cita'         => $proximoLunes,
                'hora_cita'          => '08:00:00',
            ]);
        $citaId = $respCita->json('data.id');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/reprogramarCita/{$citaId}", [
                'fecha_cita' => $proximoLunes,
                'hora_cita'  => '08:30:00',
            ]);
        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cita reprogramada correctamente');
    }
}
