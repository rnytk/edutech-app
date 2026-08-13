<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('titulo');
            $tabla->text('descripcion')->nullable();
            $tabla->string('ruta_imagen')->nullable();
            $tabla->integer('orden')->default(0);
            $tabla->boolean('publicado')->default(false);
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['publicado', 'orden'], 'cursos_publicado_orden_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
