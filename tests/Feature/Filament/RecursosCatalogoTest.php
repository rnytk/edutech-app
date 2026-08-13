<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Colegios\Pages\CrearColegio;
use App\Filament\Resources\Colegios\Pages\EditarColegio;
use App\Filament\Resources\Colegios\Pages\ListarColegios;
use App\Filament\Resources\Cursos\Pages\CrearCurso;
use App\Filament\Resources\Cursos\Pages\EditarCurso;
use App\Filament\Resources\GradosAcademicos\Pages\CrearGradoAcademico;
use App\Filament\Resources\GradosAcademicos\Pages\EditarGradoAcademico;
use App\Filament\Resources\Niveles\Pages\CrearNivel;
use App\Filament\Resources\Niveles\Pages\EditarNivel;
use App\Models\Colegio;
use App\Models\Curso;
use App\Models\GradoAcademico;
use App\Models\Nivel;
use Livewire\Livewire;

class RecursosCatalogoTest extends PruebaFilament
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->autenticarSuperadministrador();
    }

    public function test_recursos_crean_colegio_grado_curso_y_nivel(): void
    {
        Livewire::test(CrearColegio::class)
            ->fillForm(['nombre' => 'Colegio Central', 'codigo' => 'CENTRAL', 'activo' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CrearGradoAcademico::class)
            ->fillForm(['nombre' => 'Primero básico', 'codigo' => '1B', 'orden' => 1, 'activo' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CrearCurso::class)
            ->fillForm([
                'titulo' => 'Ahorro inteligente',
                'descripcion' => '<p>Conceptos fundamentales.</p>',
                'orden' => 1,
                'publicado' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $curso = Curso::query()->where('titulo', 'Ahorro inteligente')->firstOrFail();

        Livewire::test(CrearNivel::class)
            ->fillForm([
                'curso_id' => $curso->getKey(),
                'titulo' => 'Fundamentos',
                'descripcion' => '<p>Nivel inicial.</p>',
                'orden' => 1,
                'publicado' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colegios', ['codigo' => 'CENTRAL']);
        $this->assertDatabaseHas('grados_academicos', ['codigo' => '1B']);
        $this->assertDatabaseHas('niveles', ['curso_id' => $curso->getKey(), 'titulo' => 'Fundamentos']);
    }

    public function test_codigos_unicos_se_validan_antes_de_persistir(): void
    {
        Colegio::factory()->create(['codigo' => 'DUPLICADO']);
        GradoAcademico::factory()->create(['codigo' => 'GRADO-DUPLICADO']);

        Livewire::test(CrearColegio::class)
            ->fillForm(['nombre' => 'Otro colegio', 'codigo' => 'DUPLICADO', 'activo' => true])
            ->call('create')
            ->assertHasFormErrors(['codigo' => 'unique']);

        Livewire::test(CrearGradoAcademico::class)
            ->fillForm(['nombre' => 'Otro grado', 'codigo' => 'GRADO-DUPLICADO', 'orden' => 2, 'activo' => true])
            ->call('create')
            ->assertHasFormErrors(['codigo' => 'unique']);
    }

    public function test_recursos_editan_colegio_grado_curso_y_nivel(): void
    {
        $colegio = Colegio::factory()->create();
        $grado = GradoAcademico::factory()->create();
        $curso = Curso::factory()->create();
        $nivel = Nivel::factory()->create(['curso_id' => $curso]);

        Livewire::test(EditarColegio::class, ['record' => $colegio->getRouteKey()])
            ->fillForm(['nombre' => 'Colegio actualizado'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditarGradoAcademico::class, ['record' => $grado->getRouteKey()])
            ->fillForm(['nombre' => 'Grado actualizado'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditarCurso::class, ['record' => $curso->getRouteKey()])
            ->fillForm(['titulo' => 'Curso actualizado'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditarNivel::class, ['record' => $nivel->getRouteKey()])
            ->fillForm(['titulo' => 'Nivel actualizado'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Colegio actualizado', $colegio->refresh()->nombre);
        $this->assertSame('Grado actualizado', $grado->refresh()->nombre);
        $this->assertSame('Curso actualizado', $curso->refresh()->titulo);
        $this->assertSame('Nivel actualizado', $nivel->refresh()->titulo);
    }

    public function test_tabla_de_colegios_renderiza_sus_registros(): void
    {
        $colegio = Colegio::factory()->create();

        Livewire::test(ListarColegios::class)
            ->assertCanSeeTableRecords([$colegio])
            ->assertSuccessful();
    }
}
