<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <style>
        html { margin-top: 10px; margin-bottom: 5px; font-family: "Helvetica";}
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body style="font-size: 12px;">
    {{-- @if($is_solo) --}}
    @foreach ($employees as $employee)
        <div>
            <div style="width: 100%;margin-top:10px;">
                <div style="font-size: 12px; text-align: center;">
                    <img src="{{ public_path().'/image/favicon.png' }}" width="20px" height="27px" draggable="false" /> <br/>
                    <span class="font-semibold">Development Academy of the Philippines</span><br/>
                    <span>DAILY TIME RECORD FROM <strong >{{ $date_covered }}</strong></span>
                </div>
            </div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 100%;">Employee Name: <strong>{{ $employee->full_name }}</strong></td>
                    <td style="width: 100%;">Group/Center/Office: <strong>{{ $employee->department->description }}</strong></td>
                </tr>
            </table>

            <table style="width: 100%;text-align:center;table-layout:auto;border-collapse: collapse;margin-top:10px;">
                <tr>
                    <td style="width: 20%;border: 1px solid black;font-weight:300;">Date</td>
                    <td style="width: 20%;border: 1px solid black;font-weight:300;">Scheduled Duties</td>
                    <td style="border: 1px solid black;font-weight:300;">Time In</td>
                    <td style="border: 1px solid black;font-weight:300;">Time Out</td>
                    <td style="border: 1px solid black;font-weight:300;">Tardy</td>
                    <td style="border: 1px solid black;font-weight:300;">Undertime</td>
                    {{-- <td style="border: 1px solid black;font-weight:300;">Overtime</td> --}}
                    <td style="border: 1px solid black;font-weight:300;">Remarks</td>
                </tr>

                @foreach ($employee['reports'] as $date => $report)
                    @php
                        $date = Carbon\Carbon::parse($date);
                        // $shift = Carbon\Carbon::parse($report['shift']);
                        // $shift_sched = ($report['w_shift']) ? $shift->format('g:i A') . '-' . $shift->copy()->addHours(8)->format('g:i A') : '';
                    @endphp
                    @if($date)
                        <tr>
                            <td style="border: 1px solid black;text-align:left;padding-left:5px;">{{ $date->format('Y-m-d, D') }}</td>
                            <td style="border: 1px solid black;">{{ $report['shift'] }}</td>
                            <td style="border: 1px solid black;">{{ $report['time_in'] }}</td>
                            <td style="border: 1px solid black;">{{ $report['time_end'] }}</td>
                            <td style="border: 1px solid black;">{{ $report['tardy'] }}</td>
                            <td style="border: 1px solid black;">{{ $report['undertime'] }}</td>
                            {{-- <td style="border: 1px solid black;">{{ $report['overtime'] }}</td> --}}
                            <td style="border: 1px solid black;font-size: 9px;">{!! $report['remarks']->where('value', '<>', 'SHIFT')->pluck('value')->implode(',') !!}</td>
                        </tr>
                    @endif
                @endforeach
                <tr style="text-align:right;font-weight:300">
                    <td colspan="4" style="border: 1px solid black;">TOTAL DEDUCTED TIME:&nbsp;</td>
                    <td style="border: 1px solid black;text-align:center;">{{ $employee['total']['tardy'][1] }}</td>
                    <td style="border: 1px solid black;text-align:center;">{{ $employee['total']['undertime'][1] }}</td>
                    <td style="border: 1px solid black;text-align:center;"></td>
                    {{-- <td style="border: 1px solid black;text-align:center;">{{ $totals[2] }}</td> --}}
                </tr>
            </table>

            <table class="w-full"  style="font-size: 10px;width:100%;margin-top:10px;">
                <tr>
                    <td style="width:100%;text-align:center;">I certify on my honor that the above are true and correct entries of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</td>
                    <td style="width:100%;text-align:center;">Verified in the prescribed office hours.</td>
                </tr>
                <tr>
                    <td class="text-center w-full" style="width:100%;text-align:center;">
                        <div class="w-96 border-b-2 mx-auto" style="width:14rem;border-bottom: 1px solid;height:40px;margin-left:auto;margin-right:auto;"></div>
                        <div>Employee</div>
                    </td>
                    <td class="text-center w-full" style="width:100%;text-align:center;">
                        <div class="w-96 border-b-2 mx-auto" style="width:14rem;border-bottom: 1px solid;height:40px;margin-left:auto;margin-right:auto;"></div>
                        <div>Center/Department Head</div>
                    </td>
                </tr>
            </table>
        </div>

        @if(($loop->iteration % 2) != 0)
            <div style="border-bottom: 2px dashed;margin-top:1.5rem;width:100%;"></div>
        @endif
        @if($loop->iteration != $loop->count)
            @if(($loop->iteration % 2) == 0)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach
    {{-- @else
        @foreach ($employees as $employee)
            <div>
                <div style="width: 100%;">
                    <div style="font-size: 12px; text-align: center;">
                        <img src="{{ public_path().'/image/favicon.png' }}" width="20px" height="27px" draggable="false" /> <br/>
                        <span class="font-semibold">Development Academy of the Philippines</span><br/>
                        <span>DAILY TIME RECORD FROM <strong >{{ $date_start }}</strong> TO <strong >{{ $date_end }}</strong></span>
                    </div>
                </div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 100%;">Employee Name: <strong>{{ $employee->name }}</strong></td>
                        <td style="width: 100%;">Group/Center/Office: <strong>{{ $employee->groupCenterOffice() }}</strong></td>
                    </tr>
                </table>

                <table style="width: 100%;text-align:center;table-layout:auto;border-collapse: collapse;margin-top:10px;">
                    <tr>
                        <td style="width: 20%;border: 1px solid black;font-weight:300;">Date</td>
                        <td style="width: 20%;border: 1px solid black;font-weight:300;">Scheduled Duties</td>
                        <td style="border: 1px solid black;font-weight:300;">Time In</td>
                        <td style="border: 1px solid black;font-weight:300;">Time Out</td>
                        <td style="border: 1px solid black;font-weight:300;">Tardy</td>
                        <td style="border: 1px solid black;font-weight:300;">Undertime</td>
                        <td style="border: 1px solid black;font-weight:300;">Overtime</td>
                    </tr>

                    @foreach ($employee->reports as $date => $report)
                        @php
                            $date = Carbon\Carbon::parse($date);
                            $shift = Carbon\Carbon::parse($report['shift']);
                            $shift_sched = ($report['w_shift']) ? $shift->format('g:i A') . '-' . $shift->copy()->addHours(8)->format('g:i A') : '';
                        @endphp
                        @if($date)
                            <tr>
                                <td style="border: 1px solid black;text-align:left;padding-left:5px;">{{ $date->format('Y-m-d, D') }}</td>
                                <td style="border: 1px solid black;">{{ $shift_sched }}</td>
                                <td style="border: 1px solid black;">{{ $report['time_in'] }}</td>
                                <td style="border: 1px solid black;">{{ $report['time_end'] }}</td>
                                <td style="border: 1px solid black;">{{ $report['tardy'] }}</td>
                                <td style="border: 1px solid black;">{{ $report['undertime'] }}</td>
                                <td style="border: 1px solid black;">{{ $report['overtime'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr style="text-align:right;font-weight:300">
                        <td colspan="4" style="border: 1px solid black;">TOTAL DEDUCTED TIME:&nbsp;</td>
                        <td style="border: 1px solid black;text-align:center;">{{ $employee->totals[0] }}</td>
                        <td style="border: 1px solid black;text-align:center;">{{ $employee->totals[1] }}</td>
                        <td style="border: 1px solid black;text-align:center;">{{ $employee->totals[2] }}</td>
                    </tr>
                </table>

                <table class="w-full"  style="font-size: 10px;width:100%;margin-top:10px;">
                    <tr>
                        <td style="width:100%;text-align:center;">I certify on my honor that the above are true and correct entries of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</td>
                        <td style="width:100%;text-align:center;">Verified in the prescribed office hours.</td>
                    </tr>
                    <tr>
                        <td class="text-center w-full" style="width:100%;text-align:center;">
                            <div class="w-96 border-b-2 mx-auto" style="width:14rem;border-bottom: 1px solid;height:40px;margin-left:auto;margin-right:auto;"></div>
                            <div>Employee</div>
                        </td>
                        <td class="text-center w-full" style="width:100%;text-align:center;">
                            <div class="w-96 border-b-2 mx-auto" style="width:14rem;border-bottom: 1px solid;height:40px;margin-left:auto;margin-right:auto;"></div>
                            <div>Center/Department Head</div>
                        </td>
                    </tr>
                </table>
            </div>
            @if(($loop->iteration % 2) != 0)
                <div style="border-bottom: 2px dashed;margin-top:1.5rem;width:100%;"></div>
            @endif
            @if($loop->iteration != $loop->count)
                @if(($loop->iteration % 2) == 0)
                    <div class="page-break"></div>
                @endif
            @endif
        @endforeach
    @endif --}}
</body>
</html>
