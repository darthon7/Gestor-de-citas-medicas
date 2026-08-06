<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\CitasRepository;
use App\Http\Repository\NotasConsultaRepository;
use App\Http\Requests\StoreNotaConsultaRequest;
use App\Models\Cita;
use App\Models\PerfilDoctor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorWebController extends Controller
{
    protected $citasRepository;
    protected $notasRepository;

    public function __construct(
        CitasRepository $citasRepository,
        NotasConsultaRepository $notasRepository
    ) {
        $this->citasRepository = $citasRepository;
        $this->notasRepository = $notasRepository;
    }

    public function agenda(Request $request)
    {
        $usuario = $request->user();
        $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

        if (!$perfilDoctor) {
            return redirect()->route('dashboard')->with('error', 'Perfil de médico no encontrado.');
        }

        $fecha = $request->query('fecha', Carbon::today()->format('Y-m-d'));

        $citas = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])
            ->where('perfil_doctor_id', $perfilDoctor->id)
            ->whereDate('fecha_cita', $fecha)
            ->orderBy('hora_cita', 'asc')
            ->get();

        return view('doctor.agenda', compact('citas', 'fecha', 'perfilDoctor'));
    }

    public function diagnostico(int $citaId)
    {
        $cita = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])->findOrFail($citaId);
        return view('doctor.diagnostico', compact('cita'));
    }

    public function iniciarConsulta($id)
    {
        try {
            $this->citasRepository->iniciarConsulta($id);
            return redirect()->route('doctor.diagnostico', $id)->with('success', 'Consulta iniciada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completarCita($id)
    {
        try {
            $this->citasRepository->completarCita($id);
            return redirect()->route('doctor.agenda')->with('success', 'Consulta finalizada con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function registrarNota(StoreNotaConsultaRequest $request, int $citaId)
    {
        try {
            $doctorUsuarioId = $request->user()->id;
            $this->notasRepository->registrarNota($citaId, $request->all(), $doctorUsuarioId);
            $this->citasRepository->completarCita($citaId);

            return redirect()->route('doctor.agenda')->with('success', 'Nota médica registrada y consulta completada.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
