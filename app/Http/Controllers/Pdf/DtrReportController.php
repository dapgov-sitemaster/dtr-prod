<?php

namespace App\Http\Controllers\Pdf;

use Carbon\Carbon;
use App\Models\Report;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use App\Actions\ProcessReport;
use App\Actions\GenerateReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class DtrReportController extends Controller
{
    public function individual($hris_number, Request $request, GenerateReport $generate, ProcessReport $process)
    {
        $employee = Employee::where('hris_number', $hris_number)->first();
        if ($employee) {
            $date_from = Carbon::parse($request->get('date_from'));
            $date_to = Carbon::parse($request->get('date_to'));
            $dtr_report = $generate->handle($employee, $date_from->format('Y-m-d'), $date_to->copy()->addDay()->format('Y-m-d'));
            $processed = $process->handle($employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->copy()->addDay()->format('Y-m-d'));

            $employee['reports'] = $processed['reports'];
            $employee['total'] = $processed['total'];

            $title = $date_from->format('m/d/Y') . '-' . $date_to->format('m/d/Y') . ' (' . $employee->department->description . ').pdf';
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
}
