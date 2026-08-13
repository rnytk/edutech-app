<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grados_academicos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre');
            $tabla->string('codigo')->unique('grados_academicos_codigo_unico');
            $tabla->integer('orden')->default(0);
            $tabla->boolean('activo')->default(true);
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['activo', 'orden'], 'grados_academicos_activo_orden_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grados_academicos');
    }
};
