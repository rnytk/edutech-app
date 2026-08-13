<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Modulos\Pages\CrearModulo;
use App\Filament\Resources\Modulos\Pages\EditarModulo;
use App\Filament\Resources\Modulos\RelationManagers\CapsulasRelationManager;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Nivel;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ModuloResourceTest extends PruebaFilament
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->autenticarSuperadministrador();
    }

    public function test_builder_crea_los_cinco_tipos_con_contrato_jsonb_en_espanol(): void
    {
        $nivel = Nivel::factory()->create(['curso_id' => Curso::factory()]);
        $uuidTarjeta = (string) Str::uuid();
        $uuidFalsoVerdadero = (string) Str::uuid();
        $uuidOpcionMultiple = (string) Str::uuid();
        $uuidOpcionA = (string) Str::uuid();
        $uuidOpcionB = (string) Str::uuid();
        $uuidRespuestaDirecta = (string) Str::uuid();
        $uuidOrdenacion = (string) Str::uuid();
        $uuidElementoA = (string) Str::uuid();
        $uuidElementoB = (string) Str::uuid();
        $uuidClasificacion = (string) Str::uuid();
        $uuidCategoriaA = (string) Str::uuid();
        $uuidCategoriaB = (string) Str::uuid();

        Livewire::test(CrearModulo::class)
            ->fillForm([
                'nivel_id' => $nivel->getKey(),
                'titulo' => 'Módulo de prueba',
                'descripcion' => '<p>Descripción.</p>',
                'orden' => 1,
                'publicado' => true,
                'bloques_contenido' => [[
                    'type' => 'tarjeta',
                    'data' => [
                        'uuid' => $uuidTarjeta,
                        'titulo' => 'Concepto de ahorro',
                        'contenido' => '<p>Ahorrar es reservar recursos.</p>',
                        'ruta_imagen' => null,
                    ],
                ]],
                'actividades' => [
                    [
                        'type' => 'falso_verdadero',
                        'data' => [
                            'uuid' => $uuidFalsoVerdadero,
                            'pregunta' => '¿Ahorrar ayuda a cumplir metas?',
                            'respuesta_correcta' => true,
                        ],
                    ],
                    [
                        'type' => 'opcion_multiple',
                        'data' => [
                            'uuid' => $uuidOpcionMultiple,
                            'pregunta' => '¿Qué acción representa ahorro?',
                            'opciones' => [
                                ['uuid' => $uuidOpcionA, 'texto' => 'Reservar una parte del ingreso'],
                                ['uuid' => $uuidOpcionB, 'texto' => 'Gastar todo de inmediato'],
                            ],
                            'opcion_correcta_uuid' => $uuidOpcionA,
                        ],
                    ],
                    [
                        'type' => 'respuesta_directa',
                        'data' => ['uuid' => $uuidRespuestaDirecta, 'pregunta' => 'Escribe una meta de ahorro.'],
                    ],
                    [
                        'type' => 'ordenacion',
                        'data' => [
                            'uuid' => $uuidOrdenacion,
                            'instruccion' => 'Ordena los pasos.',
                            'elementos' => [
                                ['uuid' => $uuidElementoA, 'texto' => 'Definir la meta', 'posicion' => 1],
                                ['uuid' => $uuidElementoB, 'texto' => 'Ahorrar periódicamente', 'posicion' => 2],
                            ],
                        ],
                    ],
                    [
                        'type' => 'clasificacion',
                        'data' => [
                            'uuid' => $uuidClasificacion,
                            'instruccion' => 'Clasifica cada concepto.',
                            'categorias' => [
                                [
                                    'uuid' => $uuidCategoriaA,
                                    'nombre' => 'Necesidad',
                                    'elementos' => [['uuid' => (string) Str::uuid(), 'texto' => 'Alimentación']],
                                ],
                                [
                                    'uuid' => $uuidCategoriaB,
                                    'nombre' => 'Deseo',
                                    'elementos' => [['uuid' => (string) Str::uuid(), 'texto' => 'Videojuego']],
                                ],
                            ],
                        ],
                    ],
                ],
                'mensaje_cierre' => '<p>¡Excelente trabajo!</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $modulo = Modulo::query()->where('titulo', 'Módulo de prueba')->firstOrFail();
        $this->assertSame('tarjeta', $modulo->bloques_contenido[0]['tipo']);
        $this->assertArrayNotHasKey('type', $modulo->bloques_contenido[0]);
        $this->assertSame([
            'falso_verdadero',
            'opcion_multiple',
            'respuesta_directa',
            'ordenacion',
            'clasificacion',
        ], array_column($modulo->actividades, 'tipo'));
        $this->assertSame($uuidOpcionA, $modulo->actividades[1]['opcion_correcta_uuid']);
    }

    public function test_editar_modulo_conserva_uuid_estables(): void
    {
        $uuidTarjeta = (string) Str::uuid();
        $uuidActividad = (string) Str::uuid();
        $modulo = Modulo::factory()->create([
            'bloques_contenido' => [[
                'tipo' => 'tarjeta',
                'uuid' => $uuidTarjeta,
                'titulo' => 'Tarjeta',
                'contenido' => '<p>Contenido.</p>',
                'ruta_imagen' => null,
            ]],
            'actividades' => [[
                'tipo' => 'falso_verdadero',
                'uuid' => $uuidActividad,
                'pregunta' => '¿Pregunta?',
                'respuesta_correcta' => true,
            ]],
        ]);

        Livewire::test(EditarModulo::class, ['record' => $modulo->getRouteKey()])
            ->fillForm(['titulo' => 'Módulo actualizado'])
            ->call('save')
            ->assertHasNoFormErrors();

        $modulo->refresh();
        $this->assertSame($uuidTarjeta, $modulo->bloques_contenido[0]['uuid']);
        $this->assertSame($uuidActividad, $modulo->actividades[0]['uuid']);
    }

    public function test_relation_manager_crea_capsula_sin_permitir_borrado_historico(): void
    {
        $modulo = Modulo::factory()->create();

        Livewire::test(CapsulasRelationManager::class, [
            'ownerRecord' => $modulo,
            'pageClass' => EditarModulo::class,
        ])
            ->callTableAction('create', null, [
                'titulo' => '¿Sabías que?',
                'contenido' => '<p>El ahorro genera hábitos positivos.</p>',
                'orden' => 1,
                'activo' => true,
            ])
            ->assertHasNoFormErrors();

        $capsula = $modulo->capsulas()->where('titulo', '¿Sabías que?')->firstOrFail();

        Livewire::test(CapsulasRelationManager::class, [
            'ownerRecord' => $modulo,
            'pageClass' => EditarModulo::class,
        ])
            ->callTableAction('edit', $capsula, [
                'titulo' => 'Consejo actualizado',
                'contenido' => '<p>El ahorro genera hábitos positivos.</p>',
                'orden' => 2,
                'activo' => false,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('capsulas', [
            'modulo_id' => $modulo->getKey(),
            'titulo' => 'Consejo actualizado',
            'activo' => false,
        ]);
    }
}
