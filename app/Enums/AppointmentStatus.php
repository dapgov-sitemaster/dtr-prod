<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasLabel, HasColor
{
    case PBP = 'pbp';
    case NPP = 'npp';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PBP => 'Plantilla based Position',
            self::NPP => 'Non-plantilla Position',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PBP => 'info',
            self::NPP => 'gray',
        };
    }
}
