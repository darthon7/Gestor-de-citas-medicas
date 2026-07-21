<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'curp',
        'telefono',
        'rol',
        'estado',
        'foto_perfil',
        'intentos_fallidos',
        'bloqueado_hasta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'       => 'hashed',
        'bloqueado_hasta' => 'datetime',
    ];

    public function perfilDoctor()
    {
        return $this->hasOne(PerfilDoctor::class, 'usuario_id');
    }

    public function perfilPaciente()
    {
        return $this->hasOne(PerfilPaciente::class, 'usuario_id');
    }

    public function perfilRecepcionista()
    {
        return $this->hasOne(PerfilRecepcionista::class, 'usuario_id');
    }

    public function registrosAuditoria()
    {
        return $this->hasMany(RegistroAuditoria::class, 'usuario_id');
    }
}
