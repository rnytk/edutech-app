<?php

namespace Tests\Feature\Portal;

use App\Livewire\Portal\VisorModulo;
use App\Models\Capsula;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use App\Services\ServicioCalificacion;
use App\Services\ServicioDesbloqueo;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Dominio\PruebaDominio;

class VisorModuloTest extends PruebaDominio
{
    public function test_ruta_exige_autenticacion_y_permite_abrir_un_modulo_disponible(): void
    {
        [$estudiante, $modulo] = $this->crearEscenario();

        $this->get(route('modulos.ver', $modulo))->assertRedirect('/login');

        $this->actingAs($estudiante)
            ->get(route('modulos.ver', $modulo))
            ->assertSuccessful()
            ->assertSeeText($modulo->titulo)
            ->assertSee('data-seccion="contenido"', escape: false);
    }

    public function test_rechaza_modulo_bloqueado_no_asignado_y_no_publicado_por_url_manual(): void
    {
        [$estudiante, $primerModulo, $nivel] = $this->crearEscenario(incluirActividades: false);
        $bloqueado = $this->crearModuloPublicado($nivel, ['orden' => 2]);
        $noPublicado = Modulo::factory()->create([
            'nivel_id' => $nivel,
            'orden' => 3,
            'publicado' => false,
        ]);

        $otroCurso = $this->crearCursoPublicado();
        $otroNivel = $this->crearNivelPublicado($otroCurso);
        $noAsignado = $this->crearModuloPublicado($otroNivel);

        $this->actingAs($estudiante)->get(route('modulos.ver', $bloqueado))->assertForbidden();
        $this->actingAs($estudiante)->get(route('modulos.ver', $noAsignado))->assertForbidden();
        $this->actingAs($estudiante)->get(route('modulos.ver', $noPublicado))->assertForbidden();
        $this->assertFalse($primerModulo->progresosUsuarios()->exists());
    }

    public function test_muestra_bloques_sanitizados_y_solo_capsulas_activas_en_orden(): void
    {
        [$estudiante, $modulo] = $this->crearEscenario(incluirActividades: false);
        $modulo->update([
            'bloques_contenido' => [[
                'tipo' => 'tarjeta',
                'uuid' => (string) Str::uuid(),
                'titulo' => 'El ahorro inteligente',
                'contenido' => '<p>Contenido seguro</p><script>alert("xss")</script><img src="x" onerror="alert(1)">',
                'ruta_imagen' => null,
            ]],
        ]);
        Capsula::factory()->create([
            'modulo_id' => $modulo,
            'titulo' => 'Cápsula segunda',
            'contenido' => '<p>Dato visible dos</p>',
            'orden' => 2,
            'activo' => true,
        ]);
        Capsula::factory()->create([
            'modulo_id' => $modulo,
            'titulo' => 'Cápsula primera',
            'contenido' => '<p>Dato visible uno</p>',
            'orden' => 1,
            'activo' => true,
        ]);
        Capsula::factory()->create([
            'modulo_id' => $modulo,
            'titulo' => 'Cápsula privada',
            'contenido' => 'No debe verse',
            'activo' => false,
        ]);

        $respuesta = $this->actingAs($estudiante)->get(route('modulos.ver', $modulo));

        $respuesta
            ->assertSuccessful()
            ->assertSeeText('El ahorro inteligente')
            ->assertSeeText('Contenido seguro')
            ->assertSeeTextInOrder(['Cápsula primera', 'Cápsula segunda'])
            ->assertDontSeeText('Cápsula privada')
            ->assertDontSee('alert("xss")', escape: false)
            ->assertDontSee('onerror="alert(1)"', escape: false);
    }

    public function test_presentacion_y_estado_livewire_no_exponen_respuestas_correctas(): void
    {
        [$estudiante, $modulo] = $this->crearEscenario();

        $prueba = Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->call('irAActividades')
            ->assertSuccessful();

        $html = $prueba->html();
        $estado = json_encode($prueba->snapshot, JSON_THROW_ON_ERROR);

        foreach (['respuesta_correcta', 'opcion_correcta_uuid', 'posicion'] as $secreto) {
            $this->assertStringNotContainsString($secreto, $html);
            $this->assertStringNotContainsString($secreto, $estado);
        }

        foreach (['actividades', 'bloques_contenido'] as $propiedadPrivada) {
            $this->assertStringNotContainsString('"'.$propiedadPrivada.'":', $estado);
        }
    }

