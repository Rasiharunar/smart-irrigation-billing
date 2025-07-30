<?php

namespace App\Filament\Resources\PumpResource\Pages;

use App\Filament\Resources\PumpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPump extends EditRecord
{
    protected static string $resource = PumpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}