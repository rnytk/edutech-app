<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Curso;
use App\Models\Usuario;
use App\Services\ServicioAccesoCursos;

class CursoPolicy
{
    public function __construct(
        private readonly ServicioAccesoCursos $servicioAccesoCursos,
    ) {}

    public function viewAny(Usuario $usuario): bool
    {
        return $this->esSuperadministradorActivo($usuario)
            || $this->servicioAccesoCursos->usuarioPuedeConsumirContenido($usuario);
    }

    public function view(Usuario $usuario, Curso $curso): bool
    {
        return $this->esSuperadministradorActivo($usuario)
            || $this->servicioAccesoCursos->puedeAcceder($usuario, $curso);
    }

    private function esSuperadministradorActivo(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
