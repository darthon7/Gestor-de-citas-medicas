<?php

namespace App\Providers;

use App\Support\PaletaRol;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function (\Illuminate\View\View $view) {
            $paleta = PaletaRol::para(auth()->user()?->rol);

            $view->with([
                'paleta' => $paleta,
                'paletaCssVars' => PaletaRol::cssVars($paleta),
            ]);
        });
    }
}
