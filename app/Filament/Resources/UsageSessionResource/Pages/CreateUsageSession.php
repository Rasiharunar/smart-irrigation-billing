<?php

namespace App\Filament\Resources\UsageSessionResource\Pages;

use App\Filament\Resources\UsageSessionResource;
use App\Models\Tariff;
use Filament\Resources\Pages\CreateRecord;

class CreateUsageSession extends CreateRecord
{
    protected static string $resource = UsageSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tariff_rate'] = Tariff::getCurrentRate();
        $data['started_at'] = now();
        
        return $data;
    }
}