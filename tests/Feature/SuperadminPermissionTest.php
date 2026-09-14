<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Department;
use App\Models\Permission;
use App\Models\User;
use App\Providers\PermissionsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SuperadminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_is_allowed_by_dynamic_permission_gates(): void
    {
        Permission::create([
            'name' => 'View all HR admin modules',
            'slug' => 'hr-admin-view-any',
        ]);

        (new PermissionsServiceProvider(app()))->boot();

        $superadmin = new User(['role' => Role::SUPERADMIN]);

        $this->assertTrue(Gate::forUser($superadmin)->allows('hr-admin-view-any'));
    }

    public function test_regular_user_still_needs_the_permission(): void
    {
        Permission::create([
            'name' => 'View all HR admin modules',
            'slug' => 'hr-admin-view-any',
        ]);

        (new PermissionsServiceProvider(app()))->boot();

        $employee = new User(['role' => Role::EMPLOYEE]);

        $this->assertFalse(Gate::forUser($employee)->allows('hr-admin-view-any'));
    }

    public function test_superadmin_bypasses_named_gates_and_route_permissions(): void
    {
        Gate::define('normally-denied', fn () => false);
        Route::middleware([
            'web',
            'auth',
            'role:hradmin',
            'checkcenter:DAPCC',
            'permission:missing-permission',
            'haswfhsched',
        ])
            ->get('/test/superadmin-only-bypass', fn () => 'allowed');

        $superadmin = User::create([
            'hris_number' => '000001',
            'email' => 'superadmin@example.test',
            'role' => Role::SUPERADMIN,
            'password' => 'password',
        ]);

        $this->assertTrue(Gate::forUser($superadmin)->allows('normally-denied'));
        $this->actingAs($superadmin)
            ->get('/test/superadmin-only-bypass')
            ->assertOk()
            ->assertSeeText('allowed');
    }

    public function test_center_scope_gates_keep_their_data_filtering_semantics(): void
    {
        $superadmin = $this->createSuperadminWithEmployee('000002');

        $this->assertTrue(Gate::forUser($superadmin)->allows('view-pasig'));
        $this->assertFalse(Gate::forUser($superadmin)->allows('view-dapcc'));
    }

    public function test_superadmin_sees_both_center_navigation_menus(): void
    {
        $superadmin = $this->createSuperadminWithEmployee('000003');
        $this->actingAs($superadmin);

        $sidebar = Blade::render('<x-sidebar.index />');

        $this->assertStringContainsString('DAPCC HR Administrator Panel', $sidebar);
        $this->assertStringContainsString('DAPCC Admin Coordinator Panel', $sidebar);
        $this->assertStringContainsString('HR Administrator Panel', $sidebar);
        $this->assertStringContainsString('Admin Coordinator Panel', $sidebar);
        $this->assertStringContainsString('Work from Home', $sidebar);
    }

    private function createSuperadminWithEmployee(string $hrisNumber): User
    {
        $department = Department::create([
            'group' => 'SYSTEM',
            'center' => 'SYSTEM',
            'office' => 'ADMINISTRATION',
        ]);
        $superadmin = User::create([
            'hris_number' => $hrisNumber,
            'email' => "superadmin-{$hrisNumber}@example.test",
            'role' => Role::SUPERADMIN,
            'password' => 'password',
        ]);
        $superadmin->employee()->create([
            'hris_number' => $superadmin->hris_number,
            'last_name' => 'Admin',
            'first_name' => 'Super',
            'department_id' => $department->id,
            'appointment_status' => 'pbp',
        ]);

        return $superadmin->load('employee.department');
    }
}
