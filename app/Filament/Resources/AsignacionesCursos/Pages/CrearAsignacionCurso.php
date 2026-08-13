<?php

namespace App\Filament\Resources\AsignacionesCursos\Pages;

use App\Filament\Resources\AsignacionesCursos\AsignacionCursoResource;
use Filament\Resources\Pages\CreateRecord;

class CrearAsignacionCurso extends CreateRecord
{
    protected static string $resource = AsignacionCursoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        AsignacionCursoResource::validarUnicidad($data);

        return $data;
    }
}
