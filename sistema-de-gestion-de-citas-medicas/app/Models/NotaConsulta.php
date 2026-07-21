<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaConsulta extends Model
{
    use HasFactory;

    protected $table = 'notas_consulta';

    protected $fillable = [
        'cita_id',
        'diagnostico',
        'tratamiento',
        'notas_adicionales',
        'creado_por',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
