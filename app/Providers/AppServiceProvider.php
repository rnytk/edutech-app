<?php

namespace App\Providers;

use App\Models\Curso;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\ProgresoModuloUsuario;
use App\Policies\CursoPolicy;
use App\Policies\IntentoActividadPolicy;
use App\Policies\ModuloPolicy;
use App\Policies\NivelPolicy;
use App\Policies\ProgresoModuloUsuarioPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Curso::class, CursoPolicy::class);
        Gate::policy(Nivel::class, NivelPolicy::class);
        Gate::policy(Modulo::class, ModuloPolicy::class);
        Gate::policy(IntentoActividad::class, IntentoActividadPolicy::class);
        Gate::policy(ProgresoModuloUsuario::class, ProgresoModuloUsuarioPolicy::class);
    }
}
