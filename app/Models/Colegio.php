<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Colegio extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'colegios';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'colegio_id');
    }

    public function asignacionesCursos(): HasMany
    {
        return $this->hasMany(AsignacionCurso::class, 'colegio_id');
    }
}
