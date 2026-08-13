<?php

namespace Tests\Feature\Dominio;

use App\Models\AsignacionCurso;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

abstract class PruebaDominio extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'edutech_app_testing') {
            throw new RuntimeException('Las pruebas de dominio solo pueden ejecutarse en edutech_app_testing.');
        }
    }

    /** @param array<string, mixed> $atributos */
    protected function crearEstudianteHabilitado(array $atributos = []): Usuario
    {
        return Usuario::factory()->create($atributos);
    }

    /** @param array<string, mixed> $atributos */
    protected function crearCursoPublicado(array $atributos = []): Curso
    {
        return Curso::factory()->create([
            'publicado' => true,
            ...$atributos,
        ]);
    }

    /** @param array<string, mixed> $atributos */
    protected function asignarCurso(Usuario $usuario, Curso $curso, array $atributos = []): AsignacionCurso
    {
        return AsignacionCurso::factory()->create([
            'curso_id' => $curso,
            'colegio_id' => $usuario->colegio_id,
            'grado_academico_id' => null,
            ...$atributos,
        ]);
    }

    /** @param array<string, mixed> $atributos */
    protected function crearNivelPublicado(Curso $curso, array $atributos = []): Nivel
    {
        return Nivel::factory()->create([
            'curso_id' => $curso,
            'publicado' => true,
            ...$atributos,
        ]);
    }

    /** @param array<string, mixed> $atributos */
    protected function crearModuloPublicado(Nivel $nivel, array $atributos = []): Modulo
    {
        return Modulo::factory()->create([
            'nivel_id' => $nivel,
            'publicado' => true,
            'actividades' => [],
            ...$atributos,
        ]);
    }
}