    public function test_falso_verdadero_registra_error_acierto_y_reintento(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();

        Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->call('irAActividades')
            ->set('respuestaFalsoVerdadero', 'falso')
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', false)
            ->assertSet('actividadSuperada', false)
            ->set('respuestaFalsoVerdadero', 'verdadero')
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', true)
            ->assertSet('actividadSuperada', true);

        $this->assertSame([1, 2], IntentoActividad::query()
            ->where('actividad_uuid', $datos['falso'])
            ->orderBy('numero_intento')
            ->pluck('numero_intento')
            ->all());
    }

    public function test_opcion_multiple_rechaza_uuid_invalido_y_permite_reintento(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        app(ServicioCalificacion::class)->calificar($estudiante, $modulo, $datos['falso'], true);

        $prueba = Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('indiceActividad', 1)
            ->set('respuestaOpcion', (string) Str::uuid())
            ->call('enviarRespuesta')
            ->assertHasErrors('respuesta');

        $this->assertSame(0, IntentoActividad::query()->where('actividad_uuid', $datos['multiple'])->count());

        $prueba
            ->set('respuestaOpcion', $datos['opcion_incorrecta'])
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', false)
            ->set('respuestaOpcion', $datos['opcion_correcta'])
            ->call('enviarRespuesta')
            ->assertSet('actividadSuperada', true);

        $this->assertSame(2, IntentoActividad::query()->where('actividad_uuid', $datos['multiple'])->count());
    }

    public function test_respuesta_directa_valida_se_registra_y_cuenta_como_realizada(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        $this->completarPrevias($estudiante, $modulo, $datos, 2);

        Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('indiceActividad', 2)
            ->set('respuestaDirecta', 'Aprendí a planificar mis gastos.')
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', null)
            ->assertSet('actividadSuperada', true);

        $this->assertDatabaseHas('intentos_actividades', [
            'actividad_uuid' => $datos['directa'],
            'correcta' => null,
        ]);
    }

    public function test_ordenacion_valida_secuencia_y_manipulacion_de_uuid(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        $this->completarPrevias($estudiante, $modulo, $datos, 3);
        $prueba = Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('indiceActividad', 3)
            ->set('respuestaOrdenacion', [$datos['elemento_segundo'], $datos['elemento_primero']])
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', false)
            ->set('respuestaOrdenacion', [(string) Str::uuid(), $datos['elemento_primero']])
            ->call('enviarRespuesta')
            ->assertHasErrors('respuesta');

        $this->assertSame(1, IntentoActividad::query()->where('actividad_uuid', $datos['orden'])->count());

        $prueba
            ->set('respuestaOrdenacion', [$datos['elemento_primero'], $datos['elemento_segundo']])
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', true);
    }

    public function test_clasificacion_valida_resultado_y_rechaza_manipulacion(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        $this->completarPrevias($estudiante, $modulo, $datos, 4);
        $prueba = Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('indiceActividad', 4)
            ->set('asignacionesClasificacion', [
                $datos['elemento_categoria_primera'] => $datos['categoria_segunda'],
                $datos['elemento_categoria_segunda'] => $datos['categoria_primera'],
            ])
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', false)
            ->set('asignacionesClasificacion', [
                $datos['elemento_categoria_primera'] => (string) Str::uuid(),
                $datos['elemento_categoria_segunda'] => $datos['categoria_segunda'],
            ])
            ->call('enviarRespuesta')
            ->assertHasErrors('respuesta');

        $this->assertSame(1, IntentoActividad::query()->where('actividad_uuid', $datos['clasificacion'])->count());

        $prueba
            ->set('asignacionesClasificacion', [
                $datos['elemento_categoria_primera'] => $datos['categoria_primera'],
                $datos['elemento_categoria_segunda'] => $datos['categoria_segunda'],
            ])
            ->call('enviarRespuesta')
            ->assertSet('resultadoCorrecto', true)
            ->assertSet('moduloCompletado', true);
    }

