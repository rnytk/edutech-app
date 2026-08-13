<?php

namespace Tests\Feature\Dominio;

use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\ProgresoModuloUsuario;
use App\Services\ServicioCalificacion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServicioCalificacionTest extends PruebaDominio
{
    public function test_califica_los_cinco_tipos_registra_reintentos_y_finaliza_el_modulo(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $datos = $this->crearActividades();
        $modulo = $this->crearModuloPublicado($nivel, ['actividades' => $datos['actividades']]);
        $servicio = app(ServicioCalificacion::class);

        $falsoIncorrecto = $servicio->calificar($usuario, $modulo, $datos['falso'], false);
        $falsoCorrecto = $servicio->calificar($usuario, $modulo, $datos['falso'], true);
        $multipleIncorrecta = $servicio->calificar($usuario, $modulo, $datos['multiple'], $datos['opcion_incorrecta']);
        $multipleCorrecta = $servicio->calificar($usuario, $modulo, $datos['multiple'], $datos['opcion_correcta']);
        $directa = $servicio->calificar($usuario, $modulo, $datos['directa'], '  Mi reflexión financiera  ');
        $ordenIncorrecto = $servicio->calificar($usuario, $modulo, $datos['orden'], [
            $datos['elemento_segundo'],
            $datos['elemento_primero'],
        ]);
        $ordenCorrecto = $servicio->calificar($usuario, $modulo, $datos['orden'], [
            $datos['elemento_primero'],
            $datos['elemento_segundo'],
        ]);
        $clasificacionIncorrecta = $servicio->calificar($usuario, $modulo, $datos['clasificacion'], [
            $datos['categoria_primera'] => [$datos['elemento_categoria_segunda']],
            $datos['categoria_segunda'] => [$datos['elemento_categoria_primera']],
        ]);
        $clasificacionCorrecta = $servicio->calificar($usuario, $modulo, $datos['clasificacion'], [
            $datos['categoria_primera'] => [$datos['elemento_categoria_primera']],
            $datos['categoria_segunda'] => [$datos['elemento_categoria_segunda']],
        ]);

        $this->assertFalse($falsoIncorrecto->correcta);
        $this->assertFalse($falsoIncorrecto->actividadCompletada);
        $this->assertSame(1, $falsoIncorrecto->numeroIntento);
        $this->assertTrue($falsoCorrecto->correcta);
        $this->assertSame(2, $falsoCorrecto->numeroIntento);
        $this->assertFalse($multipleIncorrecta->correcta);
        $this->assertTrue($multipleCorrecta->correcta);
        $this->assertNull($directa->correcta);
        $this->assertTrue($directa->actividadCompletada);
        $this->assertFalse($ordenIncorrecto->correcta);
        $this->assertTrue($ordenCorrecto->correcta);
        $this->assertFalse($clasificacionIncorrecta->correcta);
        $this->assertTrue($clasificacionCorrecta->correcta);
        $this->assertTrue($clasificacionCorrecta->moduloCompletado);
        $this->assertDatabaseCount('intentos_actividades', 9);
        $this->assertDatabaseCount('progreso_modulos_usuario', 1);
        $this->assertSame(
            [1, 2],
            IntentoActividad::query()
                ->where('actividad_uuid', $datos['falso'])
                ->orderBy('numero_intento')
                ->pluck('numero_intento')
                ->all(),
        );
        $this->assertDatabaseHas('intentos_actividades', [
            'actividad_uuid' => $datos['directa'],
            'correcta' => null,
        ]);
    }

    public function test_resultado_serializable_no_expone_respuestas_correctas_ni_configuracion(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $actividadUuid = (string) Str::uuid();
        $modulo = $this->crearModuloPublicado($nivel, [
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta segura',
                'respuesta_correcta' => true,
            ]],
        ]);

        $resultado = app(ServicioCalificacion::class)->calificar($usuario, $modulo, $actividadUuid, true);
        $serializado = json_encode($resultado, JSON_THROW_ON_ERROR);

        $this->assertSame([
            'actividad_uuid',
            'correcta',
            'actividad_completada',
            'numero_intento',
            'modulo_completado',
        ], array_keys($resultado->toArray()));
        $this->assertStringNotContainsString('respuesta_correcta', $serializado);
        $this->assertStringNotContainsString('opcion_correcta_uuid', $serializado);
        $this->assertStringNotContainsString('posicion', $serializado);
        $this->assertStringNotContainsString('actividades', $serializado);
    }

    public function test_relee_el_jsonb_actual_en_servidor_en_lugar_de_confiar_en_el_modelo_recibido(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $actividadUuid = (string) Str::uuid();
        $modulo = $this->crearModuloPublicado($nivel, [
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta',
                'respuesta_correcta' => true,
            ]],
        ]);
        Modulo::query()->findOrFail($modulo->getKey())->update([
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta',
                'respuesta_correcta' => false,
            ]],
        ]);

        $resultado = app(ServicioCalificacion::class)->calificar($usuario, $modulo, $actividadUuid, true);

        $this->assertFalse($resultado->correcta);
        $this->assertFalse($resultado->moduloCompletado);
        $this->assertDatabaseHas('intentos_actividades', [
            'actividad_uuid' => $actividadUuid,
            'correcta' => false,
        ]);
    }

    public function test_rechaza_modulo_bloqueado_sin_registrar_intento(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $primerModulo = $this->crearModuloPublicado($nivel, ['orden' => 1]);
        $actividadUuid = (string) Str::uuid();
        $segundoModulo = $this->crearModuloPublicado($nivel, [
            'orden' => 2,
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta bloqueada',
                'respuesta_correcta' => true,
            ]],
        ]);

        try {
            app(ServicioCalificacion::class)->calificar($usuario, $segundoModulo, $actividadUuid, true);
            $this->fail('Se esperaba una excepción de autorización.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('intentos_actividades', 0);
            $this->assertDatabaseCount('progreso_modulos_usuario', 0);
            $this->assertFalse($primerModulo->progresosUsuarios()->exists());
        }
    }

    public function test_respuesta_mal_formada_o_actividad_inexistente_no_genera_historial(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $actividadUuid = (string) Str::uuid();
        $modulo = $this->crearModuloPublicado($nivel, [
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta',
                'respuesta_correcta' => true,
            ]],
        ]);
        $servicio = app(ServicioCalificacion::class);

        try {
            $servicio->calificar($usuario, $modulo, $actividadUuid, ['respuesta_correcta' => true]);
            $this->fail('Se esperaba una excepción de validación.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('intentos_actividades', 0);
        }

        try {
            $servicio->calificar($usuario, $modulo, (string) Str::uuid(), true);
            $this->fail('Se esperaba una excepción de validación.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('intentos_actividades', 0);
        }
    }

    public function test_permite_reintentos_ilimitados_sin_sobrescribir_historial(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $actividadUuid = (string) Str::uuid();
        $modulo = $this->crearModuloPublicado($nivel, [
            'actividades' => [[
                'uuid' => $actividadUuid,
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Pregunta',
                'respuesta_correcta' => true,
            ]],
        ]);
        $servicio = app(ServicioCalificacion::class);

        foreach (range(1, 5) as $numeroIntento) {
            $resultado = $servicio->calificar($usuario, $modulo, $actividadUuid, false);
            $this->assertSame($numeroIntento, $resultado->numeroIntento);
        }

        $this->assertSame(5, IntentoActividad::query()->where('actividad_uuid', $actividadUuid)->count());
        $this->assertSame([1, 2, 3, 4, 5], IntentoActividad::query()
            ->where('actividad_uuid', $actividadUuid)
            ->orderBy('numero_intento')
            ->pluck('numero_intento')
            ->all());
        $this->assertSame(0, ProgresoModuloUsuario::query()->count());
    }

    /** @return array<string, mixed> */
    private function crearActividades(): array
    {
        $falso = (string) Str::uuid();
        $multiple = (string) Str::uuid();
        $directa = (string) Str::uuid();
        $orden = (string) Str::uuid();
        $clasificacion = (string) Str::uuid();
        $opcionIncorrecta = (string) Str::uuid();
        $opcionCorrecta = (string) Str::uuid();
        $elementoPrimero = (string) Str::uuid();
        $elementoSegundo = (string) Str::uuid();
        $categoriaPrimera = (string) Str::uuid();
        $categoriaSegunda = (string) Str::uuid();
        $elementoCategoriaPrimera = (string) Str::uuid();
        $elementoCategoriaSegunda = (string) Str::uuid();

        return [
            'falso' => $falso,
            'multiple' => $multiple,
            'directa' => $directa,
            'orden' => $orden,
            'clasificacion' => $clasificacion,
            'opcion_incorrecta' => $opcionIncorrecta,
            'opcion_correcta' => $opcionCorrecta,
            'elemento_primero' => $elementoPrimero,
            'elemento_segundo' => $elementoSegundo,
            'categoria_primera' => $categoriaPrimera,
            'categoria_segunda' => $categoriaSegunda,
            'elemento_categoria_primera' => $elementoCategoriaPrimera,
            'elemento_categoria_segunda' => $elementoCategoriaSegunda,
            'actividades' => [
                [
                    'uuid' => $falso,
                    'tipo' => 'falso_verdadero',
                    'pregunta' => '¿El ahorro es importante?',
                    'respuesta_correcta' => true,
                ],
                [
                    'uuid' => $multiple,
                    'tipo' => 'opcion_multiple',
                    'pregunta' => 'Selecciona la opción correcta',
                    'opciones' => [
                        ['uuid' => $opcionIncorrecta, 'texto' => 'Opción incorrecta'],
                        ['uuid' => $opcionCorrecta, 'texto' => 'Opción correcta'],
                    ],
                    'opcion_correcta_uuid' => $opcionCorrecta,
                ],
                [
                    'uuid' => $directa,
                    'tipo' => 'respuesta_directa',
                    'pregunta' => '¿Qué aprendiste?',
                ],
                [
                    'uuid' => $orden,
                    'tipo' => 'ordenacion',
                    'instruccion' => 'Ordena los pasos',
                    'elementos' => [
                        ['uuid' => $elementoSegundo, 'texto' => 'Segundo', 'posicion' => 2],
                        ['uuid' => $elementoPrimero, 'texto' => 'Primero', 'posicion' => 1],
                    ],
                ],
                [
                    'uuid' => $clasificacion,
                    'tipo' => 'clasificacion',
                    'instruccion' => 'Clasifica los elementos',
                    'categorias' => [
                        [
                            'uuid' => $categoriaPrimera,
                            'nombre' => 'Ingresos',
                            'elementos' => [[
                                'uuid' => $elementoCategoriaPrimera,
                                'texto' => 'Salario',
                            ]],
                        ],
                        [
                            'uuid' => $categoriaSegunda,
                            'nombre' => 'Gastos',
                            'elementos' => [[
                                'uuid' => $elementoCategoriaSegunda,
                                'texto' => 'Compra',
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }
}
