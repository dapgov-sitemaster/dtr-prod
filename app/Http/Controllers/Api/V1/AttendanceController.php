<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Jobs\TimeEntryJob;
use App\Enums\ScheduleType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Resources\EmployeeResource;

class AttendanceController extends Controller
{
    public function info(Request $request)
    {
        try {
            // $hris = Crypt::decryptString($request->hris);
            $hris = $request->hris;

            $employee = Employee::where('hris_number', $hris)->first();
            // return $employee;

            return response()->json(new EmployeeResource($employee), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'HRIS could not be found. QR Code is not valid!'], 422);
        }
    }

    public function time_capture(Request $request)
    {
        try {
            $employee = Employee::where('hris_number', $request->hris)->first();

            if ($employee->department->center != "DAPCC") {
                $schedule = Event::where('hris_number', $employee->hris_number)->whereDate('start', now()->format('Y-m-d'))->where('description', 'Work From Home (Hybrid)')->first();

                if ($schedule) {
                    return response()->json([
                        'message' => $employee->first_name . ' is currently on Work From Home arrangement. Please contact your Admin Coordinator to change your Work From Home to Hybrid.'
                    ], 422);
                }

                $official_time = null;
                $sched_type = ($employee->official_time) ? $employee->official_time->schedule_type : ScheduleType::FULLFLEXI;
                if ($sched_type == ScheduleType::FIXED) {
                    $official_time = $employee->official_time->time_in->format('H:i:s');
                }
                // $official_time = (!$employee->official_time->isEmpty()) ? $employee->official_time->time_in : date('H:i:s', strtotime('08:00:00'));
                $timestart = now();

                $latest = TimeEntry::where('hris_number', $request->hris)->whereDate('time_start', now()->format('Y-m-d'))->latest()->first();

                // return auth()->user()->employee;

                if ($latest != null) {
                    if ($latest->time_end == null) {
                        TimeEntryJob::dispatch(type: 'update', data: [
                            'latest' => $latest,
                            'time_end' => $timestart,
                        ]);

                        return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A')], 200);
                    }
                }

                TimeEntryJob::dispatch(type: 'new', data: [
                    'hris_number' => $employee->hris_number,
                    'timestart' => $timestart,
                    'department_id' => $employee->department_id,
                    'sched_type' => $sched_type,
                    'official_time' => $official_time,
                    'tag' => 'ROS',
                    'timekeeper_id' => auth()->user()->id
                ]);

                return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A')], 200);

                // if ($latest?->time_end) {
                //     TimeEntryJob::dispatch(type: 'new', data: [
                //         'hris_number' => $employee->hris_number,
                //         'timestart' => $timestart,
                //         'department_id' => $employee->department_id,
                //         'sched_type' => $sched_type,
                //         'official_time' => $official_time,
                //         'tag' => 'ROS',
                //         'timekeeper_id' => auth()->user()->id
                //     ]);
                //     // $time_entry = new TimeEntry();
                //     // $time_entry->hris_number = $employee->hris_number;
                //     // $time_entry->time_start = $timestart;
                //     // $time_entry->department_id = $employee->department_id;
                //     // $time_entry->sched_type = $sched_type;
                //     // $time_entry->official_time = $official_time;
                //     // $time_entry->tag = 'ROS';
                //     // $time_entry->timekeeper_id = auth()->user()->id;
                //     // $time_entry->save();
                // } else {
                //     TimeEntryJob::dispatch(type: 'update', data: [
                //         'latest' => $latest,
                //         'time_end' => $timestart,
                //     ]);
                //     // $latest->time_end = $timestart;
                //     // $latest->save();
                // }

            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function dapcc_time_entry(Request $request)
    {
        try {
            $employee = Employee::with('department')->where('hris_number', $request->hris)->first();

            if ($employee->department->center == 'DAPCC') {
                $timestart = Carbon::now();
                $time_entry = TimeEntry::where('hris_number', $employee->hris_number)->where('time_end', null)->where('tag', 'ros')->orderBy('time_start', 'desc')->get();
                $shift = Event::where('hris_number', $employee->hris_number)->where('tag', \App\Enums\Dapcc\Events::SHIFT)->whereDate('start', $time_entry->time_start->format('Y-m-d'))->first();

                $timecapture = $timestart->copy()->toDateTimeString();
                $shift_remarks = '';

                if ($shift) {
                    $shift_remarks = $employee->full_name . ', you have no schedule of duty for today. But we will record your Time Entry. Please contact your Admin Coordinator to schedule you to duty today. Thank you.';
                }

                if ($time_entry) {
                    $diffHours = $time_entry->time_start->diffInHours($timecapture, true);
                    TimeEntryJob::dispatch(type: 'update', data: [
                        'latest' => $time_entry->first(),
                        'time_end' => $timestart,
                    ]);
                    // $time_entry->time_end = $timecapture;
                    // $time_entry->save();

                    if ($diffHours < 25) {
                        return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A'), 'remarks' => $shift_remarks], 200);
                        // return response()->json([
                        //     'capture_status' => 'timein',
                        //     'timeentry' => $timeentry_formatted,
                        //     'time_only' => $time_only,
                        // ]);
                    }

                    // return response()->json([
                    //     'capture_status' => 'timein-warning',
                    //     'timeentry' => $timeentry_formatted,
                    //     'time_only' => $time_only,
                    //     'remarks' => 'Hey! It seems like you forgot to logout and you have rendered more than 24 hours. This is your Time out for date '.$time_entry->time_start->format('M d, Y').'. You have to Scan again to log your Time in for today. Thank you.'
                    // ]);
                    return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A'), 'remarks' => 'Hey! It seems like you forgot to logout and you have rendered more than 24 hours. This is your Time out for date ' . $time_entry->time_start->format('M d, Y') . '. You have to Scan again to log your Time in for today. Thank you.'], 200);
                } else {
                    $official_time = null;
                    $sched_type = ($employee->official_time) ? $employee->official_time->schedule_type : ScheduleType::FULLFLEXI;
                    if ($sched_type == ScheduleType::FIXED) {
                        $official_time = $employee->official_time->time_in->format('H:i:s');
                    }

                    if ($time_entry->count() > 0) {
                        return response()->json([
                            'capture_status' => 'error',
                            'remarks' => "You have already completed your time entries for today`s duty. Your Time in: " . $time_entry->first()->time_start->format('g:i A') . ", your Time out: " . $time_entry->first()->time_end->format('g:i A')
                        ]);
                    }

                    TimeEntryJob::dispatch(type: 'new', data: [
                        'hris_number' => $employee->hris_number,
                        'timestart' => $timestart,
                        'department_id' => $employee->department_id,
                        'sched_type' => $sched_type,
                        'official_time' => $official_time,
                        'tag' => 'ros',
                        'timekeeper_id' => auth()->user()->id
                    ]);

                    return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A'), 'remarks' => $shift_remarks], 200);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
