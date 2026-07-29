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
            ->whereDate('fecha_hora', $hoy)
            ->orderBy('fecha_hora', 'asc')
            ->get();

        $statTotalDia = $citasHoy->count();
        $statCompletadas = $citasHoy->where('estado', 'completada')->count();
        $statPendientes = $citasHoy->whereIn('estado', ['pendiente', 'confirmada'])->count();
        $statCanceladas = $citasHoy->where('estado', 'cancelada')->count();

        $proximasCitas = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
            ->where('fecha_hora', '>=', Carbon::now())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_hora', 'asc')
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
