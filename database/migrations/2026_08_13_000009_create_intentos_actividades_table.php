<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos_actividades', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $tabla->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();
            $tabla->uuid('actividad_uuid');
            $tabla->string('tipo_actividad', 32);
            $tabla->unsignedInteger('numero_intento');
            $tabla->jsonb('respuesta');
            $tabla->boolean('correcta')->nullable();
            $tabla->timestampTz('respondido_en');
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['usuario_id', 'modulo_id', 'actividad_uuid'], 'intentos_usuario_modulo_actividad_indice');
            $tabla->index(['modulo_id', 'tipo_actividad'], 'intentos_modulo_tipo_indice');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE intentos_actividades
            ADD CONSTRAINT intentos_actividades_numero_positivo
            CHECK (numero_intento > 0)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE intentos_actividades
            ADD CONSTRAINT intentos_actividades_tipo_valido
            CHECK (tipo_actividad IN ('falso_verdadero', 'opcion_multiple', 'respuesta_directa', 'ordenacion', 'clasificacion'))
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_actividades');
    }
};
