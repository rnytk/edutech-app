<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AsignacionesCursos\Pages\CrearAsignacionCurso;
use App\Filament\Resources\AsignacionesCursos\Pages\EditarAsignacionCurso;
use App\Models\AsignacionCurso;
use App\Models\Colegio;
use App\Models\Curso;
use Livewire\Livewire;

class AsignacionCursoResourceTest extends PruebaFilament
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->autenticarSuperadministrador();
    }

    public function test_crea_asignacion_general_y_rechaza_duplicado_desde_el_formulario(): void
    {
        $curso = Curso::factory()->create();
        $colegio = Colegio::factory()->create();
        $datos = [
            'curso_id' => $curso->getKey(),
            'colegio_id' => $colegio->getKey(),
            'grado_academico_id' => null,
            'activo' => true,
            'inicia_en' => null,
            'finaliza_en' => null,
        ];

        Livewire::test(CrearAsignacionCurso::class)
            ->fillForm($datos)
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CrearAsignacionCurso::class)
            ->fillForm($datos)
            ->call('create')
            ->assertHasFormErrors(['grado_academico_id']);

        $this->assertSame(1, AsignacionCurso::query()
            ->where('curso_id', $curso->getKey())
            ->where('colegio_id', $colegio->getKey())
            ->whereNull('grado_academico_id')
            ->count());
    }

    public function test_rechaza_fecha_final_anterior_a_fecha_inicial(): void
    {
        Livewire::test(CrearAsignacionCurso::class)
            ->fillForm([
                'curso_id' => Curso::factory()->create()->getKey(),
                'colegio_id' => Colegio::factory()->create()->getKey(),
                'grado_academico_id' => null,
                'activo' => true,
                'inicia_en' => '2026-08-20 08:00:00',
                'finaliza_en' => '2026-08-19 08:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['finaliza_en' => 'after_or_equal']);

        $this->assertDatabaseCount('asignaciones_cursos', 0);
    }

    public function test_edita_asignacion_valida(): void
    {
        $asignacion = AsignacionCurso::factory()->create([
            'activo' => true,
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);

        Livewire::test(EditarAsignacionCurso::class, ['record' => $asignacion->getRouteKey()])
            ->fillForm([
                'activo' => false,
                'inicia_en' => '2026-08-20 08:00:00',
                'finaliza_en' => '2026-08-21 08:00:00',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($asignacion->refresh()->activo);
        $this->assertNotNull($asignacion->inicia_en);
        $this->assertNotNull($asignacion->finaliza_en);
    }
}
