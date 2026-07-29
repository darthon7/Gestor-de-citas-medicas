<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; color: #2a9d8f; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Agenda Médica - Reporte de Citas</h1>
    <p>Fecha de emisión: {{ date('d/m/Y H:i') }}</p>

    <table>
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
            @foreach($citas as $cita)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('d/m/Y H:i A') }}</td>
                    <td>{{ $cita['paciente']['nombre'] ?? 'N/A' }}</td>
                    <td>Dr. {{ $cita['doctor']['nombre'] ?? 'N/A' }}</td>
                    <td>{{ $cita['especialidad']['nombre'] ?? 'General' }}</td>
                    <td>{{ $cita['estado'] ?? 'Pendiente' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
