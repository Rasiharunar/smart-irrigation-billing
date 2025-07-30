<?php

namespace App\Filament\Resources\UsageSessionResource\Pages;

use App\Filament\Resources\UsageSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsageSession extends EditRecord
{
    protected static string $resource = UsageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}