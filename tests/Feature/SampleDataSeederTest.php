<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SampleDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_linked_samples_for_every_domain_table(): void
    {
        $this->seed(SampleDataSeeder::class);

        foreach (['departments', 'permissions', 'users_permissions', 'events', 'locations', 'reports', 'shifting_schedules', 'movs', 'activity_log'] as $table) {
            $this->assertSame(10, DB::table($table)->count(), "{$table} should contain 10 base samples.");
        }

        $this->assertSame(40, DB::table('users')->count());
        $this->assertSame(40, DB::table('employees')->count());
        $this->assertSame(40, DB::table('official_times')->count());
        $this->assertSame(1210, DB::table('time_entries')->count());

        $workforce = DB::table('employees')->where('hris_number', 'like', '8%');
        $this->assertSame(30, (clone $workforce)->count());
        $this->assertSame(24, (clone $workforce)->where('employment_status', true)->count());
        $this->assertSame(6, (clone $workforce)->where('employment_status', false)->count());
        $this->assertSame(10, (clone $workforce)->distinct()->count('department_id'));

        $this->assertSame(
            30,
            DB::table('time_entries')
                ->select('hris_number')
                ->where('hris_number', 'like', '8%')
                ->groupBy('hris_number')
                ->havingRaw('COUNT(*) = 40')
                ->get()
                ->count(),
        );

        $this->assertSame(1200, DB::table('time_entries')->where('hris_number', 'like', '8%')->whereNotNull('time_end')->count());
    }

    public function test_it_can_be_rerun_without_duplicating_sample_data(): void
    {
        $this->seed(SampleDataSeeder::class);
        $this->seed(SampleDataSeeder::class);

        $this->assertSame(40, DB::table('users')->count());
        $this->assertSame(10, DB::table('events')->count());
        $this->assertSame(1210, DB::table('time_entries')->count());
        $this->assertSame(10, DB::table('movs')->count());
    }

    public function test_the_main_seeder_includes_samples_in_testing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['hris_number' => '000000', 'role' => 'superadmin']);
        $this->assertDatabaseHas('users', ['hris_number' => '900001', 'role' => 'employee']);
        $this->assertDatabaseHas('events', ['hris_number' => '900001']);
    }
}
