<?php

namespace App\Filament\Resources\OfficialTimeResource\Pages;

use App\Filament\Resources\OfficialTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficialTime extends EditRecord
{
    protected static string $resource = OfficialTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
