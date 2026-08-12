<?php

namespace Tests\Feature;

use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_version_mas_reciente_devuelve_404_si_no_hay_versiones(): void
    {
        $response = $this->getJson('/api/app-version/latest');

        $response->assertStatus(404)
            ->assertJsonPath('mensaje', 'No hay ninguna versión registrada.');
    }

    public function test_registrar_version_requiere_token_valido(): void
    {
        $response = $this->postJson('/api/app-version', [
            'version'      => '1.0.20',
            'run_number'   => 20,
            'download_url' => 'https://github.com/darthon7/Gestor-de-citas-medicas/releases/download/v1.0.20/app-debug.apk',
        ]);

        $response->assertStatus(401);
    }

    public function test_registrar_y_obtener_version_mas_reciente_exitoso(): void
    {
        $token = config('citas.deploy_token', 'secret-deploy-token-citas');

        $responseStore = $this->withHeader('X-Deploy-Token', $token)
            ->postJson('/api/app-version', [
                'version'      => '1.0.20',
                'run_number'   => 20,
                'download_url' => 'https://github.com/darthon7/Gestor-de-citas-medicas/releases/download/v1.0.20/app-debug.apk',
                'notas'        => 'Build de prueba #20',
            ]);

        $responseStore->assertStatus(200)
            ->assertJsonPath('mensaje', 'Versión registrada correctamente')
            ->assertJsonPath('data.version', '1.0.20')
            ->assertJsonPath('data.run_number', 20);

        $this->assertDatabaseHas('app_versions', [
            'version'    => '1.0.20',
            'run_number' => 20,
        ]);

        // GET /api/app-version/latest debe devolver esta versión
        $responseLatest = $this->getJson('/api/app-version/latest');

        $responseLatest->assertStatus(200)
            ->assertJsonPath('version', '1.0.20')
            ->assertJsonPath('run_number', 20)
            ->assertJsonPath('download_url', 'https://github.com/darthon7/Gestor-de-citas-medicas/releases/download/v1.0.20/app-debug.apk');
    }
}
