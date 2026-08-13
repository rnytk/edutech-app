<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionCurso extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'asignaciones_cursos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'inicia_en' => 'immutable_datetime',
            'finaliza_en' => 'immutable_datetime',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function scopeVigentes(Builder $consulta, ?CarbonInterface $momento = null): Builder
    {
        $momento ??= now();

        return $consulta
            ->where('activo', true)
            ->where(fn (Builder $rango): Builder => $rango
                ->whereNull('inicia_en')
                ->orWhere('inicia_en', '<=', $momento))
            ->where(fn (Builder $rango): Builder => $rango
                ->whereNull('finaliza_en')
                ->orWhere('finaliza_en', '>=', $momento));
    }

    public function scopeAplicablesA(Builder $consulta, Usuario $usuario): Builder
    {
        return $consulta
            ->where('colegio_id', $usuario->colegio_id)
            ->where(fn (Builder $grado): Builder => $grado
                ->whereNull('grado_academico_id')
                ->orWhere('grado_academico_id', $usuario->grado_academico_id));
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class, 'colegio_id');
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id');
    }
}
