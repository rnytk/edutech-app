<?php

namespace App\Filament\Resources\Niveles\Pages;

use App\Filament\Resources\Niveles\NivelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListarNiveles extends ListRecords
{
    protected static string $resource = NivelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Crear nivel')];
    }
}
