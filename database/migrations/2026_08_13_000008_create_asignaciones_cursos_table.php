<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_cursos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $tabla->foreignId('colegio_id')->constrained('colegios')->restrictOnDelete();
            $tabla->foreignId('grado_academico_id')->nullable()->constrained('grados_academicos')->restrictOnDelete();
            $tabla->boolean('activo')->default(true);
            $tabla->timestampTz('inicia_en')->nullable();
            $tabla->timestampTz('finaliza_en')->nullable();
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['colegio_id', 'grado_academico_id', 'activo'], 'asignaciones_cursos_acceso_indice');
            $tabla->index(['curso_id', 'activo'], 'asignaciones_cursos_curso_activo_indice');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE asignaciones_cursos
            ADD CONSTRAINT asignaciones_cursos_fechas_validas
            CHECK (inicia_en IS NULL OR finaliza_en IS NULL OR finaliza_en >= inicia_en)
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX asignaciones_cursos_general_unica
            ON asignaciones_cursos (curso_id, colegio_id)
            WHERE grado_academico_id IS NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX asignaciones_cursos_especifica_unica
            ON asignaciones_cursos (curso_id, colegio_id, grado_academico_id)
            WHERE grado_academico_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_cursos');
    }
};
