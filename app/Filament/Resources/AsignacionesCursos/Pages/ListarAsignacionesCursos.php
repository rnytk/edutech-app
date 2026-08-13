<?php

namespace App\Filament\Resources\AsignacionesCursos\Pages;

use App\Filament\Resources\AsignacionesCursos\AsignacionCursoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListarAsignacionesCursos extends ListRecords
{
    protected static string $resource = AsignacionCursoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Crear asignación')];
    }
}
