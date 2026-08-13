<?php

namespace Tests\Feature\Dominio;

use App\Models\IntentoActividad;
use App\Models\ProgresoModuloUsuario;
use App\Services\ServicioProgreso;
use DomainException;
use Illuminate\Support\Str;

class ServicioProgresoTest extends PruebaDominio
{
    public function test_calcula_progreso_solo_con_modulos_y_niveles_publicados(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $nivel = $this->crearNivelPublicado($curso);
        $nivelNoPublicado = $this->crearNivelPublicado($curso, ['publicado' => false]);
        $primerModulo = $this->crearModuloPublicado($nivel, ['orden' => 1]);
        $segundoModulo = $this->crearModuloPublicado($nivel, ['orden' => 2]);
        $tercerModulo = $this->crearModuloPublicado($nivel, ['orden' => 3]);
        $moduloNoPublicado = $this->crearModuloPublicado($nivel, ['publicado' => false]);
        $moduloNivelNoPublicado = $this->crearModuloPublicado($nivelNoPublicado);
        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $primerModulo,
        ]);
        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $moduloNoPublicado,
        ]);
        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $moduloNivelNoPublicado,
        ]);
        $servicio = app(ServicioProgreso::class);

        $this->assertSame(33, $servicio->calcularPorcentajeNivel($usuario, $nivel));
        $this->assertSame(33, $servicio->calcularPorcentajeCurso($usuario, $curso));
        $this->assertFalse($servicio->nivelEstaCompletado($usuario, $nivel));
        $this->assertFalse($servicio->cursoEstaCompletado($usuario, $curso));

        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $segundoModulo,
        ]);
        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $tercerModulo,
        ]);

        $this->assertSame(100, $servicio->calcularPorcentajeNivel($usuario, $nivel));
        $this->assertSame(100, $servicio->calcularPorcentajeCurso($usuario, $curso));
        $this->assertTrue($servicio->nivelEstaCompletado($usuario, $nivel));
        $this->assertTrue($servicio->cursoEstaCompletado($usuario, $curso));
    }

    public function test_finalizacion_es_idempotente_y_se_conserva_si_cambia_el_modulo(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $modulo = $this->crearModuloPublicado($nivel);
        $servicio = app(ServicioProgreso::class);

        $primerProgreso = $servicio->finalizarModulo($usuario, $modulo);
        $segundoProgreso = $servicio->finalizarModulo($usuario, $modulo);

        $this->assertTrue($primerProgreso->is($segundoProgreso));
        $this->assertSame(1, ProgresoModuloUsuario::query()->count());

        $modulo->update([
            'actividades' => [[
                'uuid' => (string) Str::uuid(),
                'tipo' => 'falso_verdadero',
                'pregunta' => 'Nueva actividad',
                'respuesta_correcta' => true,
            ]],
        ]);

        $this->assertTrue($servicio->moduloEstaCompletado($usuario, $modulo));
        $this->assertTrue($servicio->puedeFinalizarModulo($usuario, $modulo));
        $this->assertSame(1, ProgresoModuloUsuario::query()->count());
    }

    public function test_no_finaliza_hasta_cumplir_todas_las_actividades_actuales(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $uuidCalificada = (string) Str::uuid();
        $uuidDirecta = (string) Str::uuid();
        $modulo = $this->crearModuloPublicado($nivel, [
            'actividades' => [
                [
                    'uuid' => $uuidCalificada,
                    'tipo' => 'falso_verdadero',
                    'pregunta' => 'Pregunta',
                    'respuesta_correcta' => true,
                ],
                [
                    'uuid' => $uuidDirecta,
                    'tipo' => 'respuesta_directa',
                    'pregunta' => 'Reflexiona',
                ],
            ],
        ]);
        $servicio = app(ServicioProgreso::class);

        $this->assertFalse($servicio->puedeFinalizarModulo($usuario, $modulo));

        try {
            $servicio->finalizarModulo($usuario, $modulo);
            $this->fail('Se esperaba una excepción por actividades pendientes.');
        } catch (DomainException) {
            $this->assertDatabaseCount('progreso_modulos_usuario', 0);
        }

        IntentoActividad::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
            'actividad_uuid' => $uuidCalificada,
            'tipo_actividad' => 'falso_verdadero',
            'correcta' => false,
        ]);
        IntentoActividad::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
            'actividad_uuid' => $uuidDirecta,
            'tipo_actividad' => 'respuesta_directa',
            'correcta' => null,
        ]);

        $this->assertFalse($servicio->puedeFinalizarModulo($usuario, $modulo));

        IntentoActividad::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
            'actividad_uuid' => $uuidCalificada,
            'tipo_actividad' => 'falso_verdadero',
            'correcta' => true,
        ]);

        $this->assertTrue($servicio->puedeFinalizarModulo($usuario, $modulo));
        $this->assertInstanceOf(ProgresoModuloUsuario::class, $servicio->finalizarModulo($usuario, $modulo));
    }

    public function test_un_nivel_sin_modulos_publicados_no_se_considera_completado(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $nivel = $this->crearNivelPublicado($curso);
        $servicio = app(ServicioProgreso::class);

        $this->assertSame(0, $servicio->calcularPorcentajeNivel($usuario, $nivel));
        $this->assertFalse($servicio->nivelEstaCompletado($usuario, $nivel));
    }
}
