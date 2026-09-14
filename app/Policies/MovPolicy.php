<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Event;
use App\Models\Mov;
use App\Models\User;

class MovPolicy
{
    public function view(User $user, Mov $mov): bool
    {
        if ($user->role === Role::SUPERADMIN) {
            return true;
        }

        $movable = $mov->movable;

        if (! $movable instanceof Event || ! $movable->employee) {
            return false;
        }

        return $user->can('view', $movable->employee);
    }
}
