<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_exitoso_administrador(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'Admin1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Login correcto')
            ->assertJsonStructure(['token', 'usuario', 'rol']);
    }

    public function test_login_fallido_bloquea_tras_5_intentos(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email'    => 'admin@citasmedicas.com',
                'password' => 'WrongPassword',
            ]);
            $response->assertStatus(200);
        }

        // Intento 5: debe bloquear
        $response5 = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'WrongPassword',
        ]);

        $response5->assertStatus(200)
            ->assertJsonPath('mensaje', 'Cuenta bloqueada por 15 minutos tras 5 intentos fallidos.');

        $admin = Usuario::where('email', 'admin@citasmedicas.com')->first();
        $this->assertEquals('bloqueado', $admin->estado);
    }

    public function test_registro_paciente_exitoso(): void
    {
        $response = $this->postJson('/api/auth/registrarPaciente', [
            'nombre'                => 'Paciente Test',
            'email'                 => 'paciente@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'curp'                  => 'AAAA900101HDFXXX01',
            'telefono'              => '5512345678',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Paciente registrado correctamente')
            ->assertJsonStructure(['token', 'usuario']);

        $this->assertDatabaseHas('usuarios', ['email' => 'paciente@test.com', 'rol' => 'paciente']);
        $this->assertDatabaseHas('perfiles_paciente', ['usuario_id' => $response->json('usuario.id')]);
    }

    public function test_registro_medico_requiere_cedula_valida(): void
    {
        // Cédula no existe en mock
        $response = $this->postJson('/api/auth/registrarMedico', [
            'nombre'                => 'Doctor Invalido',
            'email'                 => 'doctor.falso@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'curp'                  => 'BBBB900101HDFXXX02',
            'cedula_profesional'    => '0000000',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'La cédula profesional no se encuentra registrada en el sistema de verificación.');

        // Cédula válida (1234567 existe en Seeder)
        $responseValido = $this->postJson('/api/auth/registrarMedico', [
            'nombre'                => 'Doctor Valido',
            'email'                 => 'doctor.valido@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'curp'                  => 'CCCC900101HDFXXX03',
            'cedula_profesional'    => '1234567',
        ]);

        $responseValido->assertStatus(200)
            ->assertJsonPath('mensaje', 'Médico registrado. Tu cuenta está pendiente de validación por el administrador.');
    }
}
