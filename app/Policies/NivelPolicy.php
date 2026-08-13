<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Nivel;
use App\Models\Usuario;
use App\Services\ServicioDesbloqueo;

class NivelPolicy
{
    public function __construct(
        private readonly ServicioDesbloqueo $servicioDesbloqueo,
    ) {}

    public function viewAny(Usuario $usuario): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function view(Usuario $usuario, Nivel $nivel): bool
    {
        if ($this->esSuperadministradorActivo($usuario)) {
            return true;
        }

        return $this->servicioDesbloqueo->nivelEstaDesbloqueado($usuario, $nivel);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function update(Usuario $usuario, Nivel $nivel): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function delete(Usuario $usuario, Nivel $nivel): bool
    {
        return false;
    }

    private function esSuperadministradorActivo(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
