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
use App\Actions\ProcessDapccReport;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Actions\Azure;

class DtrReportController extends Controller
{
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

    public function individual($hris_number, Request $request, GenerateReport $generate)
    {
        $employee = Employee::with(['official_time', 'department'])->where('hris_number', $hris_number)->first();
        if ($employee) {
            if ($employee->hris_number == "212469" || $employee->hris_number == "210798") {
                $request->merge(['week' => "null"]);
                $process = new ProcessDapccReport;
                return $this->dapcc_individual($employee->hris_number, $request, $process);
            }

            $appointment_status = $employee->appointment_status->value;
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $date_from = null;
            $date_to = null;

            $range = $this->date_range($appointment_status, $cutoff, $yearmonth);

            $date_from = Carbon::parse($range['date_from']);
            $date_to = Carbon::parse($range['date_to']);

            $events = Event::query()
                ->select('id', 'tag', 'start', 'end', 'hris_number', 'status')
                ->where('hris_number', $employee->hris_number)
                ->orWhereIn('tag', [\App\Enums\Events::HOL, \App\Enums\Events::SUS, \App\Enums\Events::FLAG])
                ->whereBetween('start', [$date_from->copy()->subDay(), $date_to->copy()->addDay()])
                ->get();

            $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
            // dd($dtr_report);
            $process = new ProcessReport;
            $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'), $events);


            $azure = new Azure;
            $employee['reports'] = $processed['reports'];
            $employee['total'] = $processed['total'];
            $employee['blob'] = ($employee->signature_path) ? $azure->get($employee->signature_path) : null;

            $title = 'DTR Report-' . $date_from->format('m/d/Y') . '-' . $date_to->format('m/d/Y') . ' (' . $employee->hris_number . ').pdf';
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

    public function bulk(Department $department, Request $request, GenerateReport $generate, ProcessReport $process)
    {
        $title = null;
        $appointment_status = $request->get('appointment_status');

        if (auth()->user()->hasRole(Role::ADMINCOORD) || auth()->user()->hasRole(Role::SUPERADMIN) || auth()->user()->hasRole(Role::HRADMIN)) {
            $title = $department->description;
        } else if (auth()->user()->hasRole(Role::CENTERADMINCOORD)) {
            $title = $department->group . '/' . $department->center;
        } else if (auth()->user()->hasRole(Role::GROUPADMINCOORD)) {
            $title = $department->group;
        }

        $employees = Employee::query()
            ->with(['official_time', 'department'])
            ->where('department_id', $department->id)
            ->where('appointment_status', AppointmentStatus::tryFrom($appointment_status))
            ->get();

        if ($employees) {
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $range = $this->date_range($appointment_status, $cutoff, $yearmonth);

            $date_from = Carbon::parse($range['date_from']);
            $date_to = Carbon::parse($range['date_to']);

            $azure = new Azure;
            $events = Event::query()
                ->select('id', 'tag', 'start', 'end', 'hris_number')
                ->orWhereIn('tag', [\App\Enums\Events::HOL, \App\Enums\Events::SUS, \App\Enums\Events::FLAG])
                ->whereBetween('start', [$date_from->copy()->subDay(), $date_to->copy()->addDay()])
                ->get();

            foreach ($employees as $employee) {
                $employee_event = Event::query()
                    ->select('id', 'tag', 'start', 'hris_number')
                    ->where('hris_number', $employee->hris_number)
                    ->whereBetween('start', [$date_from->copy()->subDay(), $date_to->copy()->addDay()])
                    ->get();

                $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
                $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'), $events->merge($employee_event));

                $employee['reports'] = $processed['reports'];
                $employee['total'] = $processed['total'];
                $employee['blob'] = ($employee->signature_path) ? $azure->get($employee->signature_path) : null;
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

    public function dapcc_individual($hris_number, Request $request, ProcessDapccReport $process)
    {
        $employee = Employee::with(['official_time', 'department'])->where('hris_number', $hris_number)->first();
        if ($employee) {
            $appointment_status = $employee->appointment_status->value;
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $week = $request->get('week');
            $date_from = null;
            $date_to = null;

            if ($week == "null") {
                $range = $this->date_range($appointment_status, $cutoff, $yearmonth);
                $date_from = Carbon::parse($range['date_from']);
                $date_to = Carbon::parse($range['date_to']);
            } else {
                $range['date_from'] = Carbon::parse($week);
                $range['date_to'] = Carbon::parse($week)->addDays(6);
                $date_from = Carbon::parse($range['date_from']);
                $date_to = Carbon::parse($range['date_to']);
            }

            $events = Event::query()
                ->select('id', 'tag', 'start', 'end', 'hris_number')
                ->whereBetween('start', [$date_from, $date_to->copy()->addDay()])
                ->where('hris_number', $employee->hris_number)
                ->orWhereIn('tag', [\App\Enums\Dapcc\Events::HOL, \App\Enums\Dapcc\Events::SUS, \App\Enums\Dapcc\Events::FLAG])
                ->get();

            $processed = $process->handle($date_from->format('Y-m-d'), $date_to->copy()->addDay()->format('Y-m-d'), $employee, $events);

            $employee['reports'] = $processed['reports'];
            $employee['total'] = $processed['total'];

            $title = 'DTR Report-' . $date_from->format('m/d/Y') . '-' . $date_to->format('m/d/Y') . ' (' . $employee->hris_number . ').pdf';
            $pdf = App::make('dompdf.wrapper');
            $pdf->setOption(['dpi' => 100, 'defaultFont' => 'sans-serif']);
            $pdf->loadView('components.layouts.pdf.dapcc.dtr-report', [
                'employees' => [$employee],
                'date_covered' => $date_from->format('M d, Y') . ' TO ' . $date_to->format('M d, Y'),
                'title' => $title,
            ]);
            return $pdf->stream($title);
        } else {
            abort(404);
        }
    }

    public function dapcc_bulk(Department $department, Request $request, ProcessDapccReport $process)
    {
        $title = null;
        $appointment_status = $request->get('appointment_status');

        if (auth()->user()->hasRole(Role::ADMINCOORD) || auth()->user()->hasRole(Role::SUPERADMIN)) {
            $title = $department->description;
        } else if (auth()->user()->hasRole(Role::CENTERADMINCOORD)) {
            $title = $department->group . '/' . $department->center;
        } else if (auth()->user()->hasRole(Role::GROUPADMINCOORD)) {
            $title = $department->group;
        }

        $employees = Employee::query()
            ->with(['official_time', 'department'])
            ->departmentCovered()
            ->where('appointment_status', AppointmentStatus::tryFrom($appointment_status))
            ->get();
        if ($employees) {
            $yearmonth = $request->get('yearmonth');
            $cutoff = $request->get('cutoff');
            $week = $request->get('week');

            if ($week == "null") {
                $range = $this->date_range($appointment_status, $cutoff, $yearmonth);
                $date_from = Carbon::parse($range['date_from']);
                $date_to = Carbon::parse($range['date_to']);
            } else {
                $range['date_from'] = Carbon::parse($week);
                $range['date_to'] = Carbon::parse($week)->addDays(6);
                $date_from = Carbon::parse($range['date_from']);
                $date_to = Carbon::parse($range['date_to']);
            }

            $events = Event::query()
                ->select('id', 'tag', 'start', 'hris_number')
                ->whereIn('tag', [\App\Enums\Dapcc\Events::HOL, \App\Enums\Dapcc\Events::SUS, \App\Enums\Dapcc\Events::FLAG])
                ->whereBetween('start', [$date_from, $date_to])
                ->get();

            foreach ($employees as $employee) {
                $employee_event = Event::query()
                    ->select('id', 'tag', 'start', 'end', 'hris_number')
                    ->whereBetween('start', [$date_from, $date_to->copy()->addDay()])
                    ->where('hris_number', $employee->hris_number)
                    ->get();

                // $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
                // $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'), $events->merge($employee_event));

                $processed = $process->handle($date_from->format('Y-m-d'), $date_to->copy()->addDay()->format('Y-m-d'), $employee, $events->merge($employee_event));

                $employee['reports'] = $processed['reports'];
                $employee['total'] = $processed['total'];
            }

            if ($week == null) {
                $file_title = $title . '_DTR_report_(' . $request->get('yearmonth') . ' ' . (new NumberFormatter('en_US', NumberFormatter::ORDINAL))->format($request->get('cutoff')) . '-cutoff).pdf';
            } else {
                $file_title = $title . ' DTR Report - ' . $appointment_status . ' (' . $date_from . ' to ' . $date_to . ').pdf';
            }
            $pdf = App::make('dompdf.wrapper');
            $pdf->setOption(['dpi' => 100, 'defaultFont' => 'sans-serif']);
            $pdf->loadView('components.layouts.pdf.dapcc.dtr-report', [
                'employees' => $employees,
                'date_covered' => $date_from->format('M d, Y') . ' TO ' . $date_to->format('M d, Y'),
                'title' => $file_title,
            ]);
            return $pdf->stream($file_title);
        }
    }
}
