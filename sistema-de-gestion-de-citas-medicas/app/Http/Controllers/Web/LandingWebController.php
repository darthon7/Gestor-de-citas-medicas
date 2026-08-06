<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
}
