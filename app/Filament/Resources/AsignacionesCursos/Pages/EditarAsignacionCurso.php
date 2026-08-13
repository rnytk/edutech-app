<?php

namespace App\Filament\Resources\AsignacionesCursos\Pages;

use App\Filament\Resources\AsignacionesCursos\AsignacionCursoResource;
use Filament\Resources\Pages\EditRecord;

class EditarAsignacionCurso extends EditRecord
{
    protected static string $resource = AsignacionCursoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        AsignacionCursoResource::validarUnicidad($data, $this->getRecord());

        return $data;
    }
}
