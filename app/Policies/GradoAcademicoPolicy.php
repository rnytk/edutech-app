<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\GradoAcademico;
use App\Models\Usuario;

class GradoAcademicoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function view(Usuario $usuario, GradoAcademico $gradoAcademico): bool
    {
        return $this->administra($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function update(Usuario $usuario, GradoAcademico $gradoAcademico): bool
    {
        return $this->administra($usuario);
    }

    public function delete(Usuario $usuario, GradoAcademico $gradoAcademico): bool
    {
        return false;
    }

    private function administra(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
