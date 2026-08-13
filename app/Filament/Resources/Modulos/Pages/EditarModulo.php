<?php

namespace App\Filament\Resources\Modulos\Pages;

use App\Filament\Resources\Modulos\ModuloResource;
use App\Filament\Soporte\TransformadorContenidoModulo;
use Filament\Resources\Pages\EditRecord;

class EditarModulo extends EditRecord
{
    protected static string $resource = ModuloResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $transformador = app(TransformadorContenidoModulo::class);
        $data['bloques_contenido'] = $transformador->paraFormulario($data['bloques_contenido'] ?? []);
        $data['actividades'] = $transformador->paraFormulario($data['actividades'] ?? []);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $transformador = app(TransformadorContenidoModulo::class);
        $data['bloques_contenido'] = $transformador->paraPersistenciaBloques($data['bloques_contenido'] ?? []);
        $data['actividades'] = $transformador->paraPersistenciaActividades($data['actividades'] ?? []);

        return $data;
    }
}
