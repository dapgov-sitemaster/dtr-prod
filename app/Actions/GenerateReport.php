<?php

namespace App\Actions;

use App\Models\Report;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateReport
{
    public function handle($employee, $date_from, $date_to): Collection
    {
        // $employee = Employee::where('hris_number', $hris_number)->first();
        $official_time = ($employee->official_time) ? $employee->official_time->time_in->format('H:i:s') : '08:00:00';
        $dates = CarbonPeriod::create($date_from, $date_to)->toArray();
        array_pop($dates);
        $checkDtr = Report::query()
            ->where('hris_number', $employee->hris_number)
            ->whereBetween('time_start', [$date_from, $date_to])
            // ->whereDate('time_start', '>=', $date_from)->whereDate('time_start', '<=', $date_to)
            ->get();

        if ($checkDtr->isNotEmpty()) {
            return $checkDtr;
        } else {
            $time_entries = TimeEntry::query()
                ->where('hris_number', $employee->hris_number)
                ->whereBetween(DB::raw('DATE(time_start)'), [$date_from, $date_to])
                // ->whereDate('time_start', '>', $date_from)->whereDate('time_start', '<', $date_to)
                ->get();

            if ($time_entries) {
                foreach ($dates as $date) {
                    $day_entry = clone $time_entries->whereBetween('time_start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
                    if ($day_entry->isNotEmpty()) {
                        $time_start = null;
                        $time_end = null;

                        if ($day_entry->count() == 1) {
                            $time_start = $day_entry[0]->time_start;
                            $time_end = $day_entry[0]->time_end;
                        } else {
                            $time_start = $day_entry->first()->time_start;
                            $time_end = ($day_entry->last()->time_end) ? $day_entry->last()->time_end : $day_entry->last()->time_start;
                        }

                        Report::create([
                            'hris_number' => $employee->hris_number,
                            'time_start' => $time_start,
                            'time_end' => $time_end,
                            'official_time' => $official_time,
                            'office' => $employee->department->description,
                            'appointment_status' => $employee->appointment_status,
                            'time_entry_type' => $day_entry->first()->tag,
                        ]);
                    }
                }
            }

            return Report::query()
                ->where('hris_number', $employee->hris_number)
                ->whereBetween('time_start', [$date_from, $date_to])
                ->get();
        }
    }
}
