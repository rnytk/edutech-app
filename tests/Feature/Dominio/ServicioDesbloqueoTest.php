<?php

namespace Tests\Feature\Dominio;

use App\Models\Modulo;
use App\Services\ServicioDesbloqueo;
use App\Services\ServicioProgreso;

class ServicioDesbloqueoTest extends PruebaDominio
{
    public function test_aplica_secuencia_estricta_entre_modulos_y_niveles_publicados(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $primerNivel = $this->crearNivelPublicado($curso, ['orden' => 1]);
        $segundoNivel = $this->crearNivelPublicado($curso, ['orden' => 2]);
        Modulo::factory()->create([
            'nivel_id' => $primerNivel,
            'orden' => 1,
            'publicado' => false,
            'actividades' => [],
        ]);
        $primerModulo = $this->crearModuloPublicado($primerNivel, ['orden' => 2]);
        $segundoModulo = $this->crearModuloPublicado($primerNivel, ['orden' => 3]);
        $moduloSegundoNivel = $this->crearModuloPublicado($segundoNivel, ['orden' => 1]);
        $desbloqueo = app(ServicioDesbloqueo::class);
        $progreso = app(ServicioProgreso::class);

        $this->assertTrue($desbloqueo->nivelEstaDesbloqueado($usuario, $primerNivel));
        $this->assertTrue($desbloqueo->moduloEstaDesbloqueado($usuario, $primerModulo));
        $this->assertFalse($desbloqueo->moduloEstaDesbloqueado($usuario, $segundoModulo));
        $this->assertFalse($desbloqueo->nivelEstaDesbloqueado($usuario, $segundoNivel));
        $this->assertFalse($desbloqueo->moduloEstaDesbloqueado($usuario, $moduloSegundoNivel));

        $progreso->finalizarModulo($usuario, $primerModulo);

        $this->assertTrue($desbloqueo->moduloEstaDesbloqueado($usuario, $segundoModulo));
        $this->assertFalse($desbloqueo->nivelEstaDesbloqueado($usuario, $segundoNivel));

        $progreso->finalizarModulo($usuario, $segundoModulo);

        $this->assertTrue($desbloqueo->nivelEstaDesbloqueado($usuario, $segundoNivel));
        $this->assertTrue($desbloqueo->moduloEstaDesbloqueado($usuario, $moduloSegundoNivel));
    }

    public function test_usa_el_id_como_desempate_estable_cuando_el_orden_es_igual(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($usuario, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $primerModulo = $this->crearModuloPublicado($nivel, ['orden' => 1]);
        $segundoModulo = $this->crearModuloPublicado($nivel, ['orden' => 1]);
        $desbloqueo = app(ServicioDesbloqueo::class);

        $this->assertTrue($desbloqueo->moduloEstaDesbloqueado($usuario, $primerModulo));
        $this->assertFalse($desbloqueo->moduloEstaDesbloqueado($usuario, $segundoModulo));

        app(ServicioProgreso::class)->finalizarModulo($usuario, $primerModulo);

        $this->assertTrue($desbloqueo->moduloEstaDesbloqueado($usuario, $segundoModulo));
    }

    public function test_rechaza_contenido_no_publicado_o_sin_asignacion(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $nivel = $this->crearNivelPublicado($curso);
        $modulo = $this->crearModuloPublicado($nivel);
        $desbloqueo = app(ServicioDesbloqueo::class);

        $this->assertFalse($desbloqueo->nivelEstaDesbloqueado($usuario, $nivel));
        $this->assertFalse($desbloqueo->moduloEstaDesbloqueado($usuario, $modulo));

        $this->asignarCurso($usuario, $curso);
        $modulo->update(['publicado' => false]);

        $this->assertFalse($desbloqueo->moduloEstaDesbloqueado($usuario, $modulo));
    }
}
