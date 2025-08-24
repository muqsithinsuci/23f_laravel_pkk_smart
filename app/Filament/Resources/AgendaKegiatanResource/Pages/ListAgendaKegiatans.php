<?php

namespace App\Filament\Resources\AgendaKegiatanResource\Pages;

use App\Filament\Resources\AgendaKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgendaKegiatans extends ListRecords
{
    protected static string $resource = AgendaKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
