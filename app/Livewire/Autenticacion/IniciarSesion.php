<?php

namespace App\Livewire\Autenticacion;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.autenticacion')]
class IniciarSesion extends Component
{
    public function mount(): void
    {
        $usuario = auth()->user();

        if ($usuario instanceof Usuario
            && $usuario->activo
            && $usuario->rol === RolUsuario::Estudiante) {
            $this->redirectRoute('portal.inicio', navigate: true);
        }
    }

    public function render(): View
    {
        return view('livewire.autenticacion.iniciar-sesion');
    }
}
