<?php

namespace App\Filament\Resources\OfficialTimeResource\Pages;

use App\Filament\Resources\OfficialTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficialTimes extends ListRecords
{
    protected static string $resource = OfficialTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
