<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case EMPLOYEE = 'employee';
    case ADMINCOORD = 'admincoord';
    case CENTERADMINCOORD = 'centeradmincoord';
    case HRADMIN = 'hradmin';
    // case TIMEKEEPER = 'timekeeper';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EMPLOYEE => 'Employee',
            self::ADMINCOORD => 'Admin Coordinator',
            self::CENTERADMINCOORD => 'Center Admin Coordinator',
            self::HRADMIN => 'HR Administrator',
            // self::TIMEKEEPER => 'Timekeeper',
        };
    }

    // public function getColor(): string|array|null
    // {
    //     return match ($this) {
    //         self::PBP => 'info',
    //         self::NPP => 'gray',
    //     };
    // }
}
