<?php

namespace Tests\Feature\Filament;

use App\Enums\RolUsuario;
use App\Filament\Resources\Usuarios\Pages\CrearUsuario;
use App\Filament\Resources\Usuarios\Pages\EditarUsuario;
use App\Filament\Resources\Usuarios\Pages\ListarUsuarios;
use App\Models\Colegio;
use App\Models\GradoAcademico;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

class UsuarioResourceTest extends PruebaFilament
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->autenticarSuperadministrador();
    }

    public function test_estudiante_requiere_colegio_y_grado(): void
    {
        Livewire::test(CrearUsuario::class)
            ->fillForm([
                'nombre' => 'Estudiante sin asignación',
                'correo_electronico' => 'sin-asignacion@katoki.test',
                'rol' => RolUsuario::Estudiante->value,
                'activo' => true,
                'contrasena' => 'ClaveSegura123',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'colegio_id' => 'required',
                'grado_academico_id' => 'required',
            ]);
    }

    public function test_rechaza_rol_fuera_de_los_permitidos(): void
    {
        Livewire::test(CrearUsuario::class)
            ->fillForm([
                'nombre' => 'Usuario inválido',
                'correo_electronico' => 'rol-invalido@katoki.test',
                'rol' => 'docente',
                'activo' => true,
                'contrasena' => 'ClaveSegura123',
            ])
            ->call('create')
            ->assertHasFormErrors(['rol']);
    }

    public function test_crea_usuario_cifra_contrasena_y_limpia_relaciones_de_superadministrador(): void
    {
        $colegio = Colegio::factory()->create();
        $grado = GradoAcademico::factory()->create();

        Livewire::test(CrearUsuario::class)
            ->fillForm([
                'nombre' => 'Nueva estudiante',
                'correo_electronico' => 'estudiante@katoki.test',
                'rol' => RolUsuario::Estudiante->value,
                'colegio_id' => $colegio->getKey(),
                'grado_academico_id' => $grado->getKey(),
                'activo' => true,
                'contrasena' => 'ClaveSegura123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $estudiante = Usuario::query()->where('correo_electronico', 'estudiante@katoki.test')->firstOrFail();
        $this->assertTrue(Hash::check('ClaveSegura123', $estudiante->contrasena));

        Livewire::test(CrearUsuario::class)
            ->fillForm([
                'nombre' => 'Administradora',
                'correo_electronico' => 'otra-admin@katoki.test',
                'rol' => RolUsuario::Superadministrador->value,
                'colegio_id' => $colegio->getKey(),
                'grado_academico_id' => $grado->getKey(),
                'activo' => true,
                'contrasena' => 'OtraClaveSegura123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $administradora = Usuario::query()->where('correo_electronico', 'otra-admin@katoki.test')->firstOrFail();
        $this->assertNull($administradora->colegio_id);
        $this->assertNull($administradora->grado_academico_id);
    }

    public function test_edicion_no_duplica_hash_y_solo_cambia_contrasena_si_se_envia_una_nueva(): void
    {
        $usuario = Usuario::factory()->create([
            'contrasena' => Hash::make('ClaveOriginal123'),
        ]);
        $hashOriginal = $usuario->contrasena;

        Livewire::test(EditarUsuario::class, ['record' => $usuario->getRouteKey()])
            ->fillForm(['nombre' => 'Nombre actualizado', 'contrasena' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($hashOriginal, $usuario->refresh()->contrasena);

        Livewire::test(EditarUsuario::class, ['record' => $usuario->getRouteKey()])
            ->fillForm(['contrasena' => 'ClaveNueva123'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('ClaveNueva123', $usuario->refresh()->contrasena));
        $this->assertFalse(Hash::check($hashOriginal, $usuario->contrasena));
    }

    public function test_tabla_de_usuarios_no_renderiza_hash_de_contrasena(): void
    {
        $usuario = Usuario::factory()->create([
            'contrasena' => Hash::make('ClaveNoVisible123'),
        ]);

        Livewire::test(ListarUsuarios::class)
            ->assertCanSeeTableRecords([$usuario])
            ->assertDontSee($usuario->contrasena)
            ->assertSuccessful();
    }
}
