<?php

namespace App\Actions;

use Carbon\Carbon;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use App\Enums\Dapcc\Events;
use App\Enums\ScheduleType;
use Illuminate\Support\Facades\DB;

class ProcessDapccReport
{
    public function handle($start_date, $end_date, Employee $employee, $events)
    {
        $period = CarbonPeriod::create($start_date, $end_date)->toArray();
        array_pop($period);
        $dtrData = collect();

        $time_entries = TimeEntry::query()
            ->where('hris_number', $employee->hris_number)
            ->whereBetween(DB::raw('DATE(time_start)'), [$start_date, $end_date])
            ->get();

        if ($time_entries) {
            $tardies = [];
            $undertimes = [];
            $dtr_report = [];
            $not_completed_hrs = [];

            foreach ($period as $date) {
                $day_entry = clone $time_entries->whereBetween('time_start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
                $flag = clone $events->where('tag', Events::FLAG)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
                $holiday = clone $events->where('tag', Events::HOL)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
                $suspended = clone $events->where('tag', Events::SUS)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
                $schedule = clone $events->where('hris_number', $employee->hris_number)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();

                $schedule_remarks = $schedule->map(fn($item) => ['value' => ($item->mov) ? '<a href="' . route('admin.dtr.pdf.view-mov', ['mov' => $item->mov?->id]) . '" target="_blank">' . strtoupper($item->tag->value) . '</a>' : strtoupper($item->tag->getLabel())])->toArray();
                $flag_remarks = $flag->map(fn($item) => ['value' => strtoupper($item->tag->value)])->toArray();
                $suspended_remarks = $suspended->map(fn($item) => ['value' => strtoupper($item->tag->value)])->toArray();
                $holiday_remarks = $holiday->map(fn($item) => ['value' => strtoupper($item->tag->getLabel())])->toArray();
                $remarks = collect()->merge($schedule_remarks)->merge($flag_remarks)->merge($suspended_remarks)->merge($holiday_remarks);

                // dd($date);
                $isflag = false;
                $tardy = null;
                $undertime = null;
                $time_in = null;
                $time_end = null;
                $schedule_type = null;
                $shift = null;

                if ($flag->isNotEmpty() || $date->dayOfWeek == Carbon::MONDAY) {
                    $isflag = true;
                }

                if ($day_entry->isNotEmpty()) {
                    $time_in = $day_entry->first()->time_start;
                    $time_end = $day_entry->first()->time_end;
                    $schedule_type = $day_entry->first()->schedule_type;

                    $official_start_time = null;
                    $official_end_time = null;
                    if ($schedule->isNotEmpty()) {
                        if ($schedule->first()->tag == Events::SHIFT) {
                            $shift_start = Carbon::parse($schedule->first()->start);
                            $shift_end = Carbon::parse($schedule->first()->end);
                            $official_start_time = $shift_start;
                            $official_end_time = $shift_end;
                            if ($shift_start->format('Y-m-d') == $shift_end->format('Y-m-d')) {
                                $shift = $shift_start->format('g:i A') . ' - ' . $shift_end->format('g:i A');
                            } else {
                                $shift = $shift_start->format('Y-m-d g:i A') . ' - ' . $shift_end->format('Y-m-d g:i A');
                            }
                        }
                    }


                    // if ($isflag) {
                    //     $official_start_time = '08:30';
                    //     $official_end_time = '17:30';
                    // }

                    if ($suspended->isNotEmpty()) {
                        if ($suspended->first()->start == $suspended->first()->end) {
                            $official_end_time  = $suspended->first()->end;
                        } else {
                            $official_end_time  = Carbon::parse($date->format('Y-m-d') . ' 17:00:00');
                        }
                    }


                    if ($official_start_time) {
                        $start = $official_start_time->seconds(0);
                        if ($time_in->format('H:i') > $start->format('H:i')) {
                            $start_minsdiff = $start->diffInMinutes($time_in);
                            $tardy = intdiv($start_minsdiff, 60) . ':' . ($start_minsdiff % 60);
                            // $tardies[$date->format('Y-m-d')] = [$start_minsdiff, [Events::SHIFT->value, $official_start_time], $time_in, true];
                            $tardies[] = $start_minsdiff;

                            // $end = Carbon::parse($date->format('Y-m-d') . ' ' . $official_end_time);
                            $end = $official_end_time;
                            if ($time_end == null) {
                                $tardy = intdiv(480, 60) . ':' . (480 % 60);
                                // $tardies[$date->format('Y-m-d')] = [480, [ScheduleType::FULLFLEXI->value, $official_start_time], $time_in, true];
                                $tardies[] = 480;
                                $not_completed_hrs[] = $date->format('Y-m-d');
                            } else if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                                $end_minsdiff = $time_end->diffInMinutes($end);
                                $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                                $undertimes[] = $end_minsdiff;
                            }
                        }
                    } else if ($official_end_time) {
                        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $official_end_time)->seconds(0);
                        if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                            $end_minsdiff = $time_end->diffInMinutes($end);
                            $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                            $undertimes[] = $end_minsdiff;
                        }
                    } else {
                        $start = Carbon::parse($date->format('Y-m-d') . ' 09:30:00')->seconds(0);
                        if ($time_end == null) {
                            $tardy = intdiv(480, 60) . ':' . (480 % 60);
                            // $tardies[$date->format('Y-m-d')] = [480, [ScheduleType::FULLFLEXI->value, $official_start_time], $time_in, true];
                            $tardies[] = 480;
                            $not_completed_hrs[] = $date->format('Y-m-d');
                        } else if ($time_in->format('Y-m-d H:i') > $start->format('Y-m-d H:i')) {
                            $start_minsdiff = $start->diffInMinutes($time_in);
                            $tardy = intdiv($start_minsdiff, 60) . ':' . ($start_minsdiff % 60);
                            $tardies[$date->format('Y-m-d')] = [$start_minsdiff, [ScheduleType::FULLFLEXI->value, $official_start_time], $time_in, true];
                            $tardies[] = $start_minsdiff;

                            $end = Carbon::parse($date->format('Y-m-d') . ' 18:30:00')->seconds(0);
                            if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                                $end_minsdiff = $time_end->diffInMinutes($end);
                                $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                                $undertimes[] = $end_minsdiff;
                            }
                        } else {
                            $end = $time_in->copy()->addHours(9)->seconds(0)->format('Y-m-d H:i');
                            // temporary minus 60 for lunch break, will update once HR update me in lunch break time range available
                            $minute_diff = $time_in->diffInMinutes($time_end) - 60;
                            if ($minute_diff <= 480) {
                                $end_minsdiff = $time_end->diffInMinutes($end);
                                $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                                $undertimes[] = $end_minsdiff;
                            }
                        }
                    }
                }
                $dtr_report[$date->format('Y-m-d')] = [
                    'hris_number' => $employee->hris_number,
                    'time_in' => $time_in?->format('g:i A'),
                    'time_end' => $time_end?->format('g:i A'),
                    'is_flag' => $isflag,
                    'tardy' => $tardy,
                    'undertime' => $undertime,
                    'shift' => $shift,
                    'remarks' => $remarks,
                ];
            }
        }

        // arsort($tardies);
        // $flexied = 0;
        // $total_tardies = array_sum($tardies);
        // foreach ($tardies as $key => $value) {
        //     $total_tardies += $value[0];

        //     // if ($value[1][0] == ScheduleType::FIXED->value) {
        //     //     if ($flexied <= 3) {
        //     //         if (!in_array($key, $not_completed_hrs)) {
        //     //             $date = Carbon::parse($key);
        //     //             if ($date->dayOfWeek != Carbon::MONDAY && !$value[3]) {
        //     //                 $official_time_in = $value[1][1];


        //     //                 $time_in = $value[2];
        //     //                 if ($time_in->between($official_time_in, $official_time_in->copy()->addHour(), true)) {
        //     //                     $dtr_report[$key]['tardy'] = null;
        //     //                     $total_tardies -= $value[0];
        //     //                     $flexied++;
        //     //                 }
        //     //             }
        //     //         }
        //     //     }
        //     // }
        // }

        $total_tardies = array_sum($tardies);

        $tardy_freq = count($tardies);
        $tardy_total = intdiv($total_tardies, 60) . ':' . ($total_tardies % 60);

        $undertime_freq = count($undertimes);
        $total_undertimes = array_sum($undertimes);
        $undertime_total = intdiv($total_undertimes, 60) . ':' . ($total_undertimes % 60);

        // $tardy_secs = array_sum($tardies);
        // $tardy_hours = floor($tardy_secs/3600);
        // $tardy_minutes = floor(($tardy_secs % 3600)/60);
        // $tardy_total = sprintf('%02d:%02d', $tardy_hours, $tardy_minutes);

        // $undertime_secs = array_sum($undertimes);
        // $undertime_hours = floor($undertime_secs/3600);
        // $undertime_minutes = floor(($undertime_secs % 3600)/60);
        // $undertime_total = sprintf('%02d:%02d', $undertime_hours, $undertime_minutes);

        // $overtime_secs = array_sum($overtimes);
        // $overtime_hours = floor($overtime_secs/3600);
        // $overtime_minutes = floor(($overtime_secs % 3600)/60);
        // $overtime_total = sprintf('%02d:%02d', $overtime_hours, $overtime_minutes);

        return [
            'reports' => $dtr_report,
            'total' => [
                'tardy' => [
                    $tardy_freq,
                    ($tardy_total == "0:0") ? '' : $tardy_total
                ],
                'undertime' => [
                    $undertime_freq,
                    ($undertime_total == "0:0") ? '' : $undertime_total
                ]
            ],
        ];
    }
}
