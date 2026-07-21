<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['email' => 'admin@citasmedicas.com'],
            [
                'nombre'   => 'Administrador Principal',
                'password' => Hash::make('Admin1234!'),
                'curp'     => 'ADMP900101HDFXXX00',
                'telefono' => '5500000000',
                'rol'      => 'admin',
                'estado'   => 'activo',
            ]
        );
    }
}
