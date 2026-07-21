<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'perfil_paciente_id',
        'perfil_doctor_id',
        'especialidad_id',
        'codigo_referencia',
        'fecha_cita',
        'hora_cita',
        'duracion_minutos',
        'estado',
        'motivo_cancelacion',
        'cancelado_por',
        'cancelado_en',
        'checkin_en',
        'checkin_por',
    ];

    protected $casts = [
        'fecha_cita'   => 'date',
        'cancelado_en' => 'datetime',
        'checkin_en'   => 'datetime',
    ];

    public function perfilPaciente()
    {
        return $this->belongsTo(PerfilPaciente::class, 'perfil_paciente_id');
    }

    public function perfilDoctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function notaConsulta()
    {
        return $this->hasOne(NotaConsulta::class, 'cita_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por');
    }

    public function checkinPor()
    {
        return $this->belongsTo(Usuario::class, 'checkin_por');
    }
}
