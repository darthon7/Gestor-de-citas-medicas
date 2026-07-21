<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\HorarioDoctor;
use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitasTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $doctorUser;
    protected PerfilDoctor $doctorPerfil;
    protected Usuario $pacienteUser;
    protected PerfilPaciente $pacientePerfil;
    protected Especialidad $especialidad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = Usuario::where('email', 'admin@citasmedicas.com')->first();

        // Crear Doctor validado
        $this->doctorUser = Usuario::create([
            'nombre'   => 'Dr. Juan Perez',
            'email'    => 'doctor@test.com',
            'password' => bcrypt('Doctor123!'),
            'curp'     => 'DOCJ900101HDFXXX01',
            'rol'      => 'doctor',
            'estado'   => 'activo',
        ]);

        $this->doctorPerfil = PerfilDoctor::create([
            'usuario_id'         => $this->doctorUser->id,
            'cedula_profesional' => '1234567',
            'estado_validacion'  => 'validado',
        ]);

        $this->especialidad = Especialidad::first();
        $this->doctorPerfil->especialidades()->attach($this->especialidad->id);

        // Horario del doctor: Lunes 09:00 a 12:00
        HorarioDoctor::create([
            'perfil_doctor_id'          => $this->doctorPerfil->id,
            'dia_semana'                => 'lunes',
            'hora_inicio'               => '09:00:00',
            'hora_fin'                  => '12:00:00',
            'duracion_consulta_minutos' => 30,
            'activo'                    => true,
        ]);

        // Crear Paciente
        $this->pacienteUser = Usuario::create([
            'nombre'   => 'Pedro Lopez',
            'email'    => 'paciente2@test.com',
            'password' => bcrypt('Paciente123!'),
            'curp'     => 'PEDL900101HDFXXX01',
            'rol'      => 'paciente',
            'estado'   => 'activo',
        ]);

        $this->pacientePerfil = PerfilPaciente::create([
            'usuario_id'        => $this->pacienteUser->id,
            'numero_expediente' => 'EXP-20260101-0001',
        ]);
    }

    public function test_flujo_completo_de_cita_medica(): void
    {
        // 1. Consultar disponibilidad del doctor para el próximo Lunes
        $proximoLunes = now()->next('Monday')->format('Y-m-d');

        $responseDisp = $this->getJson("/api/obtenerDisponibilidad/{$this->doctorPerfil->id}?fecha={$proximoLunes}");
        $responseDisp->assertStatus(200)
            ->assertJsonPath('data.0.hora', '09:00:00')
            ->assertJsonPath('data.0.disponible', true);

        // 2. Agendar Cita (como Admin/Recepcionista)
        $responseAgendar = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/registrarCita', [
                'perfil_paciente_id' => $this->pacientePerfil->id,
                'perfil_doctor_id'   => $this->doctorPerfil->id,
                'especialidad_id'    => $this->especialidad->id,
                'fecha_cita'         => $proximoLunes,
                'hora_cita'          => '09:00:00',
            ]);

        $responseAgendar->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cita registrada correctamente');

        $citaId = $responseAgendar->json('data.id');

        // 3. Check-In (Recepcionista)
        $responseCheckin = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/checkInCita/{$citaId}");

        $responseCheckin->assertStatus(200)
            ->assertJsonPath('data.estado', 'confirmada');

        // 4. Iniciar Consulta (Doctor)
        $responseIniciar = $this->actingAs($this->doctorUser, 'sanctum')
            ->patchJson("/api/iniciarConsulta/{$citaId}");

        $responseIniciar->assertStatus(200)
            ->assertJsonPath('data.estado', 'en_consulta');

        // 5. Registrar Nota de Consulta (Doctor)
        $responseNota = $this->actingAs($this->doctorUser, 'sanctum')
            ->postJson("/api/registrarNota/{$citaId}", [
                'diagnostico' => 'Faringitis aguda',
                'tratamiento' => 'Amoxicilina 500mg cada 8 horas por 7 días',
            ]);

        $responseNota->assertStatus(200)
            ->assertJsonPath('mensaje', 'Nota de consulta registrada correctamente');

        $this->assertDatabaseHas('citas', ['id' => $citaId, 'estado' => 'completada']);
        $this->assertDatabaseHas('notas_consulta', ['cita_id' => $citaId, 'diagnostico' => 'Faringitis aguda']);
    }
}
