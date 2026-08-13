<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('nivel_id')->constrained('niveles')->restrictOnDelete();
            $tabla->string('titulo');
            $tabla->text('descripcion')->nullable();
            $tabla->string('ruta_imagen')->nullable();
            $tabla->integer('orden')->default(0);
            $tabla->jsonb('bloques_contenido');
            $tabla->jsonb('actividades');
            $tabla->text('mensaje_cierre')->nullable();
            $tabla->boolean('publicado')->default(false);
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['nivel_id', 'publicado', 'orden'], 'modulos_nivel_publicado_orden_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
