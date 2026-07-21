<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntentoLogin extends Model
{
    use HasFactory;

    protected $table = 'intentos_login';

    protected $fillable = [
        'email',
        'direccion_ip',
        'exitoso',
    ];

    protected $casts = [
        'exitoso' => 'boolean',
    ];
}
