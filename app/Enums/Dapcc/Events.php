<?php

namespace App\Enums\Dapcc;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Events: string implements HasLabel, HasColor
{
    case SHIFT = 'dapcc_shift';
    case DAYOFF = 'dapcc_dayoff';
    case ALA = 'ala';
    case OB = 'ob';
    case CDO = 'cdo';
    case SUS = 'dapcc_suspended';
    case HOL = 'dapcc_holiday';
    case FLAG = 'dapcc_flag';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SHIFT => 'Shift',
            self::DAYOFF => 'Day-off',
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
            self::SHIFT => 'info',
            self::DAYOFF => 'warning',
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
            self::SHIFT => '#2E3192',
            self::DAYOFF => 'orange',
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
