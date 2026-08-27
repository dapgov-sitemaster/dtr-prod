<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewDtr(User $user, Department $department): bool
    {
        if ($user->role === Role::SUPERADMIN) {
            return true;
        }

        $userDepartment = $user->employee?->department;

        if (! $userDepartment) {
            return false;
        }

        return match ($user->role) {
            Role::HRADMIN => ($userDepartment->center === 'DAPCC') === ($department->center === 'DAPCC'),
            Role::ADMINCOORD => $userDepartment->is($department),
            Role::CENTERADMINCOORD => $userDepartment->center === $department->center,
            Role::GROUPADMINCOORD => $userDepartment->group === $department->group,
            default => false,
        };
    }
}
