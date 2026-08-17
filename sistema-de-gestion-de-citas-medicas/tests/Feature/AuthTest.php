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
            $response->assertStatus(401);
        }

        // Intento 5: debe bloquear (403)
        $response5 = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'WrongPassword',
        ]);

        $response5->assertStatus(403)
            ->assertJsonPath('mensaje', 'Cuenta bloqueada por 15 minutos tras 5 intentos fallidos.');

        $admin = Usuario::where('email', 'admin@citasmedicas.com')->first();
        $this->assertEquals('bloqueado', $admin->estado);
    }

    public function test_recuperacion_contrasena_flujo_completo(): void
    {
        // 1. Solicitar código
        $resSolicitud = $this->postJson('/api/auth/solicitarRecuperacion', [
            'email' => 'admin@citasmedicas.com',
        ]);
        $resSolicitud->assertStatus(200)
            ->assertJsonPath('mensaje', 'Código de recuperación enviado a tu correo electrónico.');

        $reset = \Illuminate\Support\Facades\DB::table('password_resets')
            ->where('email', 'admin@citasmedicas.com')->first();
        $this->assertNotNull($reset);
        $codigo = $reset->codigo;

        // 2. Verificar código
        $resVerificar = $this->postJson('/api/auth/verificarCodigo', [
            'email'  => 'admin@citasmedicas.com',
            'codigo' => $codigo,
        ]);
        $resVerificar->assertStatus(200)
            ->assertJsonPath('valido', true);

        // 3. Restablecer contraseña
        $resRestablecer = $this->postJson('/api/auth/restablecerPassword', [
            'email'                 => 'admin@citasmedicas.com',
            'codigo'                => $codigo,
            'password'              => 'NuevaPassword123!',
            'password_confirmation' => 'NuevaPassword123!',
        ]);
        $resRestablecer->assertStatus(200)
            ->assertJsonPath('mensaje', 'Contraseña restablecida correctamente.');

        // 4. Probar nuevo login con nueva contraseña
        $resLogin = $this->postJson('/api/auth/login', [
            'email'    => 'admin@citasmedicas.com',
            'password' => 'NuevaPassword123!',
        ]);
        $resLogin->assertStatus(200)
            ->assertJsonStructure(['token']);
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
            'fecha_nacimiento'      => '1990-01-01',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Paciente registrado correctamente')
            ->assertJsonStructure(['token', 'usuario']);

        $this->assertDatabaseHas('usuarios', ['email' => 'paciente@test.com', 'rol' => 'paciente']);
        $this->assertDatabaseHas('perfiles_paciente', ['usuario_id' => $response->json('usuario.id')]);
    }

    public function test_registro_medico_requiere_cedula_valida(): void
    {
        // Cédula con formato inválido (menos de 7 dígitos)
        $responseInvalido = $this->postJson('/api/auth/registrarMedico', [
            'nombre'                => 'Doctor Invalido',
            'email'                 => 'doctor.falso@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'curp'                  => 'BBBB900101HDFXXX02',
            'cedula_profesional'    => '12345',
        ]);

        $responseInvalido->assertStatus(422)
            ->assertJsonPath('mensaje', 'La cédula profesional debe contener de 7 a 8 dígitos numéricos.');

        // Cédula con formato válido (7 a 8 dígitos numéricos)
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

        $this->assertDatabaseHas('perfiles_doctor', [
            'cedula_profesional' => '1234567',
            'estado_validacion'  => 'pendiente',
        ]);
    }
}
