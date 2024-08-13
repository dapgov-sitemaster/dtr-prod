<?php

namespace App\Actions;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Carbon\CarbonPeriod;
use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Collection;

class ProcessReport
{
    public function handle($employee, Collection $dtr, $date_from, $date_to, $events): array
    {
        $dates = CarbonPeriod::create($date_from, $date_to)->toArray();
        array_pop($dates);

        // $schedules = Event::query()
        //     ->select('id', 'tag', 'start', 'hris_number')
        //     ->with('mov:id,movable_id,movable_type')
        //     ->where('hris_number', $employee->hris_number)
        //     ->orWhere('hris_number', null)
        //     ->whereBetween('start', [$date_from, $date_to])
        //     ->get();

        $tardies = [];
        $undertimes = [];
        $dtr_report = [];
        $not_completed_hrs = [];

        foreach ($dates as $date) {
            $time = clone $dtr->whereBetween('time_start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();

            $flag = clone $events->where('tag', Events::FLAG)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
            $holiday = clone $events->where('tag', Events::HOL)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
            $suspended = clone $events->where('tag', Events::SUS)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
            $schedule = clone $events->where('hris_number', $employee->hris_number)->whereBetween('start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();

            $schedule_remarks = $schedule->map(fn ($item) => ['value' => ($item->mov) ? '<a href="' . route('admin.dtr.pdf.view-mov', ['mov' => $item->mov?->id]) . '" target="_blank">' . strtoupper($item->tag->value) . '</a>' : strtoupper($item->tag->value)])->toArray();
            $flag_remarks = $flag->map(fn ($item) => ['value' => strtoupper($item->tag->value)])->toArray();
            $suspended_remarks = $suspended->map(fn ($item) => ['value' => strtoupper($item->tag->value)])->toArray();
            $holiday_remarks = $holiday->map(fn ($item) => ['value' => strtoupper($item->tag->value)])->toArray();
            $remarks = collect()->merge($schedule_remarks)->merge($flag_remarks)->merge($suspended_remarks)->merge($holiday_remarks);
            // $remarks = collect($schedule_remarks);

            $tardy = null;
            $undertime = null;
            $time_in = null;
            $break_start = null;
            $break_end = null;
            $time_end = null;
            $no_out = null;
            $graced = false;
            $schedule_type = null;

            if ($time->isNotEmpty()) {
                $time_in = $time->first()->time_start;
                $time_end = $time->first()->time_end;
                $schedule_type = $time->first()->schedule_type;

                if ($schedule_type == ScheduleType::FIXED) {
                    $break_start = $time->first()->break_start;
                    $break_end = $time->first()->break_end;
                    $official_start_time = '08:30:00';
                    $grace_period = 15;

                    if ($flag->isNotEmpty()) {
                        if (date('H:i:s', strtotime('08:30')) > date('H:i:s', strtotime($time->first()->official_time))) {
                            $official_start_time = $time->first()->official_time;
                        }
                    } else if ($time->first()->time_start->dayOfWeek == Carbon::MONDAY) {
                        if (date('H:i:s', strtotime($time->first()->official_time)) < date('H:i:s', strtotime('08:30'))) {
                            $official_start_time = $time->first()->official_time;
                        }
                    } else if ($time->first()->official_time) {
                        $official_start_time = $time->first()->official_time;
                    }

                    if ($suspended->isNotEmpty()) {
                        if ($suspended->first()->start == $suspended->first()->end) {
                            $official_end_time  = $suspended->first()->end->format('H:i:s');
                        } else {
                            $official_end_time  = '17:00:00';
                        }
                    } else {
                        $official_end_time = Carbon::parse($official_start_time)->addHours(9)->format('H:i:s');
                    }

                    if ($holiday->isEmpty()) {
                        $official_start = Carbon::parse($date->format('Y-m-d') . ' ' . $official_start_time)->seconds(0);
                        $official_end = Carbon::parse($date->format('Y-m-d') . ' ' . $official_end_time)->seconds(0);

                        if ($time_in->format('H:i:s') > $official_start->format('H:i:s')) {
                            $official_start_diff = $official_start->diffInMinutes($time_in);
                        } else {
                            $official_start_diff = 10;
                        }

                        // START TARDY CALCULATION
                        if ($official_start->format('H:i') < $time_in->format('H:i')) {
                            if ($flag->isNotEmpty()) {
                                $tardy = intdiv($official_start_diff, 60) . ':' . ($official_start_diff % 60);
                                $tardies[$date->format('Y-m-d')] = [$official_start_diff, [ScheduleType::FIXED->value, $official_start], $time_in, true];
                            } else if ($date->dayOfWeek == Carbon::MONDAY) {
                                if (date('H:i', strtotime($time->first()->official_time)) < date('H:i', strtotime('08:30'))) {
                                    $tardy = intdiv($official_start_diff, 60) . ':' . ($official_start_diff % 60);
                                    $tardies[$date->format('Y-m-d')] = [$official_start_diff, [ScheduleType::FIXED->value, $official_start], $time_in, false];
                                }
                            } else if ($official_start_diff > $grace_period) {
                                $tardy = intdiv($official_start_diff, 60) . ':' . ($official_start_diff % 60);
                                $tardies[$date->format('Y-m-d')] = [$official_start_diff, [ScheduleType::FIXED->value, $official_start], $time_in, false];
                            } else if ($official_start_diff < $grace_period) {
                                $graced = true;
                            }
                        }
                        // END OF TARDY CALCULATIONS

                        $time_in_converted = Carbon::createFromFormat('H:i', $time_in->format('H:i'))->seconds(0);
                        $time_out_converted = Carbon::createFromFormat('H:i', $time_end->format('H:i'))->seconds(0);
                        $total_rendered = $time_in_converted->diffInMinutes($time_out_converted);
                        if (($total_rendered - 60) < 480) {
                            $not_completed_hrs[] = $date->format('Y-m-d');
                        }

                        if ($official_end->format('H:i:s') > $time_end->format('H:i:s')) {
                            $official_end_converted = Carbon::createFromFormat('H:i', $official_end->format('H:i'))->seconds(0);
                            $undertime_mins = $time_out_converted->diffInMinutes($official_end_converted);
                            $undertime = intdiv($undertime_mins, 60) . ':' . ($undertime_mins % 60);
                            $undertimes[] = $undertime_mins;
                        }
                    }
                } else if ($schedule_type == ScheduleType::FULLFLEXI) {
                    $break_start = $time->first()->break_start;
                    $break_end = $time->first()->break_end;
                    $official_start_time = null;
                    $official_end_time = null;

                    if ($flag->isNotEmpty() || $time->first()->time_start->dayOfWeek == Carbon::MONDAY) {
                        $official_start_time = '08:30';
                        $official_end_time = '17:30';
                    }

                    if ($suspended->isNotEmpty()) {
                        if ($suspended->first()->start == $suspended->first()->end) {
                            $official_end_time  = $suspended->first()->end->format('H:i');
                        } else {
                            $official_end_time  = '17:00';
                        }
                    }

                    if ($official_start_time) {
                        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $official_start_time);
                        if ($time_in->format('Y-m-d H:i') > $start->format('Y-m-d H:i')) {
                            $start_minsdiff = $start->diffInMinutes($time_in);
                            $tardy = intdiv($start_minsdiff, 60) . ':' . ($start_minsdiff % 60);
                            $tardies[$date->format('Y-m-d')] = [$start_minsdiff, [ScheduleType::FULLFLEXI->value, $official_start_time], $time_in, true];

                            $end = Carbon::parse($date->format('Y-m-d') . ' ' . $official_end_time);
                            if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                                $end_minsdiff = $time_end->diffInMinutes($end);
                                $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                                $undertimes[] = $end_minsdiff;
                            }
                        }
                    } else if ($official_end_time) {
                        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $official_end_time);
                        if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                            $end_minsdiff = $time_end->diffInMinutes($end);
                            $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                            $undertimes[] = $end_minsdiff;
                        }
                    } else {
                        $start = Carbon::parse($date->format('Y-m-d') . ' 09:30:00');
                        if ($time_in->format('Y-m-d H:i') > $start->format('Y-m-d H:i')) {
                            $start_minsdiff = $start->diffInMinutes($time_in);
                            $tardy = intdiv($start_minsdiff, 60) . ':' . ($start_minsdiff % 60);
                            $tardies[$date->format('Y-m-d')] = [$start_minsdiff, [ScheduleType::FULLFLEXI->value, $official_start_time], $time_in, true];

                            $end = Carbon::parse($date->format('Y-m-d') . ' 18:30:00');
                            if ($time_end->format('Y-m-d H:i') < $end->format('Y-m-d H:i')) {
                                $end_minsdiff = $time_end->diffInMinutes($end);
                                $undertime = intdiv($end_minsdiff, 60) . ':' . ($end_minsdiff % 60);
                                $undertimes[] = $end_minsdiff;
                            }
                        } else {
                            $end = $time_in->addHours(9)->format('Y-m-d H:i');
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
            }

            $dtr_report[$date->format('Y-m-d')] = [
                'hris_number' => $employee->hris_number,
                'time_in' => $time_in?->format('g:i A'),
                'break_start' => $break_start?->format('g:i A'),
                'break_end' => $break_end?->format('g:i A'),
                'time_end' => $time_end?->format('g:i A'),
                'schedule_type' => $schedule_type?->getAbbr(),
                'tardy' => $tardy,
                'undertime' => $undertime,
                'graced' => $graced,
                'flexied' => false,
                'no_out' => $no_out,
                'remarks' => $remarks,
            ];
        }

        arsort($tardies);
        $flexied = 0;
        $total_tardies = 0;
        foreach ($tardies as $key => $value) {
            $total_tardies += $value[0];

            if ($value[1][0] == ScheduleType::FIXED->value) {
                if ($flexied <= 3) {
                    if (!in_array($key, $not_completed_hrs)) {
                        $date = Carbon::parse($key);
                        if ($date->dayOfWeek != Carbon::MONDAY && !$value[3]) {
                            $official_time_in = Carbon::createFromFormat('Y-m-d H:i:s', $key . ' ' . $value[1][1])->seconds(0);


                            $time_in = $value[2];
                            if ($time_in->between($official_time_in, $official_time_in->copy()->addHour(), true)) {
                                $dtr_report[$key]['flexied'] = true;
                                $dtr_report[$key]['tardy'] = null;
                                $total_tardies -= $value[0];
                                $flexied++;
                            }
                        }
                    }
                }
            }
        }

        $tardy_freq = count($tardies) - $flexied;
        $tardy_total = intdiv($total_tardies, 60) . ':' . ($total_tardies % 60);

        $undertime_freq = count($undertimes);
        $total_undertimes = array_sum($undertimes);
        $undertime_total = intdiv($total_undertimes, 60) . ':' . ($total_undertimes % 60);

        return [
            'reports' => $dtr_report,
            'total' => [
                'total_flexi' => $flexied,
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
