<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAuditoria extends Model
{
    use HasFactory;

    protected $table = 'registros_auditoria';

    protected $fillable = [
        'usuario_id',
        'accion',
        'tipo_entidad',
        'entidad_id',
        'valores_anteriores',
        'valores_nuevos',
        'direccion_ip',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos'     => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
