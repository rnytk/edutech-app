<?php

namespace App\Filament\Resources\GradosAcademicos\Pages;

use App\Filament\Resources\GradosAcademicos\GradoAcademicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListarGradosAcademicos extends ListRecords
{
    protected static string $resource = GradoAcademicoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Crear grado académico')];
    }
}
