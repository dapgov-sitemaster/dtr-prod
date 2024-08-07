<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Events: string implements HasLabel, HasColor
{
    case WFH = 'wfh';
    case HWFH = 'hwfh';
    case ALA = 'ala';
    case OB = 'ob';
    case CDO = 'cdo';
    case SUS = 'suspended';
    case HOL = 'holiday';
    case FLAG = 'flag';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WFH => 'Work from Home',
            self::HWFH => 'Hybrid Work from Home',
            self::ALA => 'Official Leave',
            self::OB => 'Official Business',
            self::CDO => 'Compensatory Day-off',
            self::SUS => 'Work Suspension',
            self::HOL => 'Holiday',
            self::FLAG => 'Flag Ceremony',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WFH => 'info',
            self::HWFH => 'info',
            self::ALA => 'warning',
            self::OB => 'gray',
            self::CDO => 'warning',
            self::SUS => 'success',
            self::HOL => 'success',
            self::FLAG => 'success',
        };
    }

    public function getColorT(): string|array|null
    {
        return match ($this) {
            self::WFH => '#2E3192',
            self::HWFH => '#2E3192',
            self::ALA => 'orange',
            self::OB => 'darkgray',
            self::CDO => 'orange',
            self::SUS => 'green',
            self::HOL => 'green',
            self::FLAG => 'green',
        };
    }

    public static function parse(Events | string | null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }
}
