<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\CitasRepository;
use App\Http\Repository\HorariosRepository;
use App\Http\Repository\NotasConsultaRepository;
use App\Http\Requests\StoreHorarioRequest;
use App\Http\Requests\StoreNotaConsultaRequest;
use App\Models\Cita;
use App\Models\HorarioDoctor;
use App\Models\PerfilDoctor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorWebController extends Controller
{
    protected $citasRepository;
    protected $notasRepository;
    protected $horariosRepository;

    public function __construct(
        CitasRepository $citasRepository,
        NotasConsultaRepository $notasRepository,
        HorariosRepository $horariosRepository
    ) {
        $this->citasRepository = $citasRepository;
        $this->notasRepository = $notasRepository;
        $this->horariosRepository = $horariosRepository;
    }

    public function agenda(Request $request)
    {
        $usuario = $request->user();
        $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

        if (!$perfilDoctor) {
            return redirect()->route('dashboard')->with('error', 'Perfil de médico no encontrado.');
        }

        $fecha = $request->query('fecha'); // Si es null, trae todas las fechas
        $estado = $request->query('estado');

        $query = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])
            ->where('perfil_doctor_id', $perfilDoctor->id);

        if (!empty($fecha)) {
            $query->whereDate('fecha_cita', $fecha);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $citas = $query->orderBy('fecha_cita', 'desc')
            ->orderBy('hora_cita', 'asc')
            ->get();

        return view('doctor.agenda', compact('citas', 'fecha', 'estado', 'perfilDoctor'));
    }

    public function horario(Request $request)
    {
        try {
            $usuario = $request->user();
            $perfilDoctor = PerfilDoctor::with(['especialidades'])->where('usuario_id', $usuario->id)->first();

            if (!$perfilDoctor) {
                return redirect()->route('dashboard')->with('error', 'Perfil de médico no encontrado.');
            }

            $horariosRes = $this->horariosRepository->obtenerHorarios($perfilDoctor->id);
            $horarios = isset($horariosRes['data']) ? collect($horariosRes['data']) : collect();

            return view('doctor.horario', compact('perfilDoctor', 'horarios'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function storeHorario(StoreHorarioRequest $request)
    {
        try {
            $usuario = $request->user();
            $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

            if (!$perfilDoctor) {
                return back()->with('error', 'Perfil de médico no encontrado.');
            }

            $res = $this->horariosRepository->registrarHorario($perfilDoctor->id, $request->all());

            if (isset($res['mensaje']) && !isset($res['data'])) {
                return back()->withInput()->with('error', $res['mensaje']);
            }

            return back()->with('success', 'Horario de atención guardado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateHorario(Request $request, $id)
    {
        try {
            $usuario = $request->user();
            $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

            $horario = HorarioDoctor::find($id);
            if (!$horario || $horario->perfil_doctor_id !== $perfilDoctor?->id) {
                return back()->with('error', 'Horario no encontrado o no autorizado.');
            }

            $res = $this->horariosRepository->actualizarHorario($id, $request->all());

            if (isset($res['mensaje']) && !isset($res['data'])) {
                return back()->withInput()->with('error', $res['mensaje']);
            }

            return back()->with('success', 'Horario de atención actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteHorario(Request $request, $id)
    {
        try {
            $usuario = $request->user();
            $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

            $horario = HorarioDoctor::find($id);
            if (!$horario || $horario->perfil_doctor_id !== $perfilDoctor?->id) {
                return back()->with('error', 'Horario no encontrado o no autorizado.');
            }

            $res = $this->horariosRepository->eliminarHorario($id);
            return back()->with('success', $res['mensaje'] ?? 'Horario eliminado con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function diagnostico(int $citaId)
    {
        $cita = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])->findOrFail($citaId);
        return view('doctor.diagnostico', compact('cita'));
    }

    public function iniciarConsulta($id)
    {
        try {
            $res = $this->citasRepository->iniciarConsulta($id);
            if (isset($res['mensaje']) && !isset($res['data'])) {
                return back()->with('error', $res['mensaje']);
            }
            return redirect()->route('doctor.diagnostico', $id)->with('success', 'Consulta iniciada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completarCita($id)
    {
        try {
            $res = $this->citasRepository->completarCita($id);
            if (isset($res['mensaje']) && !isset($res['data'])) {
                return back()->with('error', $res['mensaje']);
            }
            return redirect()->route('doctor.agenda')->with('success', 'Consulta finalizada con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function registrarNota(StoreNotaConsultaRequest $request, int $citaId)
    {
        try {
            $doctorUsuarioId = $request->user()->id;
            $resNota = $this->notasRepository->registrarNota($citaId, $request->all(), $doctorUsuarioId);

            if (isset($resNota['mensaje']) && !isset($resNota['data'])) {
                return back()->withInput()->with('error', $resNota['mensaje']);
            }

            return redirect()->route('doctor.agenda')->with('success', 'Nota médica registrada y consulta finalizada.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
