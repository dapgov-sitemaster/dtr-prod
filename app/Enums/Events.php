<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Events: string implements HasLabel, HasColor
{
    case WFH = 'wfh';
    case HWFH = 'hwfh';
    case ALA = 'ala';
    case CDO = 'cdo';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WFH => 'Work from Home',
            self::HWFH => 'Hybrid Work from Home',
            self::ALA => 'Official Leave',
            self::CDO => 'Compensatory Day-off',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WFH => 'info',
            self::HWFH => 'info',
            self::ALA => 'success',
            self::CDO => 'success',
        };
    }

    public function getColorT(): string|array|null
    {
        return match ($this) {
            self::WFH => '#2E3192',
            self::HWFH => '#2E3192',
            self::ALA => 'orange',
            self::CDO => 'green',
        };
    }
}
