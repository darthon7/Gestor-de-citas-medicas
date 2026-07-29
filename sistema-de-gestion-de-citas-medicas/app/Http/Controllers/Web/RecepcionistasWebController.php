<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\AuthRepository;
use App\Http\Requests\StoreRegistroRecepcionistaRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;

class RecepcionistasWebController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function index()
    {
        $recepcionistas = Usuario::where('rol', 'recepcionista')->orderBy('id', 'desc')->get();
        return view('recepcionistas.index', compact('recepcionistas'));
    }

    public function store(StoreRegistroRecepcionistaRequest $request)
    {
        try {
            $adminId = $request->user()->id;
            $this->authRepository->registrarRecepcionista($request->all(), $adminId);
            return redirect()->route('recepcionistas.index')->with('success', 'Recepcionista registrada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
