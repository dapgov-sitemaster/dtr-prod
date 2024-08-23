<?php

namespace App\Filament\Resources\MovResource\Pages;

use App\Filament\Resources\MovResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMov extends EditRecord
{
    protected static string $resource = MovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
