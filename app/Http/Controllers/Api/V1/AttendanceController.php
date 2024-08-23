<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Location;
use App\Models\TimeEntry;
use App\Jobs\TimeEntryJob;
use App\Enums\ScheduleType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Http\Resources\EmployeeResource;

class AttendanceController extends Controller
{
    public function info(Request $request)
    {
        try {
            $hris = Crypt::decryptString($request->hris);
            // $hris = $request->hris;


            $employee = Employee::with('department')->where('hris_number', $hris)->first();
            // return $employee;

            if ($request->new) {
                return response()->json(new EmployeeResource($employee), 200);
            }

            return response()->json([
                'capture_status' => 'timein',
                'timeentry' => now()->format('M d, Y g:i A'),
                'hris' => $employee->hris_number,
                'name' => $employee->full_name,
                'group' => $employee->department->group,
                'center' => $employee->department->center,
                'office' => $employee->department->office,
                'image_path' => $employee->image_path,
                'remarks' => ""
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'HRIS could not be found. QR Code is not valid!'], 422);
        }
    }

    public function old_time_capture(Request $request)
    {
        $hris_number = $request->hris;
        if ($hris_number == null) {
            return "hris is required";
        }

        try {
            // $hris_number = Crypt::decryptString($hris_number);
            $employee = Employee::with('department')->where('hris_number', $hris_number)->first();

            if ($employee->department->center == 'DAPCC') {
                return $this->dapcc_time_entry($request);
            } else {
                return $this->time_capture($request);
            }
        } catch (\Exception $e) {
            info('Time Entry error!', [$e->getMessage()]);
            return response()->json(['message' => 'Unknown error occured!'], 422);
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
            return response()->json(['message' => 'PASIG employees only!'], 422);
        } catch (\Exception $e) {
            info('PASIG Time Entry error!', [$e->getMessage()]);
            return response()->json(['message' => 'Unknown error occured!'], 422);
        }
    }

