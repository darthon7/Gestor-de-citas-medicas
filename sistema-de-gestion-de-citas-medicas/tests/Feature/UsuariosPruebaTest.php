<?php

namespace Tests\Feature;

use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\PerfilRecepcionista;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para verificar los usuarios de prueba insertados
 * con UsuariosPruebaSeeder.
 *
 * Usuarios disponibles en la BD real:
 *  - maria.gonzalez@paciente.com   / Paciente1234!  (paciente)
 *  - carlos.ramirez@paciente.com   / Paciente1234!  (paciente)
 *  - luisa.hernandez@paciente.com  / Paciente1234!  (paciente)
 *  - dr.alejandro.vega@doctor.com  / Doctor1234!    (doctor, validado)
 *  - sofia.morales@recepcion.com   / Recep1234!     (recepcionista)
 */
class UsuariosPruebaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ejecuta TODOS los seeders incluyendo UsuariosPruebaSeeder
        $this->seed();
    }

    // ─────────────────────────────────────────────────────────────
    // EXISTENCIA EN BASE DE DATOS
    // ─────────────────────────────────────────────────────────────

    public function test_tres_pacientes_de_prueba_existen_en_bd(): void
    {
        $this->assertDatabaseHas('usuarios', [
            'email' => 'maria.gonzalez@paciente.com',
            'rol'   => 'paciente',
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'carlos.ramirez@paciente.com',
            'rol'   => 'paciente',
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'luisa.hernandez@paciente.com',
            'rol'   => 'paciente',
            'estado' => 'activo',
        ]);
    }

    public function test_doctor_de_prueba_existe_y_esta_validado(): void
    {
        $this->assertDatabaseHas('usuarios', [
            'email' => 'dr.alejandro.vega@doctor.com',
            'rol'   => 'doctor',
            'estado' => 'activo',
        ]);

        $doctor = Usuario::where('email', 'dr.alejandro.vega@doctor.com')->first();
        $this->assertNotNull($doctor);

        $perfil = PerfilDoctor::where('usuario_id', $doctor->id)->first();
        $this->assertNotNull($perfil);
        $this->assertEquals('validado', $perfil->estado_validacion);
    }

    public function test_recepcionista_de_prueba_existe_en_bd(): void
    {
        $this->assertDatabaseHas('usuarios', [
            'email' => 'sofia.morales@recepcion.com',
            'rol'   => 'recepcionista',
            'estado' => 'activo',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PERFILES ASOCIADOS
    // ─────────────────────────────────────────────────────────────

    public function test_pacientes_tienen_perfiles_completos(): void
    {
        $emails = [
            'maria.gonzalez@paciente.com',
            'carlos.ramirez@paciente.com',
            'luisa.hernandez@paciente.com',
        ];

        foreach ($emails as $email) {
            $usuario = Usuario::where('email', $email)->first();
            $this->assertNotNull($usuario, "Usuario {$email} no encontrado");

            $perfil = PerfilPaciente::where('usuario_id', $usuario->id)->first();
            $this->assertNotNull($perfil, "Perfil paciente de {$email} no encontrado");
            $this->assertNotNull($perfil->numero_expediente);
        }
    }

    public function test_doctor_tiene_especialidad_y_horarios(): void
    {
        $doctor = Usuario::where('email', 'dr.alejandro.vega@doctor.com')->first();
        $perfil = PerfilDoctor::where('usuario_id', $doctor->id)->first();

        // Tiene al menos una especialidad
        $this->assertGreaterThan(0, $perfil->especialidades()->count());

        // Tiene horarios activos
        $this->assertGreaterThan(0, $perfil->horarios()->where('activo', true)->count());
    }

    // ─────────────────────────────────────────────────────────────
    // LOGIN — CREDENCIALES DE PRUEBA
    // ─────────────────────────────────────────────────────────────

    public function test_login_exitoso_paciente_maria(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'maria.gonzalez@paciente.com',
            'password' => 'Paciente1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Login correcto')
            ->assertJsonStructure(['token', 'usuario', 'rol'])
            ->assertJsonPath('rol', 'paciente');
    }

    public function test_login_exitoso_paciente_carlos(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'carlos.ramirez@paciente.com',
            'password' => 'Paciente1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('rol', 'paciente');
    }

    public function test_login_exitoso_paciente_luisa(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'luisa.hernandez@paciente.com',
            'password' => 'Paciente1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('rol', 'paciente');
    }

    public function test_login_exitoso_doctor_alejandro(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'dr.alejandro.vega@doctor.com',
            'password' => 'Doctor1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Login correcto')
            ->assertJsonPath('rol', 'doctor');
    }

    public function test_login_exitoso_recepcionista_sofia(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'sofia.morales@recepcion.com',
            'password' => 'Recep1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Login correcto')
            ->assertJsonPath('rol', 'recepcionista');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESO A PERFIL PROPIO
    // ─────────────────────────────────────────────────────────────

    public function test_cada_paciente_puede_ver_su_propio_perfil(): void
    {
        $emails = [
            'maria.gonzalez@paciente.com',
            'carlos.ramirez@paciente.com',
            'luisa.hernandez@paciente.com',
        ];

        foreach ($emails as $email) {
            $usuario = Usuario::where('email', $email)->first();

            $response = $this->actingAs($usuario, 'sanctum')
                ->getJson('/api/miPerfil');

            $response->assertStatus(200)
                ->assertJsonPath('mensaje', 'Perfil obtenido correctamente')
                ->assertJsonPath('data.email', $email);
        }
    }

    public function test_doctor_puede_ver_su_propio_perfil(): void
    {
        $doctor = Usuario::where('email', 'dr.alejandro.vega@doctor.com')->first();

        $response = $this->actingAs($doctor, 'sanctum')
            ->getJson('/api/miPerfil');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'dr.alejandro.vega@doctor.com');
    }

    // ─────────────────────────────────────────────────────────────
    // AISLAMIENTO — PACIENTES NO ACCEDEN A RUTAS RESTRINGIDAS
    // ─────────────────────────────────────────────────────────────

    public function test_pacientes_de_prueba_no_pueden_acceder_rutas_admin(): void
    {
        $emails = [
            'maria.gonzalez@paciente.com',
            'carlos.ramirez@paciente.com',
            'luisa.hernandez@paciente.com',
        ];

        foreach ($emails as $email) {
            $usuario = Usuario::where('email', $email)->first();

            $response = $this->actingAs($usuario, 'sanctum')
                ->getJson('/api/obtenerCitas');

            $response->assertStatus(403);
        }
    }

    public function test_total_de_usuarios_de_prueba_en_bd(): void
    {
        // Admin(1) + 3 pacientes + 1 doctor + 1 recepcionista = 6
        $totalEsperado = 6;
        $total = Usuario::count();

        $this->assertGreaterThanOrEqual($totalEsperado, $total);
    }
}
