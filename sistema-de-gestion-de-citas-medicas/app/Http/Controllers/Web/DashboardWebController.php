<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardWebController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today()->format('Y-m-d');

        $citasHoy = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
            ->whereDate('fecha_cita', $hoy)
            ->orderBy('hora_cita', 'asc')
            ->get();

        $statTotalDia = $citasHoy->count();
        $statCompletadas = $citasHoy->where('estado', 'completada')->count();
        $statPendientes = $citasHoy->whereIn('estado', ['agendada', 'confirmada'])->count();
        $statCanceladas = $citasHoy->where('estado', 'cancelada')->count();

        $proximasCitas = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
            ->where('fecha_cita', '>=', Carbon::today()->format('Y-m-d'))
            ->whereIn('estado', ['agendada', 'confirmada'])
            ->orderBy('fecha_cita', 'asc')
            ->orderBy('hora_cita', 'asc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'citasHoy',
            'statTotalDia',
            'statCompletadas',
            'statPendientes',
            'statCanceladas',
            'proximasCitas'
        ));
    }
}
