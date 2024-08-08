<?php

namespace App\Http\Controllers\Pdf;

use Carbon\Carbon;
use App\Enums\Role;
use NumberFormatter;
use App\Models\Event;
use App\Models\Report;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Actions\ProcessReport;
use App\Actions\GenerateReport;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class DtrReportController extends Controller
{
    public function individual($hris_number, Request $request, GenerateReport $generate, ProcessReport $process)
    {
        $employee = Employee::with(['official_time', 'department'])->where('hris_number', $hris_number)->first();
        if ($employee) {
            $appointment_status = $employee->appointment_status->value;
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $date_from = null;
            $date_to = null;

            $range = $this->date_range($appointment_status, $cutoff, $yearmonth);

            $date_from = Carbon::parse($range['date_from']);
            $date_to = Carbon::parse($range['date_to']);

            $events = Event::query()
                ->select('id', 'tag', 'start', 'hris_number')
                ->where('hris_number', $employee->hris_number)
                ->orWhere('hris_number', NULL)
                ->whereBetween('start', [$date_from, $date_to])
                ->get();

            // $events = Event::query()
            //     ->select('id', 'tag', 'start', 'hris_number')
            //     ->orWhere('hris_number', NULL)
            //     ->whereBetween('start', [$date_from, $date_to])
            //     ->get();

            // $employee_event = Event::query()
            //     ->select('id', 'tag', 'start', 'hris_number')
            //     ->where('hris_number', $employee->hris_number)
            //     ->whereBetween('start', [$date_from, $date_to])
            //     ->get();

            $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
            $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'), $events);


            $employee['reports'] = $processed['reports'];
            $employee['total'] = $processed['total'];

            $title = 'DTR Report-' . $date_from->format('m/d/Y') . '-' . $date_to->format('m/d/Y') . ' (' . $employee->department->description . ').pdf';
            $pdf = App::make('dompdf.wrapper');
            $pdf->setOption(['dpi' => 100, 'defaultFont' => 'sans-serif']);
            $pdf->loadView('components.layouts.pdf.dtr-report', [
                'employees' => [$employee],
                'date_covered' => $date_from->format('M d, Y') . ' TO ' . $date_to->format('M d, Y'),
                'title' => $title,
            ]);
            return $pdf->stream($title);
        } else {
            abort(404);
        }
    }

    private function date_range($appointment_status, $cutoff, $yearmonth): array
    {
        $date_from = null;
        $date_to = null;

        if ($appointment_status == 'pbp') {
            $date_from = ($cutoff == 1) ? $yearmonth . '-01' : $yearmonth . '-16';
            $date_to = ($cutoff == 1) ? $yearmonth . '-15' : $yearmonth . '-' . date('t', strtotime($yearmonth . '-' . '01'));
        } else if ($appointment_status == 'npp') {
            $yearmonth_ex = explode('-', $yearmonth);
            $prevMonth = (int) $yearmonth_ex[1] - 1;

            $date_from = ($cutoff == 1) ? $yearmonth_ex[0] . '-' . $prevMonth . '-26' : $yearmonth . '-11';
            $date_to = ($cutoff == 1) ? $yearmonth . '-10' : $yearmonth . '-25';
        }

        return ['date_from' => $date_from, 'date_to' => $date_to];
    }

    public function bulk(Department $department, Request $request, GenerateReport $generate, ProcessReport $process)
    {
        $title = null;
        $department_query = null;
        $appointment_status = $request->get('appointment_status');

        if (auth()->user()->role == Role::ADMINCOORD || auth()->user()->role == Role::SUPERADMIN) {
            $title = $department->description;
            $department_query = [$department->id];
        } else if (auth()->user()->role == Role::CENTERADMINCOORD) {
            $title = $department->group . '/' . $department->center;
            $department_query = Department::select('id')->where('center', $department->center)->get()->toArray();
        }

        $employees = Employee::query()
            ->with(['official_time', 'department'])
            ->whereIn('department_id', $department_query)
            ->where('appointment_status', AppointmentStatus::tryFrom($appointment_status))
            ->get();

        if ($employees) {
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $range = $this->date_range($appointment_status, $cutoff, $yearmonth);

            $date_from = Carbon::parse($range['date_from']);
            $date_to = Carbon::parse($range['date_to']);

            $events = Event::query()
                ->select('id', 'tag', 'start', 'hris_number')
                ->orWhere('hris_number', NULL)
                ->whereBetween('start', [$date_from, $date_to])
                ->get();

            foreach ($employees as $employee) {
                $employee_event = Event::query()
                    ->select('id', 'tag', 'start', 'hris_number')
                    ->where('hris_number', $employee->hris_number)
                    ->whereBetween('start', [$date_from, $date_to])
                    ->get();

                $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
                $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'), $events->merge($employee_event));

                $employee['reports'] = $processed['reports'];
                $employee['total'] = $processed['total'];
            }

            $file_title = $title . '_DTR_report_(' . $request->get('yearmonth') . ' ' . (new NumberFormatter('en_US', NumberFormatter::ORDINAL))->format($request->get('cutoff')) . '-cutoff).pdf';
            $pdf = App::make('dompdf.wrapper');
            $pdf->setOption(['dpi' => 100, 'defaultFont' => 'sans-serif']);
            $pdf->loadView('components.layouts.pdf.dtr-report', [
                'employees' => $employees,
                'date_covered' => $date_from->format('M d, Y') . ' TO ' . $date_to->format('M d, Y'),
                'title' => $file_title,
            ]);
            return $pdf->stream($file_title);
        }
    }
}
