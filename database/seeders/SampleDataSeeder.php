<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\ScheduleType;
use App\Models\Employee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    private const SAMPLE_COUNT = 10;

    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $departments = $this->seedDepartments($now);
            $users = $this->seedUsers($now);
            $employees = $this->seedEmployees($departments, $now);
            $permissions = $this->seedPermissions($now);

            $this->seedUserPermissions($users, $permissions);
            $events = $this->seedEvents($now);
            $timeEntries = $this->seedTimeEntries($departments, $users, $now);
            $this->seedLocations($timeEntries, $now);
            $this->seedOfficialTimes($now);
            $this->seedReports($departments, $now);
            $this->seedShiftingSchedules($now);
            $this->seedMovs($events, $now);
            $this->seedActivityLog($employees, $users, $now);
            $this->call(EmployeeAttendanceSampleSeeder::class);
        });
    }

    private function seedDepartments($now): array
    {
        $departments = [
            ['group' => 'OFFICE OF THE PRESIDENT', 'center' => 'OP', 'office' => 'Office of the President'],
            ['group' => 'CORPORATE SERVICES', 'center' => 'ADMIN', 'office' => 'Human Resource Management Division'],
            ['group' => 'CORPORATE SERVICES', 'center' => 'ADMIN', 'office' => 'Information and Communications Technology Division'],
            ['group' => 'FINANCE', 'center' => 'FINANCE', 'office' => 'Accounting Division'],
            ['group' => 'OPERATIONS', 'center' => 'OPERATIONS', 'office' => 'Project Management Office'],
            ['group' => 'DAPCC OPERATIONS', 'center' => 'DAPCC', 'office' => 'Office of the Resident Director'],
            ['group' => 'DAPCC OPERATIONS', 'center' => 'DAPCC', 'office' => 'Conference Services Division'],
            ['group' => 'DAPCC OPERATIONS', 'center' => 'DAPCC', 'office' => 'Facilities Management Division'],
            ['group' => 'DAPCC OPERATIONS', 'center' => 'DAPCC', 'office' => 'Food and Beverage Division'],
            ['group' => 'DAPCC OPERATIONS', 'center' => 'DAPCC', 'office' => 'Security and Safety Division'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                $department,
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        return array_map(
            fn (array $department) => DB::table('departments')->where($department)->value('id'),
            $departments,
        );
    }

    private function seedUsers($now): array
    {
        $roles = [
            Role::EMPLOYEE,
            Role::ADMINCOORD,
            Role::CENTERADMINCOORD,
            Role::GROUPADMINCOORD,
            Role::HRADMIN,
            Role::TIMEKEEPER,
            Role::ADMINCOORD,
            Role::CENTERADMINCOORD,
            Role::GROUPADMINCOORD,
            Role::HRADMIN,
        ];
        $password = Hash::make('password');
        $userIds = [];

        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $hrisNumber = $this->hrisNumber($index);
            DB::table('users')->updateOrInsert(
                ['hris_number' => $hrisNumber],
                [
                    'email' => "sample{$index}@dap.test",
                    'role' => $roles[$index - 1]->value,
                    'password' => $password,
                    'remember_token' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $userIds[] = DB::table('users')->where('hris_number', $hrisNumber)->value('id');
        }

        return $userIds;
    }

    private function seedEmployees(array $departments, $now): array
    {
        $firstNames = ['Alex', 'Bianca', 'Carlo', 'Diana', 'Ethan', 'Faith', 'Gabriel', 'Hazel', 'Ivan', 'Julia'];
        $lastNames = ['Reyes', 'Santos', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Ramos', 'Castro', 'Aquino'];
        $employeeIds = [];

        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $hrisNumber = $this->hrisNumber($index);
            DB::table('employees')->updateOrInsert(
                ['hris_number' => $hrisNumber],
                [
                    'last_name' => $lastNames[$index - 1],
                    'first_name' => $firstNames[$index - 1],
                    'middle_name' => 'Sample',
                    'department_id' => $departments[$index - 1],
                    'appointment_status' => $index % 2 === 0 ? 'npp' : 'pbp',
                    'employment_status' => true,
                    'signature_path' => null,
                    'identity_photo_path' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $employeeIds[] = DB::table('employees')->where('hris_number', $hrisNumber)->value('id');
        }

        return $employeeIds;
    }

    private function seedPermissions($now): array
    {
        $permissions = [
            ['name' => 'View HR RSP', 'slug' => 'hr-admin-rsp-view'],
            ['name' => 'View All HR Modules', 'slug' => 'hr-admin-view-any'],
            ['name' => 'View Employees', 'slug' => 'sample-employees-view'],
            ['name' => 'Create Employees', 'slug' => 'sample-employees-create'],
            ['name' => 'Update Employees', 'slug' => 'sample-employees-update'],
            ['name' => 'View Events', 'slug' => 'sample-events-view'],
            ['name' => 'Manage Events', 'slug' => 'sample-events-manage'],
            ['name' => 'View DTR', 'slug' => 'sample-dtr-view'],
            ['name' => 'Generate Reports', 'slug' => 'sample-reports-generate'],
            ['name' => 'Manage Settings', 'slug' => 'sample-settings-manage'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'updated_at' => $now, 'created_at' => $now],
            );
        }

        return array_map(
            fn (array $permission) => DB::table('permissions')->where('slug', $permission['slug'])->value('id'),
            $permissions,
        );
    }

    private function seedUserPermissions(array $users, array $permissions): void
    {
        foreach (range(0, self::SAMPLE_COUNT - 1) as $index) {
            DB::table('users_permissions')->updateOrInsert([
                'user_id' => $users[$index],
                'permission_id' => $permissions[$index],
            ]);
        }
    }

    private function seedEvents($now): array
    {
        $tags = ['wfh', 'ala', 'ob', 'cdo', 'wfh', 'ala', 'ob', 'cdo', 'wfh', 'ob'];
        $statuses = ['approved', 'pending', 'approved', 'disapproved'];
        $eventIds = [];

        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $eventStart = $now->copy()->setDate(2026, 1, 5)->startOfDay()->addDays($index - 1)->addHours(8);
            $description = $tags[$index - 1] === 'ala' ? 'vl' : "Sample event {$index}";
            DB::table('events')->updateOrInsert(
                ['hris_number' => $this->hrisNumber($index), 'start' => $eventStart],
                [
                    'end' => $eventStart->copy()->addHours(9),
                    'tag' => $tags[$index - 1],
                    'description' => $description,
                    'status' => $statuses[($index - 1) % count($statuses)],
                    'created_by' => $this->hrisNumber($index),
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $eventIds[] = DB::table('events')
                ->where('hris_number', $this->hrisNumber($index))
                ->where('start', $eventStart)
                ->value('id');
        }

        return $eventIds;
    }

    private function seedTimeEntries(array $departments, array $users, $now): array
    {
        $timeEntryIds = [];

        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $timeStart = $now->copy()->setDate(2026, 1, 5)->startOfDay()->addDays($index - 1)->addHours(8);
            DB::table('time_entries')->updateOrInsert(
                ['hris_number' => $this->hrisNumber($index), 'time_start' => $timeStart],
                [
                    'time_end' => $timeStart->copy()->addHours(9),
                    'department_id' => $departments[$index - 1],
                    'schedule_type' => $index % 2 === 0 ? ScheduleType::FIXED->value : ScheduleType::FULLFLEXI->value,
                    'official_time' => $index % 2 === 0 ? '08:00:00' : null,
                    'timekeeper_id' => $users[5],
                    'tag' => $index % 3 === 0 ? 'wfh' : 'ros',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $timeEntryIds[] = DB::table('time_entries')
                ->where('hris_number', $this->hrisNumber($index))
                ->where('time_start', $timeStart)
                ->value('id');
        }

        return $timeEntryIds;
    }

    private function seedLocations(array $timeEntries, $now): void
    {
        foreach ($timeEntries as $index => $timeEntryId) {
            DB::table('locations')->updateOrInsert(
                ['time_entry_id' => $timeEntryId],
                [
                    'location' => 'Development Academy of the Philippines sample location '.($index + 1),
                    'coordinates' => json_encode([
                        'latitude' => 14.5764 + ($index / 1000),
                        'longitude' => 121.0851 + ($index / 1000),
                    ]),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOfficialTimes($now): void
    {
        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            DB::table('official_times')->updateOrInsert(
                ['hris_number' => $this->hrisNumber($index)],
                [
                    'time_in' => $index % 2 === 0 ? '08:00:00' : null,
                    'schedule_type' => $index % 2 === 0 ? ScheduleType::FIXED->value : ScheduleType::FULLFLEXI->value,
                    'effectivity_date' => $now->copy()->startOfMonth()->toDateString(),
                    'status' => 'approved',
                    'created_by' => $this->hrisNumber($index),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedReports(array $departments, $now): void
    {
        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $timeStart = $now->copy()->setDate(2026, 1, 5)->startOfDay()->addDays($index - 1)->addHours(8);
            $office = DB::table('departments')->where('id', $departments[$index - 1])->value('office');
            DB::table('reports')->updateOrInsert(
                ['hris_number' => $this->hrisNumber($index), 'time_start' => $timeStart],
                [
                    'break_start' => $timeStart->copy()->addHours(4),
                    'break_end' => $timeStart->copy()->addHours(5),
                    'time_end' => $timeStart->copy()->addHours(9),
                    'schedule_type' => $index % 2 === 0 ? ScheduleType::FIXED->value : ScheduleType::FULLFLEXI->value,
                    'official_time' => $index % 2 === 0 ? '08:00:00' : null,
                    'office' => $office,
                    'appointment_status' => $index % 2 === 0 ? 'npp' : 'pbp',
                    'time_entry_type' => $index % 3 === 0 ? 'wfh' : 'ros',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedShiftingSchedules($now): void
    {
        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            $description = "Sample shift {$index}";
            DB::table('shifting_schedules')->updateOrInsert(
                ['description' => $description],
                [
                    'hris_number' => $this->hrisNumber($index),
                    'start' => $now->copy()->setDate(2026, 1, 5)->startOfDay()->addDays($index - 1)->addHours(6),
                    'end' => $now->copy()->setDate(2026, 1, 5)->startOfDay()->addDays($index - 1)->addHours(14),
                    'tag' => 'dapcc_shift',
                    'created_by' => $this->hrisNumber($index),
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedMovs(array $events, $now): void
    {
        foreach ($events as $index => $eventId) {
            DB::table('movs')->updateOrInsert(
                ['movable_type' => Event::class, 'movable_id' => $eventId],
                [
                    'filename' => sprintf('samples/mov-%02d.pdf', $index + 1),
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedActivityLog(array $employees, array $users, $now): void
    {
        $table = config('activitylog.table_name', 'activity_log');

        foreach (range(1, self::SAMPLE_COUNT) as $index) {
            DB::table($table)->updateOrInsert(
                ['log_name' => 'sample', 'description' => "Sample activity {$index}"],
                [
                    'subject_type' => Employee::class,
                    'subject_id' => $employees[$index - 1],
                    'causer_type' => User::class,
                    'causer_id' => $users[$index - 1],
                    'event' => 'created',
                    'properties' => json_encode(['sample' => true, 'sequence' => $index]),
                    'batch_uuid' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function hrisNumber(int $index): string
    {
        return sprintf('9%05d', $index);
    }
}
