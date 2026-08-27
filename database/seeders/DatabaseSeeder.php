<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $department = Department::firstOrCreate([
            'group' => 'SYSTEM',
            'center' => 'SYSTEM',
            'office' => 'ADMINISTRATION',
        ]);

        $user = User::updateOrCreate(
            ['hris_number' => '000000'],
            [
                'role' => 'superadmin',
                'email' => 'superadmin@dap.edu.ph',
                'password' => Hash::make('password'),
            ],
        );

        $user->employee()->updateOrCreate(
            ['hris_number' => $user->hris_number],
            [
                'last_name' => 'Test',
                'first_name' => 'Super Admin',
                'department_id' => $department->id,
                'appointment_status' => 'pbp',
            ],
        );
    }
}
