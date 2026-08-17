<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\PerfilDoctor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorGoGoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario doctor GoGo
        $usuario = Usuario::firstOrCreate(
            ['email' => 'gogo@doctor.com'],
            [
                'nombre'   => 'GoGo',
                'password' => Hash::make('Doctor1234!'),
                'curp'     => 'GOGO950101HDFRRC09',
                'telefono' => '5511223344',
                'rol'      => 'doctor',
                'estado'   => 'activo',
            ]
        );

        // Crear perfil del doctor con estado_validacion = pendiente
        $perfilDoctor = PerfilDoctor::firstOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'cedula_profesional'  => '3456789',
                'cedula_especialidad' => 'ESP-GOGO-01',
                'estado_validacion'   => 'pendiente',
            ]
        );

        // Asignar especialidad
        $especialidad = Especialidad::where('nombre', 'Cardiología')->first()
            ?? Especialidad::first();

        if ($especialidad && !$perfilDoctor->especialidades()->where('especialidad_id', $especialidad->id)->exists()) {
            $perfilDoctor->especialidades()->attach($especialidad->id);
        }
    }
}
