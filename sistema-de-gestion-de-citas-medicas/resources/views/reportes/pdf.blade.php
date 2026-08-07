<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #005275; padding-bottom: 10px; }
        h1 { font-size: 18px; color: #005275; margin: 0 0 5px 0; }
        .subtitle { font-size: 11px; color: #666; }
        .stats { margin-bottom: 15px; width: 100%; border-collapse: collapse; }
        .stats td { padding: 6px 10px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px; text-align: center; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data th { background-color: #005275; color: #ffffff; font-weight: bold; font-size: 10px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: capitalize; display: inline-block; }
        .badge-agendada { background: #e0f2fe; color: #0369a1; }
        .badge-confirmada { background: #dcfce7; color: #15803d; }
        .badge-en_consulta { background: #fef3c7; color: #b45309; }
        .badge-completada { background: #d1fae5; color: #047857; }
        .badge-cancelada { background: #ffe4e6; color: #be123c; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Agenda Médica — Reporte de Citas</h1>
        <div class="subtitle">Fecha de emisión: {{ date('d/m/Y H:i') }}</div>
    </div>

    <table class="stats">
        <tr>
            <td><strong>Total Citas:</strong> {{ $total ?? count($citas) }}</td>
            <td><strong>Agendadas:</strong> {{ $agendadas ?? 0 }}</td>
            <td><strong>Confirmadas:</strong> {{ $confirmadas ?? 0 }}</td>
            <td><strong>Completadas:</strong> {{ $completadas ?? 0 }}</td>
            <td><strong>Canceladas:</strong> {{ $canceladas ?? 0 }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Paciente</th>
                <th>Doctor</th>
                <th>Especialidad</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citas as $cita)
                @php
                    $fecha = is_object($cita) && isset($cita->fecha_cita) 
                        ? ($cita->fecha_cita instanceof \Carbon\Carbon ? $cita->fecha_cita->format('d/m/Y') : \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y'))
                        : (\Carbon\Carbon::parse($cita['fecha_cita'] ?? $cita['fecha_hora'] ?? now())->format('d/m/Y'));
                    $hora = is_object($cita) ? ($cita->hora_cita ?? '') : ($cita['hora_cita'] ?? '');
                    
                    $pacienteNombre = is_object($cita)
                        ? ($cita->perfilPaciente?->usuario?->nombre ?? 'N/A')
                        : ($cita['perfil_paciente']['usuario']['nombre'] ?? $cita['paciente']['nombre'] ?? 'N/A');

                    $doctorNombre = is_object($cita)
                        ? ($cita->perfilDoctor?->usuario?->nombre ?? 'N/A')
                        : ($cita['perfil_doctor']['usuario']['nombre'] ?? $cita['doctor']['nombre'] ?? 'N/A');

                    $especialidadNombre = is_object($cita)
                        ? ($cita->especialidad?->nombre ?? 'General')
                        : ($cita['especialidad']['nombre'] ?? 'General');

                    $estado = is_object($cita) ? ($cita->estado ?? 'agendada') : ($cita['estado'] ?? 'agendada');
                @endphp
                <tr>
                    <td>{{ $fecha }} {{ $hora }}</td>
                    <td>{{ $pacienteNombre }}</td>
                    <td>Dr. {{ $doctorNombre }}</td>
                    <td>{{ $especialidadNombre }}</td>
                    <td>
                        <span class="badge badge-{{ $estado }}">{{ str_replace('_', ' ', $estado) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8;">No se encontraron registros de citas para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por el Sistema de Gestión de Citas Médicas.
    </div>
</body>
</html>
