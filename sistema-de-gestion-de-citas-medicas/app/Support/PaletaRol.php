<?php

namespace App\Support;

class PaletaRol
{
    public const ROL_ADMIN = 'admin';
    public const ROL_RECEPCIONISTA = 'recepcionista';
    public const ROL_DOCTOR = 'doctor';
    public const ROL_PACIENTE = 'paciente';

    /**
     * Tokens compartidos por todos los roles (no son "color de marca").
     * Incluye estados de cita, superficies, texto y colores de alerta.
     */
    public const BASE = [
        'secondary' => '#006a60',
        'secondary-light' => '#B5E8D5',
        'secondary-container' => '#BDE3DB',
        'on-secondary-container' => '#00201C',
        'tertiary' => '#885c00',
        'tertiary-fixed-dim' => '#ffba42',
        'danger' => '#E76F51',
        'danger-light' => '#FADED4',
        'error' => '#ba1a1a',
        'warning-gold' => '#E9A319',
        'background' => '#f7f9fc',
        'surface' => '#FFFFFF',
        'surface-dim' => '#D9DFE6',
        'surface-variant' => '#E7ECF2',
        'surface-container-low' => '#EFF3F7',
        'surface-container-high' => '#E4EAF1',
        'border' => '#E2E8F0',
        'text-primary' => '#1A1A2E',
        'text-secondary' => '#4A5568',
        'text-muted' => '#A0AEC0',
        'on-surface' => '#191c1e',
        'on-surface-variant' => '#40484e',
    ];

    /**
     * Paleta de marca por defecto (usada cuando no hay sesión o rol desconocido).
     */
    public const DEFAULT = [
        'primary' => '#005275',
        'primary-dark' => '#0F4C6B',
        'primary-light' => '#A8D5E2',
        'primary-container' => '#1b6b93',
        'primary-fixed' => '#CBE7F0',
        'on-primary' => '#ffffff',
        'on-primary-fixed-variant' => '#0F4C6B',
    ];

    /**
     * Sobrescrituras de la familia "primary" por rol.
     * Agregar un rol nuevo = agregar una entrada aquí (nunca tocar vistas).
     */
    public const PALETAS = [
        self::ROL_ADMIN => [
            'primary' => '#1B5E20',
            'primary-dark' => '#0D3B12',
            'primary-light' => '#A5D6A7',
            'primary-container' => '#2E7D32',
            'primary-fixed' => '#C8E6C9',
            'on-primary' => '#ffffff',
            'on-primary-fixed-variant' => '#1B5E20',
        ],
        self::ROL_RECEPCIONISTA => [
            'primary' => '#059669',
            'primary-dark' => '#065F46',
            'primary-light' => '#A7F3D0',
            'primary-container' => '#10B981',
            'primary-fixed' => '#D1FAE5',
            'on-primary' => '#ffffff',
            'on-primary-fixed-variant' => '#064E3B',
        ],
        self::ROL_DOCTOR => [
            'primary' => '#1E40AF',
            'primary-dark' => '#172554',
            'primary-light' => '#BFDBFE',
            'primary-container' => '#2563EB',
            'primary-fixed' => '#DBEAFE',
            'on-primary' => '#ffffff',
            'on-primary-fixed-variant' => '#172554',
        ],
        self::ROL_PACIENTE => [
            'primary' => '#0284C7',
            'primary-dark' => '#075985',
            'primary-light' => '#BAE6FD',
            'primary-container' => '#0EA5E9',
            'primary-fixed' => '#E0F2FE',
            'on-primary' => '#ffffff',
            'on-primary-fixed-variant' => '#0C4A6E',
        ],
    ];

    /**
     * Resuelve la paleta completa (base + marca del rol) para el rol dado.
     */
    public static function para(?string $rol): array
    {
        $paleta = array_merge(self::BASE, self::DEFAULT);

        if ($rol !== null && isset(self::PALETAS[$rol])) {
            $paleta = array_merge($paleta, self::PALETAS[$rol]);
        }

        return $paleta;
    }

    /**
     * Convierte un color hex a "r, g, b" para usar en rgba().
     */
    public static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return hexdec(substr($hex, 0, 2)).', '.hexdec(substr($hex, 2, 2)).', '.hexdec(substr($hex, 4, 2));
    }

    /**
     * Genera las variables CSS de marca para el bloque <style> del layout.
     */
    public static function cssVars(array $paleta): string
    {
        $vars = '';

        foreach (['primary', 'primary-dark', 'primary-light', 'primary-container', 'on-primary'] as $clave) {
            if (isset($paleta[$clave])) {
                $vars .= "        --{$clave}: {$paleta[$clave]};\n";
            }
        }

        $vars .= '        --primary-container-rgb: '.self::rgb($paleta['primary-container']).";\n";

        return rtrim($vars, "\n");
    }
}
