<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilPaciente extends Model
{
    use HasFactory;

    protected $table = 'perfiles_paciente';

    protected $fillable = [
        'usuario_id',
        'numero_expediente',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'nss',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'perfil_paciente_id');
    }
}
