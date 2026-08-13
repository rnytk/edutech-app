<?php

namespace App\Providers;

use App\Models\AsignacionCurso;
use App\Models\Capsula;
use App\Models\Colegio;
use App\Models\Curso;
use App\Models\GradoAcademico;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use App\Policies\AsignacionCursoPolicy;
use App\Policies\CapsulaPolicy;
use App\Policies\ColegioPolicy;
use App\Policies\CursoPolicy;
use App\Policies\GradoAcademicoPolicy;
use App\Policies\IntentoActividadPolicy;
use App\Policies\ModuloPolicy;
use App\Policies\NivelPolicy;
use App\Policies\ProgresoModuloUsuarioPolicy;
use App\Policies\UsuarioPolicy;
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
        Gate::policy(Colegio::class, ColegioPolicy::class);
        Gate::policy(GradoAcademico::class, GradoAcademicoPolicy::class);
        Gate::policy(Usuario::class, UsuarioPolicy::class);
        Gate::policy(Curso::class, CursoPolicy::class);
        Gate::policy(Nivel::class, NivelPolicy::class);
        Gate::policy(Modulo::class, ModuloPolicy::class);
        Gate::policy(Capsula::class, CapsulaPolicy::class);
        Gate::policy(AsignacionCurso::class, AsignacionCursoPolicy::class);
        Gate::policy(IntentoActividad::class, IntentoActividadPolicy::class);
        Gate::policy(ProgresoModuloUsuario::class, ProgresoModuloUsuarioPolicy::class);
    }
}
