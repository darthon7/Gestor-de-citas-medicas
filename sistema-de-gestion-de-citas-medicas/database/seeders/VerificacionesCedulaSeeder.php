<?php

namespace Database\Seeders;

use App\Models\VerificacionCedula;
use Illuminate\Database\Seeder;

class VerificacionesCedulaSeeder extends Seeder
{
    public function run(): void
    {
        $cedulas = [
            [
                'numero_cedula'  => '1234567',
                'nombre_titular' => 'Juan Carlos López Martínez',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'UNAM',
                'es_valida'      => true,
            ],
            [
                'numero_cedula'  => '2345678',
                'nombre_titular' => 'María Elena Rodríguez García',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'IPN',
                'es_valida'      => true,
            ],
            [
                'numero_cedula'  => '3456789',
                'nombre_titular' => 'Roberto Sánchez Pérez',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'UAM',
                'es_valida'      => true,
            ],
            [
                'numero_cedula'  => '4567890',
                'nombre_titular' => 'Ana Patricia Flores Hernández',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'UNAM',
                'es_valida'      => true,
            ],
            [
                'numero_cedula'  => '5678901',
                'nombre_titular' => 'Carlos Mendoza Torres',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'Anáhuac',
                'es_valida'      => true,
            ],
            [
                'numero_cedula'  => '9999999',
                'nombre_titular' => 'Cédula Inválida Test',
                'profesion'      => 'Médico Cirujano',
                'institucion'    => 'Test',
                'es_valida'      => false,
            ],
        ];

        foreach ($cedulas as $cedula) {
            VerificacionCedula::firstOrCreate(['numero_cedula' => $cedula['numero_cedula']], $cedula);
        }
    }
}
