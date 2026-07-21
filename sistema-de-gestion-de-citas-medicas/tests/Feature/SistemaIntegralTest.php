<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\HorarioDoctor;
use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SistemaIntegralTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = Usuario::where('email', 'admin@citasmedicas.com')->first();
    }

    public function test_01_administrador_puede_crear_especialidad_y_registrar_doctor(): void
    {
        // Crear especialidad
        $respEsp = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarEspecialidad', [
                'nombre'      => 'Nutrición Clínica',
                'descripcion' => 'Especialidad en nutrición y dietética.',
            ]);

        $respEsp->assertStatus(200)
            ->assertJsonPath('mensaje', 'Especialidad registrada correctamente');

        $espId = $respEsp->json('data.id');

        // Registrar Doctor con cédula válida (1234567 en Seeder)
        $respDoc = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarDoctor', [
                'nombre'             => 'Dr. Alberto Rivera',
                'email'              => 'alberto.doc@citas.com',
                'password'           => 'Doctor1234!',
                'cedula_profesional' => '1234567',
                'especialidades'     => [$espId],
                'estado_validacion'  => 'validado',
            ]);

        $respDoc->assertStatus(200)
            ->assertJsonPath('mensaje', 'Doctor registrado correctamente');

        $doctorId = $respDoc->json('data.id');

        // Configurar horario para el Doctor
        $respHorario = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/registrarHorario/{$doctorId}", [
                'dia_semana'               => 'lunes',
                'hora_inicio'              => '08:00:00',
                'hora_fin'                 => '12:00:00',
                'duracion_consulta_minutos' => 30,
            ]);

        $respHorario->assertStatus(200)
            ->assertJsonPath('mensaje', 'Horario registrado correctamente');
    }

    public function test_02_flujo_completo_paciente_agenda_y_cancela_con_regla_2_horas(): void
    {
        // 1. Registro público de un nuevo paciente
        $respReg = $this->postJson('/api/auth/registrarPaciente', [
            'nombre'                => 'Laura Gómez',
            'email'                 => 'laura.gomez@test.com',
            'password'              => 'Paciente123!',
            'password_confirmation' => 'Paciente123!',
            'curp'                  => 'GOML950505MDFXXX09',
            'telefono'              => '5599887766',
        ]);

        $respReg->assertStatus(200)
            ->assertJsonPath('mensaje', 'Paciente registrado correctamente');

        $token = $respReg->json('token');
        $paciente = Usuario::where('email', 'laura.gomez@test.com')->first();
        $pacientePerfil = PerfilPaciente::where('usuario_id', $paciente->id)->first();

        // 2. Doctor preparado
        $docUser = Usuario::create([
            'nombre'   => 'Dra. Carmen Silva',
            'email'    => 'carmen@test.com',
            'password' => bcrypt('Pass1234!'),
            'curp'     => 'SILC880808HDFXXX08',
            'rol'      => 'doctor',
            'estado'   => 'activo',
        ]);

        $docPerfil = PerfilDoctor::create([
            'usuario_id'         => $docUser->id,
            'cedula_profesional' => '2345678',
            'estado_validacion'  => 'validado',
        ]);

        $esp = Especialidad::first();
        $docPerfil->especialidades()->attach($esp->id);

        $lunesProximo = now()->next('Monday')->format('Y-m-d');

        HorarioDoctor::create([
            'perfil_doctor_id'          => $docPerfil->id,
            'dia_semana'                => 'lunes',
            'hora_inicio'               => '10:00:00',
            'hora_fin'                  => '14:00:00',
            'duracion_consulta_minutos' => 30,
            'activo'                    => true,
        ]);

        // 3. Paciente agenda cita desde la app móvil
        $respCita = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/agendarCita', [
                'perfil_doctor_id' => $docPerfil->id,
                'especialidad_id'  => $esp->id,
                'fecha_cita'       => $lunesProximo,
                'hora_cita'        => '10:00:00',
            ]);

        $respCita->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cita registrada correctamente');

        $citaId = $respCita->json('data.id');

        // 4. Paciente consulta sus citas
        $respMisCitas = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/misCitas');

        $respMisCitas->assertStatus(200);

        // 5. Paciente cancela la cita con anticipación (lunes próximo es futuro >2h)
        $respCancel = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/cancelarMiCita/{$citaId}", [
                'motivo_cancelacion' => 'Cambio de planes personales',
            ]);

        $respCancel->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cita cancelada correctamente');

        $this->assertDatabaseHas('citas', ['id' => $citaId, 'estado' => 'cancelada']);
    }

    public function test_03_reportes_administrativos(): void
    {
        $respReporteCitas = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reporteCitas');

        $respReporteCitas->assertStatus(200)
            ->assertJsonPath('mensaje', 'Reporte de citas generado');

        $respResumenDiario = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/resumenDiario?fecha=' . now()->format('Y-m-d'));

        $respResumenDiario->assertStatus(200)
            ->assertJsonPath('mensaje', 'Resumen diario generado');
    }
}
