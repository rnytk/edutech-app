<?php

namespace App\Filament\Resources\Modulos\Pages;

use App\Filament\Resources\Modulos\ModuloResource;
use App\Filament\Soporte\TransformadorContenidoModulo;
use Filament\Resources\Pages\CreateRecord;

class CrearModulo extends CreateRecord
{
    protected static string $resource = ModuloResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $transformador = app(TransformadorContenidoModulo::class);
        $data['bloques_contenido'] = $transformador->paraPersistenciaBloques($data['bloques_contenido'] ?? []);
        $data['actividades'] = $transformador->paraPersistenciaActividades($data['actividades'] ?? []);

        return $data;
    }
}
