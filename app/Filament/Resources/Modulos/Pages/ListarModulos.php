<?php

namespace App\Filament\Resources\Modulos\Pages;

use App\Filament\Resources\Modulos\ModuloResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListarModulos extends ListRecords
{
    protected static string $resource = ModuloResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Crear módulo')];
    }
}
