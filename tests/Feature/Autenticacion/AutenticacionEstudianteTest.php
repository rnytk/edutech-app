<?php

namespace Tests\Feature\Autenticacion;

use App\Enums\RolUsuario;
use App\Filament\Pages\Autenticacion\IniciarSesion as IniciarSesionAdministrativa;
use App\Livewire\Autenticacion\IniciarSesion;
use App\Models\Usuario;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Feature\Dominio\PruebaDominio;

class AutenticacionEstudianteTest extends PruebaDominio
{
    public function test_visitante_puede_ver_login_livewire_sin_propiedad_publica_de_contrasena(): void
    {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSeeLivewire(IniciarSesion::class)
            ->assertSee('Ingresa a tu cuenta')
            ->assertSee('correo_electronico');

        $this->assertFalse(property_exists(IniciarSesion::class, 'contrasena'));
    }

    public function test_estudiante_activo_puede_autenticarse_y_es_redirigido(): void
    {
        $estudiante = $this->estudianteConClave('estudiante@katoki.test');

        $this->post(route('estudiante.login.autenticar'), [
            'correo_electronico' => $estudiante->correo_electronico,
            'contrasena' => 'ClaveSegura123',
        ])->assertRedirect(route('portal.inicio'));

        $this->assertAuthenticatedAs($estudiante);
    }

    public function test_sesion_se_regenera_al_autenticar(): void
    {
        $estudiante = $this->estudianteConClave('sesion@katoki.test');
        $this->withSession(['marcador' => 'presente']);
        $identificadorAnterior = session()->getId();

        $this->post(route('estudiante.login.autenticar'), [
            'correo_electronico' => $estudiante->correo_electronico,
            'contrasena' => 'ClaveSegura123',
        ])->assertRedirect(route('portal.inicio'));

        $this->assertNotSame($identificadorAnterior, session()->getId());
        $this->assertSame('presente', session('marcador'));
    }

    public function test_contrasena_incorrecta_se_rechaza_con_mensaje_generico_y_no_se_flasha(): void
    {
        $estudiante = $this->estudianteConClave('incorrecta@katoki.test');

        $this->post(route('estudiante.login.autenticar'), [
            'correo_electronico' => $estudiante->correo_electronico,
            'contrasena' => 'ClaveIncorrecta123',
        ])
            ->assertSessionHasErrors([
                'correo_electronico' => 'Las credenciales proporcionadas no son válidas.',
            ])
            ->assertSessionMissing('_old_input.contrasena');

        $this->assertGuest();
    }

    public function test_estudiante_inactivo_es_rechazado_con_el_mismo_mensaje_generico(): void
    {
        $estudiante = $this->estudianteConClave('inactivo@katoki.test', ['activo' => false]);

        $this->post(route('estudiante.login.autenticar'), [
            'correo_electronico' => $estudiante->correo_electronico,
            'contrasena' => 'ClaveSegura123',
        ])->assertSessionHasErrors([
            'correo_electronico' => 'Las credenciales proporcionadas no son válidas.',
        ]);

        $this->assertGuest();
    }

    public function test_superadministrador_es_rechazado_en_login_estudiantil(): void
    {
        $administrador = Usuario::factory()->superadministrador()->create([
            'correo_electronico' => 'admin-portal@katoki.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);

        $this->post(route('estudiante.login.autenticar'), [
            'correo_electronico' => $administrador->correo_electronico,
            'contrasena' => 'ClaveSegura123',
        ])->assertSessionHasErrors([
            'correo_electronico' => 'Las credenciales proporcionadas no son válidas.',
        ]);

        $this->assertGuest();
    }

    public function test_rate_limiting_bloquea_el_sexto_intento_fallido(): void
    {
        $estudiante = $this->estudianteConClave('limite@katoki.test');
        $datos = [
            'correo_electronico' => $estudiante->correo_electronico,
            'contrasena' => 'ClaveIncorrecta123',
        ];

        foreach (range(1, 5) as $intento) {
            $this->post(route('estudiante.login.autenticar'), $datos)
                ->assertSessionHasErrors('correo_electronico');
        }

        $this->post(route('estudiante.login.autenticar'), $datos)
            ->assertSessionHasErrors('correo_electronico');

        $this->assertStringContainsString(
            'Demasiados intentos.',
            session('errors')->first('correo_electronico'),
        );
    }

    public function test_visitante_no_puede_entrar_al_portal_privado(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_estudiante_autenticado_puede_ver_layout_privado(): void
    {
        $estudiante = Usuario::factory()->create(['nombre' => 'Ana Estudiante']);

        $this->actingAs($estudiante)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSee('¡Bienvenido, Ana Estudiante!')
            ->assertSee('Cerrar sesión');
    }

    public function test_estudiante_inactivo_con_sesion_es_expulsado_del_portal(): void
    {
        $estudiante = Usuario::factory()->inactivo()->create();

        $this->actingAs($estudiante)
            ->get('/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_superadministrador_no_puede_entrar_al_portal_y_conserva_su_sesion(): void
    {
        $administrador = Usuario::factory()->superadministrador()->create();

        $this->actingAs($administrador)
            ->get('/dashboard')
            ->assertForbidden();

        $this->assertAuthenticatedAs($administrador);
    }

    public function test_estudiante_puede_cerrar_sesion_de_forma_segura(): void
    {
        $estudiante = Usuario::factory()->create();
        $this->withSession(['_token' => 'token-anterior']);

        $this->actingAs($estudiante)
            ->post(route('estudiante.logout'))
            ->assertRedirect(route('estudiante.login'));

        $this->assertGuest();
        $this->assertNotSame('token-anterior', session()->token());
    }

    public function test_login_administrativo_continua_funcionando_independientemente(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $administrador = Usuario::factory()->superadministrador()->create([
            'correo_electronico' => 'admin-independiente@katoki.test',
            'contrasena' => Hash::make('ClaveAdministrativa123'),
        ]);

        $this->get('/admin/login')->assertSuccessful();

        Livewire::test(IniciarSesionAdministrativa::class)
            ->fillForm([
                'email' => $administrador->correo_electronico,
                'password' => 'ClaveAdministrativa123',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($administrador);
        $this->assertTrue($administrador->canAccessPanel(Filament::getPanel('admin')));
    }

    /** @param array<string, mixed> $atributos */
    private function estudianteConClave(string $correoElectronico, array $atributos = []): Usuario
    {
        return Usuario::factory()->create([
            'correo_electronico' => $correoElectronico,
            'contrasena' => Hash::make('ClaveSegura123'),
            'rol' => RolUsuario::Estudiante,
            'activo' => true,
            ...$atributos,
        ]);
    }
}
