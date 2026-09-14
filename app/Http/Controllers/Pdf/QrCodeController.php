<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;

class QrCodeController extends Controller
{
    public function employee(Employee $employee)
    {
        Gate::authorize('viewQrCode', $employee);

        // $employee = Employee::where('hris_number', $hris)->first();

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('components.layouts.pdf.qr-code', ['employees' => [$employee]])->setPaper('a4', 'landscape');

        return $pdf->stream($employee->hris_number.'-qrcode.pdf');
    }

    public function bulk()
    {
        Gate::authorize('viewAnyQrCode', Employee::class);

        $employees = Employee::whereIn('hris_number', ['213348', '213215', '206999', '208192', '200160', '211843', '211351', '039755', '213197', '213173', '212494', '213211', '213199', '213289', '213308', '213247', '213360'])->get();

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('components.layouts.pdf.qr-code', ['employees' => $employees])->setPaper('a4', 'landscape');

        return $pdf->stream('edtr-qrcode-idcard.pdf');
    }
}