    public function dapcc_time_entry(Request $request)
    {
        try {
            $employee = Employee::with('department')->where('hris_number', $request->hris)->first();

            if ($employee->department->center == 'DAPCC') {
                $timestart = Carbon::now();
                $time_entry = TimeEntry::where('hris_number', $employee->hris_number)->where('tag', 'ros')->orderBy('time_start', 'desc')->get();
                $shift = Event::where('hris_number', $employee->hris_number)->where('tag', \App\Enums\Dapcc\Events::SHIFT)->whereDate('start', $timestart->format('Y-m-d'))->first();

                $timecapture = $timestart->copy()->toDateTimeString();
                $shift_remarks = '';

                if (!$shift) {
                    $shift_remarks = $employee->full_name . ', you have no schedule of duty for today. But we will record your Time Entry. Please contact your Admin Coordinator to schedule your duty today. Thank you.';
                }

                if ($time_entry->count() == 1) {
                    if ($time_entry->first()->time_end != null) {
                        return response()->json([
                            'capture_status' => 'error',
                            'remarks' => "You have already completed your time entries for today`s duty. Your Time in: " . $time_entry->first()->time_start->format('g:i A') . ", your Time out: " . $time_entry->first()->time_end->format('g:i A')
                        ]);
                    }

                    $diffHours = $time_entry->first()->time_start->diffInHours($timecapture, true);
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
            return response()->json(['message' => 'DAPCC employees only!'], 422);
        } catch (\Exception $e) {
            info('DAPCC Time Entry error!', [$e->getMessage()]);
            return response()->json(['message' => 'Unknown error occured!'], 422);
        }
    }

    public function mvpool_time_entry(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $employee = Employee::where('hris_number', auth()->user()->hris_number)->first();
        $official_time = null;
        $sched_type = ($employee->official_time) ? $employee->official_time->schedule_type : ScheduleType::FULLFLEXI;
        if ($sched_type == ScheduleType::FIXED) {
            $official_time = $employee->official_time->time_in->format('H:i:s');
        }

        // $official_timein = ($employee->official_time) ? $employee->official_time->time_in : date('H:i:s', strtotime('08:00:00'));
        try {
            $timestart = now();
            $maps_api = "https://atlas.microsoft.com/search/address/reverse/json?subscription-key=WYk5KFvt_gxcLFXFN9TfbruJYxXv_HtTCJX-aftj5GU&api-version=1.0&query=" . $validated['latitude'] . "," . $validated['longitude'];

            $response = Http::get($maps_api);

            if (!$response->ok()) {
                return response()->json(['capture_status' => 'error', 'remarks' => 'Something went wrong.']);
                // $new_loc = new Location();
                // $new_loc->time_entry_id = $start->id;
                // $new_loc->location = $addresses['address']['freeformAddress'];
                // $new_loc->coordinates = $validated['latitude'] . "," . $validated['longitude'];
                // $new_loc->save();
            }
            $latest = TimeEntry::where('hris_number', $employee->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->latest()->first();
            $addresses = $response->json()['addresses'][0];

            if ($latest != null) {
                if ($latest->time_end == null) {
                    TimeEntryJob::dispatch(type: 'update', data: [
                        'latest' => $latest,
                        'time_end' => $timestart,
                        'location' => [
                            'address' => $addresses['address']['freeformAddress'],
                            'coordinates' => $validated['latitude'] . "," . $validated['longitude'],
                        ],
                    ]);

                    return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A')], 200);
                }
            }

            $timeonly = Carbon::now()->format('g:i A');

            TimeEntryJob::dispatch(type: 'new', data: [
                'hris_number' => $employee->hris_number,
                'timestart' => $timestart,
                'department_id' => $employee->department_id,
                'sched_type' => $sched_type,
                'official_time' => $official_time,
                'tag' => 'MVPOOL',
                'timekeeper_id' => auth()->user()->id,
                'location' => [
                    'address' => $addresses['address']['freeformAddress'],
                    'coordinates' => $validated['latitude'] . "," . $validated['longitude'],
                ],
            ]);

            // $start = new TimeEntry();
            // $start->time_start = $timestart;
            // $start->hris_number = $employee->hris_number;
            // $start->office_id = $employee->office_id;
            // $start->security_email = $employee->user->email;
            // $start->official_time = $official_timein;
            // $start->tag = 'MVPOOL';
            // $start->save();

            $time_entries_count = TimeEntry::where('hris_number', $employee->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->count();
            // $maps_api = "https://atlas.microsoft.com/search/address/reverse/json?subscription-key=WYk5KFvt_gxcLFXFN9TfbruJYxXv_HtTCJX-aftj5GU&api-version=1.0&query=" . $validated['latitude'] . "," . $validated['longitude'];

            // $response = Http::get($maps_api);

            // if ($response->ok()) {
            //     $addresses = $response->json()['addresses'][0];
            //     $new_loc = new Location();
            //     $new_loc->time_entry_id = $start->id;
            //     $new_loc->location = $addresses['address']['freeformAddress'];
            //     $new_loc->coordinates = $validated['latitude'] . "," . $validated['longitude'];
            //     $new_loc->save();
            // }

            return response()->json(['start' => ($time_entries_count % 2 == 0) ? true : false, 'timeonly' => $timeonly]);
        } catch (\Throwable $th) {
            return response()->json(['capture_status' => 'error', 'remarks' => 'Something went wrong.']);
        }
    }

    public function location(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $maps_api = "https://atlas.microsoft.com/search/address/reverse/json?subscription-key=WYk5KFvt_gxcLFXFN9TfbruJYxXv_HtTCJX-aftj5GU&api-version=1.0&query=" . $latitude . "," . $longitude;

        $response = Http::get($maps_api);
        if ($response->ok()) {
            $addresses = $response->json()['addresses'][0];

            return response()->json(['location' => $addresses['address']['freeformAddress']]);
        } else {
            return response()->json(['capture_status' => 'error']);
        }
    }

    public function mvpool_time_entries()
    {
        try {
            // disabled mvpool_time_entries
            // $user = auth()->user();
            // $time_entries = TimeEntryResource::collection(TimeEntry::where('hris_number', $user->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->get());
            return response()->json(['time_entries' => collect()]);
        } catch (\Throwable $th) {
            return response()->json(['capture_status' => 'error', 'remarks' => 'Somethin went wrong.']);
        }
    }
}
