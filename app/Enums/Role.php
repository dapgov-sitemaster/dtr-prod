<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum Role: string implements HasLabel, HasColor
{
    case EMPLOYEE = 'employee';
    case ADMINCOORD = 'admincoord';
    case CENTERADMINCOORD = 'centeradmincoord';
    case HRADMIN = 'hradmin';
    case TIMEKEEPER = 'timekeeper';
    case JOBBER = 'jobber';
    case SUPERADMIN = 'superadmin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EMPLOYEE => 'Employee',
            self::ADMINCOORD => 'Admin Coordinator',
            self::CENTERADMINCOORD => 'Center Admin Coordinator',
            self::HRADMIN => 'HR Administrator',
            self::TIMEKEEPER => 'Timekeeper',
            self::JOBBER => 'Jobber',
            self::SUPERADMIN => 'SuperAdmin',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EMPLOYEE => 'gray',
            self::ADMINCOORD => 'success',
            self::CENTERADMINCOORD => 'success',
            self::HRADMIN => 'primary',
            self::TIMEKEEPER => 'warning',
            self::JOBBER => 'gray',
            self::SUPERADMIN => 'danger',
        };
    }
}
