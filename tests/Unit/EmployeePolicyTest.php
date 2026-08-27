<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Policies\EmployeePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmployeePolicyTest extends TestCase
{
    #[DataProvider('roleScopeProvider')]
    public function test_employee_access_follows_the_existing_organizational_scope(Role $role, array $target, bool $allowed): void
    {
        $userDepartment = $this->department(1, 'GROUP-A', 'CENTER-A', 'OFFICE-A');
        $targetDepartment = $this->department(2, $target['group'], $target['center'], $target['office']);
        $userEmployee = $this->employee('100001', $userDepartment);
        $targetEmployee = $this->employee('100002', $targetDepartment);
        $user = new User(['hris_number' => '100001', 'role' => $role->value]);
        $user->setRelation('employee', $userEmployee);

        $this->assertSame($allowed, (new EmployeePolicy)->view($user, $targetEmployee));
    }

    public static function roleScopeProvider(): array
    {
        return [
            'employee cannot view another employee' => [Role::EMPLOYEE, ['group' => 'GROUP-A', 'center' => 'CENTER-A', 'office' => 'OFFICE-A'], false],
            'admin coordinator is restricted to their office' => [Role::ADMINCOORD, ['group' => 'GROUP-A', 'center' => 'CENTER-A', 'office' => 'OFFICE-B'], false],
            'center coordinator can view their center' => [Role::CENTERADMINCOORD, ['group' => 'GROUP-A', 'center' => 'CENTER-A', 'office' => 'OFFICE-B'], true],
            'group coordinator cannot cross groups' => [Role::GROUPADMINCOORD, ['group' => 'GROUP-B', 'center' => 'CENTER-A', 'office' => 'OFFICE-A'], false],
            'pasig HR cannot view DAPCC' => [Role::HRADMIN, ['group' => 'GROUP-A', 'center' => 'DAPCC', 'office' => 'OFFICE-A'], false],
        ];
    }

    private function department(int $id, string $group, string $center, string $office): Department
    {
        $department = new Department(compact('group', 'center', 'office'));
        $department->id = $id;

        return $department;
    }

    private function employee(string $hrisNumber, Department $department): Employee
    {
        $employee = new Employee(['hris_number' => $hrisNumber]);
        $employee->setRelation('department', $department);

        return $employee;
    }
}
