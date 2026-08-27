<?php

namespace App\Filament\Resources\MovResource\Pages;

use App\Filament\Resources\MovResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovs extends ListRecords
{
    protected static string $resource = MovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
