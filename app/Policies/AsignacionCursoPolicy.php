<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\AsignacionCurso;
use App\Models\Usuario;

class AsignacionCursoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function view(Usuario $usuario, AsignacionCurso $asignacionCurso): bool
    {
        return $this->administra($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function update(Usuario $usuario, AsignacionCurso $asignacionCurso): bool
    {
        return $this->administra($usuario);
    }

    public function delete(Usuario $usuario, AsignacionCurso $asignacionCurso): bool
    {
        return false;
    }

    private function administra(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
