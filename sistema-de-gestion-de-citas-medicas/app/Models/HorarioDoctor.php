<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDoctor extends Model
{
    use HasFactory;

    protected $table = 'horarios_doctor';

    protected $fillable = [
        'perfil_doctor_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_consulta_minutos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }
}
