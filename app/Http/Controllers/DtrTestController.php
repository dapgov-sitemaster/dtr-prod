<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\App;

class DtrTestController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $dates = CarbonPeriod::create('2024-09-01', '2024-09-15')->toArray();

        $pdf = App::make('dompdf.wrapper');
        $pdf->setOption(['dpi' => 100, 'defaultFont' => 'sans-serif']);
        $pdf->loadView('components.layouts.pdf.dtr-report2', [
            'dates' => $dates,
        ]);
        return $pdf->stream();
    }
}