    public function test_reanuda_en_primera_actividad_pendiente_desde_intentos_persistidos(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        $this->completarPrevias($estudiante, $modulo, $datos, 2);

        Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('seccion', 'actividades')
            ->assertSet('indiceActividad', 2)
            ->assertSeeText('¿Qué aprendiste?');
    }

    public function test_finalizacion_es_idempotente_y_desbloquea_siguiente_modulo_y_nivel(): void
    {
        [$estudiante, $modulo, $nivel, $datos] = $this->crearEscenario();
        $siguienteModulo = $this->crearModuloPublicado($nivel, ['orden' => 2]);
        $curso = $nivel->curso;
        $siguienteNivel = $this->crearNivelPublicado($curso, ['orden' => 2]);
        $moduloSiguienteNivel = $this->crearModuloPublicado($siguienteNivel, ['orden' => 1]);
        $this->completarPrevias($estudiante, $modulo, $datos, 5);

        $prueba = Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('seccion', 'cierre')
            ->call('confirmarFinalizacion')
            ->call('confirmarFinalizacion')
            ->assertSet('moduloCompletado', true);

        $this->assertSame(1, ProgresoModuloUsuario::query()
            ->where('usuario_id', $estudiante->getKey())
            ->where('modulo_id', $modulo->getKey())
            ->count());
        $this->assertTrue(app(ServicioDesbloqueo::class)->moduloEstaDesbloqueado($estudiante, $siguienteModulo));
        $this->assertFalse(app(ServicioDesbloqueo::class)->moduloEstaDesbloqueado($estudiante, $moduloSiguienteNivel));

        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $estudiante,
            'modulo_id' => $siguienteModulo,
        ]);

        $this->assertTrue(app(ServicioDesbloqueo::class)->nivelEstaDesbloqueado($estudiante, $siguienteNivel));
        $this->assertTrue(app(ServicioDesbloqueo::class)->moduloEstaDesbloqueado($estudiante, $moduloSiguienteNivel));
    }

    public function test_ultimo_modulo_muestra_curso_completado_sin_generar_diploma(): void
    {
        [$estudiante, $modulo, , $datos] = $this->crearEscenario();
        $this->completarPrevias($estudiante, $modulo, $datos, 5);

        Livewire::actingAs($estudiante)
            ->test(VisorModulo::class, ['modulo' => $modulo])
            ->assertSet('seccion', 'cierre')
            ->assertSet('moduloCompletado', true)
            ->assertSet('cursoCompletado', true)
            ->assertSeeText('¡Completaste todo el curso!')
            ->assertDontSee('Descargar diploma');
    }

    public function test_niveles_solo_enlaza_modulos_disponibles_o_completados(): void
    {
        [$estudiante, $primerModulo, $nivel] = $this->crearEscenario(incluirActividades: false);
        $segundoModulo = $this->crearModuloPublicado($nivel, ['orden' => 2]);

        $respuesta = $this->actingAs($estudiante)->get(route('cursos.niveles', $nivel->curso));

        $respuesta
            ->assertSuccessful()
            ->assertSee(route('modulos.ver', $primerModulo), escape: false)
            ->assertDontSee(route('modulos.ver', $segundoModulo), escape: false);

        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $estudiante,
            'modulo_id' => $primerModulo,
        ]);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $nivel->curso))
            ->assertSee(route('modulos.ver', $primerModulo), escape: false)
            ->assertSee(route('modulos.ver', $segundoModulo), escape: false);
    }

    /**
     * @return array{0: Usuario, 1: Modulo, 2: Nivel, 3: array<string, mixed>}
     */
    private function crearEscenario(bool $incluirActividades = true): array
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);
        $nivel = $this->crearNivelPublicado($curso, ['orden' => 1]);
        $datos = $this->crearActividades();
        $modulo = $this->crearModuloPublicado($nivel, [
            'titulo' => 'Mi primer módulo financiero',
            'orden' => 1,
            'actividades' => $incluirActividades ? $datos['actividades'] : [],
            'mensaje_cierre' => '<p>Terminaste este gran reto.</p>',
        ]);

        return [$estudiante, $modulo, $nivel, $datos];
    }

    /** @param array<string, mixed> $datos */
    private function completarPrevias($estudiante, Modulo $modulo, array $datos, int $cantidad): void
    {
        $respuestas = [
            [$datos['falso'], true],
            [$datos['multiple'], $datos['opcion_correcta']],
            [$datos['directa'], 'Mi respuesta personal'],
            [$datos['orden'], [$datos['elemento_primero'], $datos['elemento_segundo']]],
            [$datos['clasificacion'], [
                $datos['categoria_primera'] => [$datos['elemento_categoria_primera']],
                $datos['categoria_segunda'] => [$datos['elemento_categoria_segunda']],
            ]],
        ];

        foreach (array_slice($respuestas, 0, $cantidad) as [$uuid, $respuesta]) {
            app(ServicioCalificacion::class)->calificar($estudiante, $modulo, $uuid, $respuesta);
        }
    }

    /** @return array<string, mixed> */
    private function crearActividades(): array
    {
        $datos = [
            'falso' => (string) Str::uuid(),
            'multiple' => (string) Str::uuid(),
            'directa' => (string) Str::uuid(),
            'orden' => (string) Str::uuid(),
            'clasificacion' => (string) Str::uuid(),
            'opcion_incorrecta' => (string) Str::uuid(),
            'opcion_correcta' => (string) Str::uuid(),
            'elemento_primero' => (string) Str::uuid(),
            'elemento_segundo' => (string) Str::uuid(),
            'categoria_primera' => (string) Str::uuid(),
            'categoria_segunda' => (string) Str::uuid(),
            'elemento_categoria_primera' => (string) Str::uuid(),
            'elemento_categoria_segunda' => (string) Str::uuid(),
        ];

        $datos['actividades'] = [
            [
                'uuid' => $datos['falso'],
                'tipo' => 'falso_verdadero',
                'pregunta' => '¿El ahorro ayuda a cumplir metas?',
                'respuesta_correcta' => true,
            ],
            [
                'uuid' => $datos['multiple'],
                'tipo' => 'opcion_multiple',
                'pregunta' => 'Selecciona una opción',
                'opciones' => [
                    ['uuid' => $datos['opcion_incorrecta'], 'texto' => 'Gastar sin planificar'],
                    ['uuid' => $datos['opcion_correcta'], 'texto' => 'Crear un presupuesto'],
                ],
                'opcion_correcta_uuid' => $datos['opcion_correcta'],
            ],
            [
                'uuid' => $datos['directa'],
                'tipo' => 'respuesta_directa',
                'pregunta' => '¿Qué aprendiste?',
            ],
            [
                'uuid' => $datos['orden'],
                'tipo' => 'ordenacion',
                'instruccion' => 'Ordena los pasos',
                'elementos' => [
                    ['uuid' => $datos['elemento_segundo'], 'texto' => 'Ahorrar', 'posicion' => 2],
                    ['uuid' => $datos['elemento_primero'], 'texto' => 'Planificar', 'posicion' => 1],
                ],
            ],
            [
                'uuid' => $datos['clasificacion'],
                'tipo' => 'clasificacion',
                'instruccion' => 'Clasifica cada elemento',
                'categorias' => [
                    [
                        'uuid' => $datos['categoria_primera'],
                        'nombre' => 'Ingresos',
                        'elementos' => [[
                            'uuid' => $datos['elemento_categoria_primera'],
                            'texto' => 'Salario',
                        ]],
                    ],
                    [
                        'uuid' => $datos['categoria_segunda'],
                        'nombre' => 'Gastos',
                        'elementos' => [[
                            'uuid' => $datos['elemento_categoria_segunda'],
                            'texto' => 'Compra',
                        ]],
                    ],
                ],
            ],
        ];

        return $datos;
    }
}
