<?php

namespace App\Actions;

use Carbon\Carbon;
use App\Models\Report;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use App\Enums\ScheduleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class GenerateReport
{
    public function handle($employee, $date_from, $date_to): Collection
    {

        // $official_time = ($schedule_type == ) ? $employee->official_time->time_in->format('H:i:s') : '08:00:00';
        $period = CarbonPeriod::create($date_from, $date_to)->toArray();
        $dates = [];
        array_pop($period);
        $checkDtr = Report::query()
            ->select('time_start')
            ->where('hris_number', $employee->hris_number)
            ->whereBetween('time_start', [$date_from, $date_to])
            ->orderBy('time_start')
            ->get();

        if ($checkDtr->isNotEmpty()) {
            foreach ($period as $date) {
                if ($date->format('Y-m-d') > $checkDtr->last()->time_start->format('Y-m-d')) {
                    $dates[] = $date;
                }
            }
        } else {
            $dates = $period;
        }

        if ($dates) {
            $time_entries = TimeEntry::query()
                ->where('hris_number', $employee->hris_number)
                ->whereBetween(DB::raw('DATE(time_start)'), [$dates[0]->format('Y-m-d'), $dates[count($dates) - 1]->format('Y-m-d')])
                ->get();

            if ($time_entries) {
                foreach ($dates as $date) {
                    $day_entry = clone $time_entries->whereBetween('time_start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();

                    if ($day_entry->isNotEmpty()) {

                        $time_start = null;
                        $time_end = null;
                        $break_start = null;
                        $break_end = null;


                        $schedule_type = ScheduleType::FULLFLEXI;
                        $official_time = null;
                        $schedule_type_effect_date = null;

                        if ($employee->official_time) {
                            $schedule_type_effect_date = $employee->official_time->effectivity_date;

                            if ($day_entry->first()->schedule_type != $schedule_type) {

                                if ($schedule_type_effect_date <= $date->format('Y-m-d')) {
                                    $schedule_type = $employee->official_time->schedule_type;

                                    if ($schedule_type == ScheduleType::FIXED) {
                                        $official_time = $employee->official_time->time_in->format('H:i:s');
                                    } else {
                                        $official_time = null;
                                    }
                                }
                            }
                        }



                        if ($day_entry->count() == 1) {
                            $time_start = $day_entry->first()->time_start?->startOfMinute();
                            $time_end = $day_entry->first()->time_end?->startOfMinute();

                            if ($time_end) {
                                $break_start = $time_start->copy()->addHours(4);
                                $break_end = $break_start->copy()->addHour();
                            }
                        } else if ($day_entry->count() > 1) {
                            foreach ($day_entry as $entry) {
                                $offi_break_start = Carbon::parse($entry->time_start->format('Y-m-d') . ' 11:00:00');
                                $offi_break_end = Carbon::parse($entry->time_start->format('Y-m-d') . ' 14:00:00');

                                if ($break_start == null && $entry->time_start->between($offi_break_start, $offi_break_end)) {
                                    $break_start = $entry->time_start;
                                    $break_end = $entry->time_end;
                                } else if ($break_start == null && $entry->time_end->between($offi_break_start, $offi_break_end)) {
                                    $break_start = $entry->time_end;
                                } else if ($break_start != null) {
                                    $break_end = $entry->time_start;
                                }
                            }

                            $time_start = $day_entry->first()->time_start;
                            $time_end = ($day_entry->last()->time_end) ? $day_entry->last()->time_end : $day_entry->last()->time_start;

                            if ($break_start == null && $break_end == null) {
                                $break_start = $time_start->copy()->addHours(4);
                                $break_end = $break_start->copy()->addHour();
                            }
                        }

                        Report::create([
                            'hris_number' => $employee->hris_number,
                            'time_start' => $time_start,
                            'break_start' => $break_start,
                            'break_end' => $break_end,
                            'time_end' => $time_end,
                            'schedule_type' => $schedule_type,
                            'official_time' => $official_time,
                            'office' => $employee->department->description,
                            'appointment_status' => $employee->appointment_status,
                            'time_entry_type' => $day_entry->first()->tag,
                        ]);
                    }
                }
            }
        }

        return Report::query()
            ->where('hris_number', $employee->hris_number)
            ->whereBetween('time_start', [$date_from, $date_to])
            ->get();
    }
}
