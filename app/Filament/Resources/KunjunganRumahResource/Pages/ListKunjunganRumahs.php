<?php

namespace App\Filament\Resources\KunjunganRumahResource\Pages;

use App\Filament\Resources\KunjunganRumahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKunjunganRumahs extends ListRecords
{
    protected static string $resource = KunjunganRumahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
