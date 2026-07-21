<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilRecepcionista extends Model
{
    use HasFactory;

    protected $table = 'perfiles_recepcionista';

    protected $fillable = [
        'usuario_id',
        'numero_empleado',
        'unidad_asignada',
        'turno',
        'creado_por_admin_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_admin_id');
    }
}
