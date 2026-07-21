<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificacionCedula extends Model
{
    use HasFactory;

    protected $table = 'verificaciones_cedula';

    protected $fillable = [
        'numero_cedula',
        'nombre_titular',
        'profesion',
        'institucion',
        'es_valida',
    ];

    protected $casts = [
        'es_valida' => 'boolean',
    ];
}
