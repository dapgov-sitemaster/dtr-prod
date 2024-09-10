<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Employee;

class QrCodeController extends Controller
{
    public function employee(Employee $employee)
    {
        // $employee = Employee::where('hris_number', $hris)->first();

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('components.layouts.pdf.qr-code', ['employees' => [$employee]])->setPaper('a4', 'landscape');
        return $pdf->stream($employee->hris_number . '-qrcode.pdf');
    }

    public function bulk()
    {
        $employees = Employee::whereIn('hris_number', ['213309', '213288', '213098', '213314', '212796', '213306'])->get();

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('components.layouts.pdf.qr-code', ['employees' => $employees])->setPaper('a4', 'landscape');
        return $pdf->stream('edtr-qrcode-idcard.pdf');
    }
}
