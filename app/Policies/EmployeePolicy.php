<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        if ($user->role === Role::SUPERADMIN) {
            return true;
        }

        if ($user->hris_number === $employee->hris_number) {
            return true;
        }

        $userDepartment = $user->employee?->department;
        $employeeDepartment = $employee->department;

        if (! $userDepartment || ! $employeeDepartment) {
            return false;
        }

        return match ($user->role) {
            Role::HRADMIN => ($userDepartment->center === 'DAPCC') === ($employeeDepartment->center === 'DAPCC'),
            Role::ADMINCOORD => $userDepartment->is($employeeDepartment),
            Role::CENTERADMINCOORD => $userDepartment->center === $employeeDepartment->center,
            Role::GROUPADMINCOORD => $userDepartment->group === $employeeDepartment->group,
            default => false,
        };
    }

    public function viewDtr(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    public function viewQrCode(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    public function viewAnyQrCode(User $user): bool
    {
        return $user->role === Role::SUPERADMIN;
    }
}
