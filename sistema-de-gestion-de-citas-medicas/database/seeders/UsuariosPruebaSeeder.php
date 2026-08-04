<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\HorarioDoctor;
use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\PerfilRecepcionista;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // PACIENTE 1
        // ─────────────────────────────────────────────
        $paciente1 = Usuario::firstOrCreate(
            ['email' => 'maria.gonzalez@paciente.com'],
            [
                'nombre'   => 'María González López',
                'password' => Hash::make('Paciente1234!'),
                'curp'     => 'GOLM850312MDFNZR01',
                'telefono' => '5512345678',
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]
        );
        PerfilPaciente::firstOrCreate(
            ['usuario_id' => $paciente1->id],
            [
                'numero_expediente'            => 'EXP-2026-0001',
                'fecha_nacimiento'             => '1985-03-12',
                'sexo'                         => 'F',
                'direccion'                    => 'Av. Insurgentes Sur 1234, CDMX',
                'contacto_emergencia_nombre'   => 'Juan González',
                'contacto_emergencia_telefono' => '5598765432',
                'nss'                          => '12345678901',
            ]
        );

        // ─────────────────────────────────────────────
        // PACIENTE 2
        // ─────────────────────────────────────────────
        $paciente2 = Usuario::firstOrCreate(
            ['email' => 'carlos.ramirez@paciente.com'],
            [
                'nombre'   => 'Carlos Ramírez Mendoza',
                'password' => Hash::make('Paciente1234!'),
                'curp'     => 'RAMC920715HDFMND02',
                'telefono' => '5523456789',
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]
        );
        PerfilPaciente::firstOrCreate(
            ['usuario_id' => $paciente2->id],
            [
                'numero_expediente'            => 'EXP-2026-0002',
                'fecha_nacimiento'             => '1992-07-15',
                'sexo'                         => 'M',
                'direccion'                    => 'Calle Reforma 567, CDMX',
                'contacto_emergencia_nombre'   => 'Ana Mendoza',
                'contacto_emergencia_telefono' => '5587654321',
                'nss'                          => '98765432109',
            ]
        );

        // ─────────────────────────────────────────────
        // PACIENTE 3
        // ─────────────────────────────────────────────
        $paciente3 = Usuario::firstOrCreate(
            ['email' => 'luisa.hernandez@paciente.com'],
            [
                'nombre'   => 'Luisa Hernández Torres',
                'password' => Hash::make('Paciente1234!'),
                'curp'     => 'HETL780901MDFRRR03',
                'telefono' => '5534567890',
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]
        );
        PerfilPaciente::firstOrCreate(
            ['usuario_id' => $paciente3->id],
            [
                'numero_expediente'            => 'EXP-2026-0003',
                'fecha_nacimiento'             => '1978-09-01',
                'sexo'                         => 'F',
                'direccion'                    => 'Blvd. Manuel Ávila Camacho 890, CDMX',
                'contacto_emergencia_nombre'   => 'Pedro Torres',
                'contacto_emergencia_telefono' => '5576543210',
                'nss'                          => '11223344556',
            ]
        );

        // ─────────────────────────────────────────────
        // DOCTOR (validado con horario)
        // ─────────────────────────────────────────────
        $doctor = Usuario::firstOrCreate(
            ['email' => 'dr.alejandro.vega@doctor.com'],
            [
                'nombre'   => 'Dr. Alejandro Vega Ruiz',
                'password' => Hash::make('Doctor1234!'),
                'curp'     => 'VERA800520HDFGZL04',
                'telefono' => '5545678901',
                'rol'      => 'doctor',
                'estado'   => 'activo',
            ]
        );
        $perfilDoctor = PerfilDoctor::firstOrCreate(
            ['usuario_id' => $doctor->id],
            [
                'cedula_profesional' => 'DRO-2026-001',
                'estado_validacion'  => 'validado',
            ]
        );

        // Asociar especialidad al doctor
        $especialidad = Especialidad::where('nombre', 'Medicina General')->first();
        if ($especialidad && !$perfilDoctor->especialidades()->where('especialidad_id', $especialidad->id)->exists()) {
            $perfilDoctor->especialidades()->attach($especialidad->id);
        }

        // Crear horario lunes–viernes 09:00–17:00
        $diasLaborales = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        foreach ($diasLaborales as $dia) {
            HorarioDoctor::firstOrCreate(
                ['perfil_doctor_id' => $perfilDoctor->id, 'dia_semana' => $dia],
                [
                    'hora_inicio'               => '09:00:00',
                    'hora_fin'                  => '17:00:00',
                    'duracion_consulta_minutos' => 30,
                    'activo'                    => true,
                ]
            );
        }

        // ─────────────────────────────────────────────
        // RECEPCIONISTA
        // ─────────────────────────────────────────────
        $recepcionista = Usuario::firstOrCreate(
            ['email' => 'sofia.morales@recepcion.com'],
            [
                'nombre'   => 'Sofía Morales Díaz',
                'password' => Hash::make('Recep1234!'),
                'curp'     => 'MODS950228MDFRZF05',
                'telefono' => '5556789012',
                'rol'      => 'recepcionista',
                'estado'   => 'activo',
            ]
        );
        PerfilRecepcionista::firstOrCreate(
            ['usuario_id' => $recepcionista->id],
            []
        );

        $this->command->info('✅ Usuarios de prueba insertados correctamente:');
        $this->command->table(
            ['Nombre', 'Email', 'Rol', 'Password'],
            [
                [$paciente1->nombre,    $paciente1->email,    'paciente',       'Paciente1234!'],
                [$paciente2->nombre,    $paciente2->email,    'paciente',       'Paciente1234!'],
                [$paciente3->nombre,    $paciente3->email,    'paciente',       'Paciente1234!'],
                [$doctor->nombre,       $doctor->email,       'doctor',         'Doctor1234!'],
                [$recepcionista->nombre,$recepcionista->email,'recepcionista',  'Recep1234!'],
            ]
        );
    }
}
