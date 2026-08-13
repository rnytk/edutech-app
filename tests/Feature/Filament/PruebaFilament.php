<?php

namespace Tests\Feature\Filament;

use App\Models\Usuario;
use Filament\Facades\Filament;
use Tests\Feature\Dominio\PruebaDominio;

abstract class PruebaFilament extends PruebaDominio
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function autenticarSuperadministrador(): Usuario
    {
        $usuario = Usuario::factory()->superadministrador()->create();
        $this->actingAs($usuario);

        return $usuario;
    }
}
