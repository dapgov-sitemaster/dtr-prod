<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class QrCodeController extends Controller
{
    public function employee(Request $request)
    {
        $employee = auth()->user()->employee;
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('components.layouts.pdf.qr-code', ['employee' => $employee])->setPaper('a4', 'landscape');
        return $pdf->stream($employee->hris_number.'-qrcode.pdf');
    }
}
