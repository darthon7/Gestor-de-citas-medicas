<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\PerfilDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingWebController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function home(Request $request)
    {
        if (!Auth::check()) {
            return view('landing');
        }

        if (Auth::user()->rol === 'doctor') {
            return redirect()->route('doctor.agenda');
        }

        return app(DashboardWebController::class)->index();
    }

    /**
     * Página pública de especialidades (sin autenticación).
     * Carga todas las especialidades activas del backend.
     */
    public function especialidades()
    {
        // Todas las especialidades activas ordenadas alfabéticamente
        $especialidades = Especialidad::where('activa', true)
            ->withCount('doctores')
            ->orderBy('nombre')
            ->get();

        // Conteos para el eyebrow del hero
        $totalEspecialidades = $especialidades->count();
        $totalDoctores       = PerfilDoctor::count();

        return view('especialidades-landing', compact(
            'especialidades',
            'totalEspecialidades',
            'totalDoctores'
        ));
    }
}

