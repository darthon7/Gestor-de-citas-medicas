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

        return app(DashboardWebController::class)->index();
    }

    /**
     * Página pública de especialidades (sin autenticación).
     * Carga máximo 12 especialidades activas del backend.
     */
    public function especialidades()
    {
        // Máximo 12 especialidades activas, ordenadas por nombre
        $especialidades = Especialidad::where('activa', true)
            ->withCount('doctores')
            ->orderBy('nombre')
            ->take(12)
            ->get();

        // Las primeras 3 se muestran como "destacadas" en la sección superior
        $destacadas = $especialidades->take(3);

        // Conteos para el eyebrow del hero
        $totalEspecialidades = Especialidad::where('activa', true)->count();
        $totalDoctores       = PerfilDoctor::count();

        return view('especialidades-landing', compact(
            'especialidades',
            'destacadas',
            'totalEspecialidades',
            'totalDoctores'
        ));
    }
}

