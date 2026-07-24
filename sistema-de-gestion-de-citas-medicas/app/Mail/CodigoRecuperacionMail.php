<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;

    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    public function build()
    {
        return $this->subject('Código de recuperación de contraseña')
                    ->html("
                        <h2>Recuperación de Contraseña</h2>
                        <p>Has solicitado restablecer tu contraseña en el Sistema de Citas Médicas.</p>
                        <p>Tu código de verificación de 6 dígitos es:</p>
                        <h1 style='color: #007bff; letter-spacing: 5px;'>{$this->codigo}</h1>
                        <p>Este código expira en 30 minutos.</p>
                        <p>Si no solicitaste este cambio, ignora este correo.</p>
                    ");
    }
}
