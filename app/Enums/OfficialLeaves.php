<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OfficialLeaves: string implements HasLabel
{
    case VL = 'vl';
    case MFL = 'mfl';
    case SL = 'sl';
    case WL = 'wl';
    case ML = 'ml';
    case PL = 'pl';
    case SPL = 'spl';
    case SOLOPARENT = 'soloparentl';
    case STUDYL = 'studyl';
    case RP = 'rp';
    case SLBW = 'slbw';
    case SEL = 'sel';
    case AL = 'al';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VL => 'Vacation Leave',
            self::MFL => 'Mandatory/Forced Leave',
            self::SL => 'Sick Leave',
            self::WL => 'Wellness Leave',
            self::ML => 'Maternity Leave',
            self::PL => 'Paternity Leave',
            self::SPL => 'Special Privilege Leave',
            self::SOLOPARENT => 'Solo Parent Leave',
            self::STUDYL => 'Study Leave',
            self::RP => 'Rehabilitation Privilege',
            self::SLBW => 'Special Leave Benefits for Women',
            self::SEL => 'Special Emergency (Calamity) Leave',
            self::AL => 'Adoption Leave',
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
