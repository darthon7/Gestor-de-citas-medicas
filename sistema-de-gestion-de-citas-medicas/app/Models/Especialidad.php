<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function doctores()
    {
        return $this->belongsToMany(PerfilDoctor::class, 'doctor_especialidad', 'especialidad_id', 'perfil_doctor_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }
}
