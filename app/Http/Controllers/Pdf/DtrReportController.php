<?php

namespace App\Http\Controllers\Pdf;

use App\Actions\GenerateReport;
use App\Actions\ProcessReport;
use App\Models\Report;
use App\Models\Employee;
use Carbon\CarbonPeriod;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DtrReportController extends Controller
{
    public function individual($hris_number, Request $request, GenerateReport $generate, ProcessReport $process)
    {
        $employee = Employee::where('hris_number', $hris_number)->first();
        $dtr_report = $generate->handle($employee, $request->get('date_from'), $request->get('date_to'));
        $processed = $process->handle($dtr_report, $request->get('date_from'), $request->get('date_to'));

        // dd($dtr_report);
    }
}
