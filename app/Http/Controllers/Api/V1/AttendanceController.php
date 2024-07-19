<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Resources\EmployeeResource;
use App\Models\Event;
use App\Models\TimeEntry;

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
            $employee = Employee::where('employment_status', true)->where('hris_number', $request->hris)->first();

            if($employee->department->center === "DAPCC") {

            }
            else {
                $schedule = Event::where('hris_number', $employee->hris_number)->whereDate('start', now()->format('Y-m-d'))->where('description', 'Work From Home (Hybrid)')->first();

                if($schedule) {
                    return response()->json([
                        'message' => $employee->first_name.' is currently on Work From Home arrangement. Please contact your Admin Coordinator to change your Work From Home to Hybrid.'
                    ], 422);
                }

                $official_time = (!$employee->official_time->isEmpty()) ? $employee->official_time->time_in : date('H:i:s', strtotime('08:00:00'));
                $timestart = now();

                $latest = TimeEntry::where('hris_number', $request->hris)->whereDate('time_start', now()->format('Y-m-d'))->latest()->first();

                if($latest->time_end) {
                    $time_entry = new TimeEntry();
                    $time_entry->hris_number = $employee->hris_number;
                    $time_entry->time_start = $timestart;
                    $time_entry->department_id = $employee->department_id;
                    $time_entry->official_time = $official_time;
                    $time_entry->tag = 'ROS';
                    $time_entry->timekeeper_id = auth()->user()->id;
                    $time_entry->save();
                }
                else {
                    $latest->time_end = $timestart;
                    $latest->save();
                }

                return response()->json(['timeentry' => $timestart->copy()->format('M d, Y g:i A'), 'time_only' => $timestart->copy()->format('g:i A')], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'HRIS could not be found. QR Code is not valid!'], 422);
        }
    }
}
