<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'cursos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'publicado' => 'boolean',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function scopePublicados(Builder $consulta): Builder
    {
        return $consulta->where('publicado', true);
    }

    public function niveles(): HasMany
    {
        return $this->hasMany(Nivel::class, 'curso_id');
    }

    public function asignacionesCursos(): HasMany
    {
        return $this->hasMany(AsignacionCurso::class, 'curso_id');
    }
}
