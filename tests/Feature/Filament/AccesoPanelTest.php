<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\Autenticacion\IniciarSesion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

class AccesoPanelTest extends PruebaFilament
{
    public function test_visitante_es_redirigido_al_login_administrativo(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_solo_superadministrador_activo_puede_abrir_el_panel(): void
    {
        $administrador = Usuario::factory()->superadministrador()->create();
        $this->actingAs($administrador)->get('/admin')->assertSuccessful();

        auth()->logout();
        $estudiante = Usuario::factory()->create();
        $this->actingAs($estudiante)->get('/admin')->assertForbidden();

        auth()->logout();
        $administradorInactivo = Usuario::factory()->superadministrador()->inactivo()->create();
        $this->actingAs($administradorInactivo)->get('/admin')->assertForbidden();
    }

    public function test_login_filament_autentica_con_los_campos_espanoles_de_usuario(): void
    {
        $administrador = Usuario::factory()->superadministrador()->create([
            'correo_electronico' => 'admin@katoki.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);

        Livewire::test(IniciarSesion::class)
            ->fillForm([
                'email' => 'admin@katoki.test',
                'password' => 'ClaveSegura123',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($administrador);
    }
}
