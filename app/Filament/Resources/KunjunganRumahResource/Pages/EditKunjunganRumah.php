<?php

namespace App\Filament\Resources\KunjunganRumahResource\Pages;

use App\Filament\Resources\KunjunganRumahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKunjunganRumah extends EditRecord
{
    protected static string $resource = KunjunganRumahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
