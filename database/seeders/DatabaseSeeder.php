<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $user = User::factory()->create([
        //     'hris_number' => '000000',
        //     'role' => 'superadmin',
        //     'email' => 'superadmin@dap.edu.ph',
        // ]);

        User::find(1)->first()->employee()->create([
            'hris_number' => '000000',
            'last_name' => 'Test',
            'first_name' => 'Super Admin',
            'department_id' => 61,
            'appointment_status' => 'pbp',
        ]);

        // $user->employee->create([
        //     '' => fake()->lastName('male'),
        //     '' => fake()->lastName('male'),
        //     '' => fake()->lastName('male'),
        // ]);
    }
}
