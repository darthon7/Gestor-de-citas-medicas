<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadesSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'Medicina General',       'descripcion' => 'Atención médica primaria y general.'],
            ['nombre' => 'Pediatría',              'descripcion' => 'Atención médica para niños y adolescentes.'],
            ['nombre' => 'Cardiología',            'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón.'],
            ['nombre' => 'Dermatología',           'descripcion' => 'Tratamiento de enfermedades de la piel.'],
            ['nombre' => 'Ginecología',            'descripcion' => 'Salud reproductiva femenina.'],
            ['nombre' => 'Oftalmología',           'descripcion' => 'Diagnóstico y tratamiento de enfermedades de los ojos.'],
            ['nombre' => 'Ortopedia',              'descripcion' => 'Tratamiento del sistema músculo-esquelético.'],
            ['nombre' => 'Neurología',             'descripcion' => 'Enfermedades del sistema nervioso.'],
            ['nombre' => 'Psiquiatría',            'descripcion' => 'Salud mental y trastornos psiquiátricos.'],
            ['nombre' => 'Endocrinología',         'descripcion' => 'Enfermedades hormonales y metabólicas.'],
            ['nombre' => 'Gastroenterología',      'descripcion' => 'Enfermedades del aparato digestivo.'],
            ['nombre' => 'Urología',               'descripcion' => 'Enfermedades del aparato urinario.'],
            ['nombre' => 'Otorrinolaringología',   'descripcion' => 'Oídos, nariz y garganta.'],
            ['nombre' => 'Neumología',             'descripcion' => 'Enfermedades del aparato respiratorio.'],
            ['nombre' => 'Reumatología',           'descripcion' => 'Enfermedades articulares y autoinmunes.'],
        ];

        foreach ($especialidades as $esp) {
            Especialidad::firstOrCreate(['nombre' => $esp['nombre']], $esp);
        }
    }
}
