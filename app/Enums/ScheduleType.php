<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum ScheduleType: string implements HasLabel, HasColor
{
    case FULLFLEXI = 'full_flexitime';
    case FIXED = 'fixed_officialtime';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FULLFLEXI => 'Full Flexitime',
            self::FIXED => 'Fixed Official Time',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FULLFLEXI => 'success',
            self::FIXED => 'info',
        };
    }

    public function getAbbr(): string
    {
        return match ($this) {
            self::FULLFLEXI => 'FFT',
            self::FIXED => 'FOT',
        };
    }
}
