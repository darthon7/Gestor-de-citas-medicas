<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueoHorario extends Model
{
    use HasFactory;

    protected $table = 'bloqueos_horario';

    protected $fillable = [
        'perfil_doctor_id',
        'fecha_bloqueo',
        'hora_inicio_bloqueo',
        'hora_fin_bloqueo',
        'motivo',
        'creado_por',
    ];

    protected $casts = [
        'fecha_bloqueo' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
