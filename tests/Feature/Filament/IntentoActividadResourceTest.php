<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\IntentosActividades\IntentoActividadResource;
use App\Filament\Resources\IntentosActividades\Pages\ListarIntentosActividades;
use App\Filament\Resources\IntentosActividades\Pages\VerIntentoActividad;
use App\Models\IntentoActividad;
use Livewire\Livewire;

class IntentoActividadResourceTest extends PruebaFilament
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->autenticarSuperadministrador();
    }

    public function test_recurso_lista_filtra_y_muestra_intentos_sin_acciones_de_mutacion(): void
    {
        $intento = IntentoActividad::factory()->create();

        Livewire::test(ListarIntentosActividades::class)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('colegio_id', $intento->usuario->colegio_id)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('usuario_id', $intento->usuario_id)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('curso_id', $intento->modulo->nivel->curso_id)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('nivel_id', $intento->modulo->nivel_id)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('modulo_id', $intento->modulo_id)
            ->assertCanSeeTableRecords([$intento])
            ->filterTable('tipo_actividad', $intento->tipo_actividad)
            ->assertCanSeeTableRecords([$intento]);

        Livewire::test(VerIntentoActividad::class, ['record' => $intento->getRouteKey()])
            ->assertSee($intento->actividad_uuid)
            ->assertSuccessful();

        $this->assertFalse(IntentoActividadResource::canCreate());
        $this->assertFalse(IntentoActividadResource::canEdit($intento));
        $this->assertFalse(IntentoActividadResource::canDelete($intento));
        $this->assertFalse(IntentoActividadResource::canDeleteAny());
    }
}
