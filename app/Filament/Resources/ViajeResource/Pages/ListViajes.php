<?php

namespace App\Filament\Resources\ViajeResource\Pages;

use App\Filament\Resources\ViajeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListViajes extends ListRecords
{
    protected static string $resource = ViajeResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Crear nuevo viaje')->icon('heroicon-o-plus')->size('lg')->modalHeading('Crear nuevo viaje')->modalWidth('7xl')];
    }
}
