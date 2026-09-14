<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\ScheduleType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class EmployeeAttendanceSampleSeeder extends Seeder
{
    private const EMPLOYEE_COUNT = 30;

    private const ENTRY_COUNT = 40;

    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $departmentIds = DB::table('departments')
                ->where('group', '!=', 'SYSTEM')
                ->orderBy('id')
                ->limit(10)
                ->pluck('id')
                ->all();

            if (count($departmentIds) < 10) {
                throw new RuntimeException('Seed the base sample departments before employee attendance samples.');
            }

            $password = Hash::make('password');
            $attendanceDates = $this->attendanceDates();

            foreach (range(1, self::EMPLOYEE_COUNT) as $employeeIndex) {
                $hrisNumber = sprintf('8%05d', $employeeIndex);
                $departmentId = $departmentIds[($employeeIndex - 1) % count($departmentIds)];
                $scheduleType = $employeeIndex % 2 === 0
                    ? ScheduleType::FIXED
                    : ScheduleType::FULLFLEXI;

                DB::table('users')->updateOrInsert(
                    ['hris_number' => $hrisNumber],
                    [
                        'email' => sprintf('workforce%02d@dap.test', $employeeIndex),
                        'role' => Role::EMPLOYEE->value,
                        'password' => $password,
                        'remember_token' => null,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );

                $userId = DB::table('users')->where('hris_number', $hrisNumber)->value('id');

                DB::table('employees')->updateOrInsert(
                    ['hris_number' => $hrisNumber],
                    [
                        'last_name' => $this->lastName($employeeIndex),
                        'first_name' => $this->firstName($employeeIndex),
                        'middle_name' => 'Workforce',
                        'department_id' => $departmentId,
                        'appointment_status' => $employeeIndex % 2 === 0 ? 'npp' : 'pbp',
                        'employment_status' => $employeeIndex % 5 !== 0,
                        'signature_path' => null,
                        'identity_photo_path' => null,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );

                DB::table('official_times')->updateOrInsert(
                    ['hris_number' => $hrisNumber],
                    [
                        'time_in' => $scheduleType === ScheduleType::FIXED ? '08:00:00' : null,
                        'schedule_type' => $scheduleType->value,
                        'effectivity_date' => '2026-01-01',
                        'status' => 'approved',
                        'created_by' => $hrisNumber,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );

                foreach ($attendanceDates as $entryIndex => $date) {
                    $timeStart = $this->timeStart($date, $employeeIndex, $entryIndex, $scheduleType);
                    $timeEnd = $this->timeEnd($timeStart, $employeeIndex, $entryIndex);

                    DB::table('time_entries')->updateOrInsert(
                        ['hris_number' => $hrisNumber, 'time_start' => $timeStart],
                        [
                            'time_end' => $timeEnd,
                            'department_id' => $departmentId,
                            'schedule_type' => $scheduleType->value,
                            'official_time' => $scheduleType === ScheduleType::FIXED ? '08:00:00' : null,
                            'timekeeper_id' => $userId,
                            'tag' => ($entryIndex + 1) % 10 === 0 ? 'wfh' : 'ros',
                            'updated_at' => $now,
                            'created_at' => $now,
                        ],
                    );
                }
            }
        });
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function attendanceDates(): array
    {
        $dates = [];
        $date = CarbonImmutable::create(2026, 3, 2)->startOfDay();

        while (count($dates) < self::ENTRY_COUNT) {
            if (! $date->isWeekend()) {
                $dates[] = $date;
            }

            $date = $date->addDay();
        }

        return $dates;
    }

    private function timeStart(
        CarbonImmutable $date,
        int $employeeIndex,
        int $entryIndex,
        ScheduleType $scheduleType,
    ): CarbonImmutable {
        $baseHour = $scheduleType === ScheduleType::FIXED ? 8 : 7;
        $baseMinute = $scheduleType === ScheduleType::FIXED ? 0 : 30;
        $minuteVariation = (($employeeIndex * 7) + ($entryIndex * 11)) % 91;

        return $date->setTime($baseHour, $baseMinute)->addMinutes($minuteVariation);
    }

    private function timeEnd(CarbonImmutable $timeStart, int $employeeIndex, int $entryIndex): CarbonImmutable
    {
        $undertimeMinutes = ($employeeIndex + $entryIndex) % 9 === 0
            ? 30
            : 0;

        return $timeStart->addHours(9)->subMinutes($undertimeMinutes);
    }

    private function firstName(int $index): string
    {
        $names = ['Adrian', 'Beatriz', 'Cedric', 'Denise', 'Emmanuel', 'Frances', 'Gino', 'Hannah', 'Isabel', 'Jerome'];

        return $names[($index - 1) % count($names)].' '.(int) ceil($index / count($names));
    }

    private function lastName(int $index): string
    {
        $names = ['Abad', 'Bautista', 'Chua', 'Domingo', 'Evangelista', 'Fernandez', 'Gonzales', 'Herrera', 'Ignacio', 'Jimenez'];

        return $names[($index - 1) % count($names)];
    }
}
