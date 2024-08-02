<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DtrReportController extends Controller
{
    public function individual($hris_number, Request $request)
    {
        dd($request->all());
    }
}
