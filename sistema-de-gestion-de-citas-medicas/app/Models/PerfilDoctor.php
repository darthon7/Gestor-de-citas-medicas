<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilDoctor extends Model
{
    use HasFactory;

    protected $table = 'perfiles_doctor';

    protected $fillable = [
        'usuario_id',
        'cedula_profesional',
        'cedula_especialidad',
        'estado_validacion',
        'notas_validacion',
        'validado_por',
        'validado_en',
    ];

    protected $casts = [
        'validado_en' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'doctor_especialidad', 'perfil_doctor_id', 'especialidad_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioDoctor::class, 'perfil_doctor_id');
    }

    public function bloqueos()
    {
        return $this->hasMany(BloqueoHorario::class, 'perfil_doctor_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'perfil_doctor_id');
    }

    public function validadoPor()
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
    }
}
