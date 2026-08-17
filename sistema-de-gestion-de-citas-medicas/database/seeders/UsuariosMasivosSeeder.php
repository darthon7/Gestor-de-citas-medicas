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

class UsuariosMasivosSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // 1. ADMINISTRADORES (2)
        // =========================================================================
        $admins = [
            [
                'nombre'   => 'Lic. Mariana Valenzuela Ríos',
                'email'    => 'mariana.admin@citasmedicas.com',
                'password' => Hash::make('Admin1234!'),
                'curp'     => 'VARM850412MDFNZR01',
                'telefono' => '5511002201',
                'rol'      => 'admin',
                'estado'   => 'activo',
            ],
            [
                'nombre'   => 'Ing. Fernando Salgado Montes',
                'email'    => 'fernando.admin@citasmedicas.com',
                'password' => Hash::make('Admin1234!'),
                'curp'     => 'SAMF880925HDFRRR02',
                'telefono' => '5511002202',
                'rol'      => 'admin',
                'estado'   => 'activo',
            ],
        ];

        foreach ($admins as $admData) {
            Usuario::firstOrCreate(['email' => $admData['email']], $admData);
        }

        // =========================================================================
        // 2. RECEPCIONISTAS (3)
        // =========================================================================
        $recepcionistas = [
            [
                'usuario' => [
                    'nombre'   => 'Laura Marcela Pineda Gómez',
                    'email'    => 'laura.pineda@recepcion.com',
                    'password' => Hash::make('Recep1234!'),
                    'curp'     => 'PIGL920315MDFNND01',
                    'telefono' => '5522003301',
                    'rol'      => 'recepcionista',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_empleado' => 'EMP-REC-001',
                    'unidad_asignada' => 'Módulo Central A',
                    'turno'           => 'Matutino (07:00 - 15:00)',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Jorge Alberto Navarro Cruz',
                    'email'    => 'jorge.navarro@recepcion.com',
                    'password' => Hash::make('Recep1234!'),
                    'curp'     => 'NACJ941108HDFVRR02',
                    'telefono' => '5522003302',
                    'rol'      => 'recepcionista',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_empleado' => 'EMP-REC-002',
                    'unidad_asignada' => 'Módulo Especialidades B',
                    'turno'           => 'Vespertino (14:00 - 21:30)',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Carmen Elena Ortiz Soto',
                    'email'    => 'carmen.ortiz@recepcion.com',
                    'password' => Hash::make('Recep1234!'),
                    'curp'     => 'OISC960820MDFTRL03',
                    'telefono' => '5522003303',
                    'rol'      => 'recepcionista',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_empleado' => 'EMP-REC-003',
                    'unidad_asignada' => 'Módulo Consulta Externa C',
                    'turno'           => 'Jornada Especial / Fines de Semana',
                ],
            ],
        ];

        foreach ($recepcionistas as $rec) {
            $userRec = Usuario::firstOrCreate(['email' => $rec['usuario']['email']], $rec['usuario']);
            PerfilRecepcionista::firstOrCreate(
                ['usuario_id' => $userRec->id],
                $rec['perfil']
            );
        }

        // =========================================================================
        // 3. MÉDICOS / DOCTORES (10)
        // =========================================================================
        $doctores = [
            [
                'usuario' => [
                    'nombre'   => 'Dr. Roberto Carlos Méndez Garza',
                    'email'    => 'roberto.mendez@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'MEGR820110HDFNNR01',
                    'telefono' => '5544005001',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002001',
                'cedula_especialidad' => '81002001',
                'especialidad'        => 'Cardiología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dra. Claudia Patricia Herrera Solís',
                    'email'    => 'claudia.herrera@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'HESC860614MDFRLD02',
                    'telefono' => '5544005002',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002002',
                'cedula_especialidad' => '81002002',
                'especialidad'        => 'Pediatría',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dr. Javier Eduardo Morales Peña',
                    'email'    => 'javier.morales@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'MOPJ791022HDFZRL03',
                    'telefono' => '5544005003',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002003',
                'cedula_especialidad' => '81002003',
                'especialidad'        => 'Dermatología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dra. Gabriela Isabel Santos Bravo',
                    'email'    => 'gabriela.santos@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'SABG840405MDFNTB04',
                    'telefono' => '5544005004',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002004',
                'cedula_especialidad' => '81002004',
                'especialidad'        => 'Ginecología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dr. Mauricio Andrés Cordero Paz',
                    'email'    => 'mauricio.cordero@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'COPM810819HDFRZZ05',
                    'telefono' => '5544005005',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002005',
                'cedula_especialidad' => '81002005',
                'especialidad'        => 'Ortopedia',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dra. Valeria Berenice Aguilar Ramos',
                    'email'    => 'valeria.aguilar@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'AURV871230MDFGHL06',
                    'telefono' => '5544005006',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002006',
                'cedula_especialidad' => '81002006',
                'especialidad'        => 'Neurología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dr. Rodrigo Daniel Castro Villalobos',
                    'email'    => 'rodrigo.castro@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'CAVR830318HDFSTV07',
                    'telefono' => '5544005007',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002007',
                'cedula_especialidad' => '81002007',
                'especialidad'        => 'Oftalmología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dra. Natalia Eugenia Fuentes Cruz',
                    'email'    => 'natalia.fuentes@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'FUCN890725MDFRTR08',
                    'telefono' => '5544005008',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002008',
                'cedula_especialidad' => '81002008',
                'especialidad'        => 'Endocrinología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dr. Sergio Manuel Benítez Luna',
                    'email'    => 'sergio.benitez@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'BELS760511HDFMMR09',
                    'telefono' => '5544005009',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002009',
                'cedula_especialidad' => '81002009',
                'especialidad'        => 'Gastroenterología',
            ],
            [
                'usuario' => [
                    'nombre'   => 'Dra. Daniela Alejandra Reyes Orozco',
                    'email'    => 'daniela.reyes@doctor.com',
                    'password' => Hash::make('Doctor1234!'),
                    'curp'     => 'REOD910903MDFXNZ10',
                    'telefono' => '5544005010',
                    'rol'      => 'doctor',
                    'estado'   => 'activo',
                ],
                'cedula_profesional'  => '71002010',
                'cedula_especialidad' => '81002010',
                'especialidad'        => 'Medicina General',
            ],
        ];

        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

        foreach ($doctores as $doc) {
            $userDoc = Usuario::firstOrCreate(['email' => $doc['usuario']['email']], $doc['usuario']);

            $perfilDoc = PerfilDoctor::firstOrCreate(
                ['usuario_id' => $userDoc->id],
                [
                    'cedula_profesional'  => $doc['cedula_profesional'],
                    'cedula_especialidad' => $doc['cedula_especialidad'],
                    'estado_validacion'   => 'validado',
                    'validado_en'         => now(),
                ]
            );

            // Asociar especialidad
            $esp = Especialidad::where('nombre', $doc['especialidad'])->first() ?? Especialidad::first();
            if ($esp && !$perfilDoc->especialidades()->where('especialidad_id', $esp->id)->exists()) {
                $perfilDoc->especialidades()->attach($esp->id);
            }

            // Generar horarios de atención de lunes a viernes
            foreach ($diasSemana as $dia) {
                HorarioDoctor::firstOrCreate(
                    ['perfil_doctor_id' => $perfilDoc->id, 'dia_semana' => $dia],
                    [
                        'hora_inicio'               => '08:00:00',
                        'hora_fin'                  => '15:00:00',
                        'duracion_consulta_minutos' => 30,
                        'activo'                    => true,
                    ]
                );
            }
        }

        // =========================================================================
        // 4. PACIENTES (10)
        // =========================================================================
        $pacientes = [
            [
                'usuario' => [
                    'nombre'   => 'Hugo Arturo Valadez Romero',
                    'email'    => 'hugo.valadez@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'VARH900214HDFLTR01',
                    'telefono' => '5566001001',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1001',
                    'fecha_nacimiento'             => '1990-02-14',
                    'sexo'                         => 'M',
                    'direccion'                    => 'Av. Cuauhtémoc 450, Col. Roma Sur, CDMX',
                    'contacto_emergencia_nombre'   => 'Patricia Romero',
                    'contacto_emergencia_telefono' => '5577001001',
                    'nss'                          => '55109000011',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Mariana Lucía Espinoza Carrillo',
                    'email'    => 'mariana.espinoza@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'EACM930628MDFNRR02',
                    'telefono' => '5566001002',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1002',
                    'fecha_nacimiento'             => '1993-06-28',
                    'sexo'                         => 'F',
                    'direccion'                    => 'Calle Sonora 120, Col. Condesa, CDMX',
                    'contacto_emergencia_nombre'   => 'Javier Espinoza',
                    'contacto_emergencia_telefono' => '5577001002',
                    'nss'                          => '55109300022',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Emilio Andrés Rivas Mercado',
                    'email'    => 'emilio.rivas@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'RIME851104HDFVSR03',
                    'telefono' => '5566001003',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1003',
                    'fecha_nacimiento'             => '1985-11-04',
                    'sexo'                         => 'M',
                    'direccion'                    => 'Av. División del Norte 2300, Col. Del Valle, CDMX',
                    'contacto_emergencia_nombre'   => 'Claudia Mercado',
                    'contacto_emergencia_telefono' => '5577001003',
                    'nss'                          => '55108500033',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Adriana Guadalupe Ponce Marín',
                    'email'    => 'adriana.ponce@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'POMA880419MDFGHL04',
                    'telefono' => '5566001004',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1004',
                    'fecha_nacimiento'             => '1988-04-19',
                    'sexo'                         => 'F',
                    'direccion'                    => 'Calz. de Tlalpan 1890, Col. Portales, CDMX',
                    'contacto_emergencia_nombre'   => 'Mauricio Ponce',
                    'contacto_emergencia_telefono' => '5577001004',
                    'nss'                          => '55108800044',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Guillermo David Ochoa Saldaña',
                    'email'    => 'guillermo.ochoa@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'OOSG960712HDFKPR05',
                    'telefono' => '5566001005',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1005',
                    'fecha_nacimiento'             => '1996-07-12',
                    'sexo'                         => 'M',
                    'direccion'                    => 'Calle Amsterdam 88, Col. Hipódromo Condesa, CDMX',
                    'contacto_emergencia_nombre'   => 'Sofía Saldaña',
                    'contacto_emergencia_telefono' => '5577001005',
                    'nss'                          => '55109600055',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Lucía Jimena Cárdenas Beltrán',
                    'email'    => 'lucia.cardenas@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'CABL810130MDFZRT06',
                    'telefono' => '5566001006',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1006',
                    'fecha_nacimiento'             => '1981-01-30',
                    'sexo'                         => 'F',
                    'direccion'                    => 'Av. Patriotismo 650, Col. San Pedro de los Pinos, CDMX',
                    'contacto_emergencia_nombre'   => 'Enrique Cárdenas',
                    'contacto_emergencia_telefono' => '5577001006',
                    'nss'                          => '55108100066',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Tomás Ignacio Becerra Quintana',
                    'email'    => 'tomas.becerra@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'BEQT940915HDFMNP07',
                    'telefono' => '5566001007',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1007',
                    'fecha_nacimiento'             => '1994-09-15',
                    'sexo'                         => 'M',
                    'direccion'                    => 'Calle Eugenia 310, Col. Narvarte Poniente, CDMX',
                    'contacto_emergencia_nombre'   => 'Rosa Quintana',
                    'contacto_emergencia_telefono' => '5577001007',
                    'nss'                          => '55109400077',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Paola Monserrat Delgadillo Soler',
                    'email'    => 'paola.delgadillo@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'DESP870322MDFTRC08',
                    'telefono' => '5566001008',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1008',
                    'fecha_nacimiento'             => '1987-03-22',
                    'sexo'                         => 'F',
                    'direccion'                    => 'Av. Universidad 1200, Col. Xoco, CDMX',
                    'contacto_emergencia_nombre'   => 'Mario Delgadillo',
                    'contacto_emergencia_telefono' => '5577001008',
                    'nss'                          => '55108700088',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Julio César Santillán Miranda',
                    'email'    => 'julio.santillan@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'SAMJ990805HDFRKL09',
                    'telefono' => '5566001009',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1009',
                    'fecha_nacimiento'             => '1999-08-05',
                    'sexo'                         => 'M',
                    'direccion'                    => 'Calle Río Lerma 205, Col. Cuauhtémoc, CDMX',
                    'contacto_emergencia_nombre'   => 'Ana Miranda',
                    'contacto_emergencia_telefono' => '5577001009',
                    'nss'                          => '55109900099',
                ],
            ],
            [
                'usuario' => [
                    'nombre'   => 'Fernanda Estefanía Zamudio Vela',
                    'email'    => 'fernanda.zamudio@paciente.com',
                    'password' => Hash::make('Paciente1234!'),
                    'curp'     => 'ZAVF921201MDFGTV10',
                    'telefono' => '5566001010',
                    'rol'      => 'paciente',
                    'estado'   => 'activo',
                ],
                'perfil' => [
                    'numero_expediente'            => 'EXP-2026-1010',
                    'fecha_nacimiento'             => '1992-12-01',
                    'sexo'                         => 'F',
                    'direccion'                    => 'Av. Coyoacán 840, Col. Del Valle Centro, CDMX',
                    'contacto_emergencia_nombre'   => 'Gustavo Zamudio',
                    'contacto_emergencia_telefono' => '5577001010',
                    'nss'                          => '55109200010',
                ],
            ],
        ];

        foreach ($pacientes as $pac) {
            $userPac = Usuario::firstOrCreate(['email' => $pac['usuario']['email']], $pac['usuario']);
            PerfilPaciente::firstOrCreate(
                ['usuario_id' => $userPac->id],
                $pac['perfil']
            );
        }

        $this->command->info('✅ Seeder UsuariosMasivosSeeder ejecutado con éxito:');
        $this->command->info('  • 2 Administradores agregados');
        $this->command->info('  • 3 Recepcionistas agregadas');
        $this->command->info('  • 10 Médicos agregados con especialidad y horario');
        $this->command->info('  • 10 Pacientes agregados con perfil y expediente');
    }
}
