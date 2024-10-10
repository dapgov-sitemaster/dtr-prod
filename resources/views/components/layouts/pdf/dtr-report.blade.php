<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <!-- Styles -->
    <style>
        .w-full {
            width: 100%;
        }
        .text-center {
            text-align: center;
        }
        .font-semibold {
            font-weight: 600;
        }
        .underline {
            text-decoration-line: underline;
        }
        .flex {
            display: flex;
        }
        .justify-between {
            justify-content: space-between;
        }
        .mb-4 {
            margin-bottom: 5px/* 16px */;
        }
        .border {
            border: 1px solid;
        }
        .text-right {
            text-align: right;
        }
        .pr-2 {
            padding-right: 0.5rem/* 8px */;
        }
        .grid {
            display: grid;
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .mt-6 {
            margin-top: 0.5rem/* 24px */;
        }
        .mt-14 {
            margin-top: 3.5rem/* 24px */;
        }

        .w-96 {
            width: 14rem/* 384px */;
        }
        .border-b-2 {
            border-bottom 1px;
        }
         {
            border-color: rgb(0 0 0 );
        }
        .h-5 {
            height: 40px/* 20px */;
        }
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .border-dashed {
            border-style: dashed;
        }
        .collapse {
            border-collapse: collapse;
        }
        html { margin-top: 2px; margin-bottom: 5px;}
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body style="font-size: 10px">
    @foreach ($employees as $employee)
        <div class="w-full" style="margin-top: 10px;">
            <div class="text-center" style="font-size: 12px">
                <img src="{{ public_path().'/image/favicon.png' }}" width="20px" height="27px" draggable="false" /> <br/>
                <span class="font-semibold">Development Academy of the Philippines</span><br/>
                <span>DAILY TIME RECORD FROM <span class="underline font-semibold">{{ $date_covered }}</span></span>
            </div>
            <br/>
            <table class="w-full">
                <tr>
                    <td><span class="font-semibold">Employee Name</span>: {{ strtoupper($employee->full_name) }}</td>
                    <td>
                        <span class="font-semibold">Official Time</span>:
                        @if ($employee->official_time)
                            {{ ($employee->official_time->schedule_type == \App\Enums\ScheduleType::FIXED) ? $employee->official_time->time_in->format('g:i A'). ' - ' .$employee->official_time->time_in->copy()->addHours(9)->format('g:i A') : strtoupper(\App\Enums\ScheduleType::FULLFLEXI->getLabel()) }}
                        @else
                            {{ strtoupper(\App\Enums\ScheduleType::FULLFLEXI->getLabel()) }}
                        @endif
                        / <span class="font-semibold">Flag</span>: 8:30 AM - 5:30 PM
                    </td>
                </tr>
                <tr>
                    <td><span class="font-semibold">Group/Center/Office</span>: {{ $employee->department->description }}</td>
                    {{-- <td><span>Note: <span style="color: red;">*</span> Grace Period</span> | <span style="color:red">•</span> = Full Flexitime | <span style="color:blue">•</span> = Fixed Official Time</td> --}}
                    <td><span>Note: <span style="color:red">•</span> = Full Flexitime | <span style="color:blue">•</span> = Fixed Official Time</td>
                </tr>
            </table>

            <div>
                <table class="w-full mb-4 collapse" style="font-size: 12px">
                    <tr>
                        <th class="border ">Date</th>
                        <th class="border">AM IN</th>
                        <th class="border">AM OUT</th>
                        <th class="border">PM IN</th>
                        <th class="border">PM OUT</th>
                        <th class="border">AM TARDY</th>
                        <th class="border">PM TARDY</th>
                        <th class="border">UNDERTIME</th>
                        <th class="border">FLEXI</th>
                        <th class="border">REMARKS</th>
                    </tr>
                    @foreach ($employee->reports as $date => $report)
                        <tr>
                            {{-- DATE --}}
                            <td class="border">
                                {{ Carbon\Carbon::parse($date)->format('m-d-Y, D'). (($report['is_flag']) ? ', Flag' : '') }}
                                @if(Carbon\Carbon::parse($date)->dayOfWeek != Carbon\Carbon::MONDAY)
                                    @if($report['schedule_type'] != null )
                                        <span style="color: {{ ($report['schedule_type'] == 'FFT') ? 'red' : 'blue'}};font-size: small;">•</span>
                                    @endif
                                @endif
                            </td>
                            {{-- TIME IN AND OUT --}}
                            <td class="border text-center">
                                @if($report['time_in'])
                                    {{ (date('H:i:s', strtotime($report['time_in'])) < date('H:i:s', strtotime('12:00:00'))) ? $report['time_in'] : '' }}
                                @endif
                                @if($report['graced'])
                                    <span style="color: red;">*</span>
                                @endif
                            </td>
                            <td class="border text-center">
                                @if($report['time_end'])
                                    @if(date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('12:00:00')))
                                        @if (date('H:i:s', strtotime($report['time_in'])) < date('H:i:s', strtotime('12:00:00')))
                                            {{ $report['break_start'] }}
                                        @endif
                                    @else
                                        {{ $report['time_end'] }}
                                    @endif
                                @endif
                            </td>
                            <td class="border text-center">
                                @if($report['time_end'])
                                    @if (date('H:i:s', strtotime($report['time_in'])) > date('H:i:s', strtotime('12:00:00')))
                                        {{ $report['time_in'] }}
                                    @else
                                        @if (date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('13:00:00')))
                                            {{ $report['break_end'] }}
                                        @endif
                                    @endif
                                {{-- @else
                                    @if (date('H:i:s', strtotime($report['time_in'])) > date('H:i:s', strtotime('12:00:00')))
                                        {{ $report['time_in'] }}
                                    @endif --}}
                                @endif
                            </td>
                            {{-- <td class="border text-center">
                                @if($report['time_end'])
                                    @if(date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('12:00:00')))
                                        @if (date('H:i:s', strtotime($report['time_in'])) < date('H:i:s', strtotime('12:00:00')))
                                            12:00 PM
                                        @endif
                                    @else
                                        {{ $report['time_end'] }}
                                    @endif
                                @endif
                            </td>
                            <td class="border text-center">
                                @if($report['time_end'])
                                    @if (date('H:i:s', strtotime($report['time_in'])) > date('H:i:s', strtotime('12:00:00')))
                                        {{ $report['time_in'] }}
                                    @else
                                        @if (date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('13:00:00')))
                                            1:00 PM
                                        @endif
                                    @endif
                                @endif
                            </td> --}}
                            <td class="border text-center">
                                @if($report['time_end'])
                                    @if(date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('12:00:00')))
                                        {{ $report['time_end'] }}
                                    @endif
                                @else
                                    {{ $report['time_in'] }}
                                @endif

                                {{-- {{ (date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('12:00:00'))) ? date('g:i A', strtotime($report['time_end'])) : '' }} --}}
                            </td>
                            {{-- TARDY --}}
                            <td class="border text-center">
                                @if($report['no_out'])
                                    08:00:00
                                @else
                                    {{ $report['tardy'] }}
                                @endif
                            </td>
                            <td class="border text-center">
                                {{ $report['pm_tardy'] }}
                            </td>
                            {{-- UNDERTIME --}}
                            <td class="border text-center">
                                {{ $report['undertime'] }}
                            </td>
                            {{-- FLEXI --}}
                            <td class="border text-center">
                                {{ $report['flexied'] ? 'Yes' : '' }}
                            </td>
                            {{-- REMARKS --}}
                            <td class="border text-center">
                                {!! $report['remarks']->pluck('value')->implode(',') !!}
                            </td>
                        </tr>
                    @endforeach
                    {{-- footer --}}
                    <tr>
                        <td colspan="10" class="border"></td>
                    </tr>
                    <tr>
                        <th class="border text-right pr-2" colspan="5">TOTAL DEDUCTED TIME</th>
                        <td class="border text-center">
                            {{ $employee['total']['tardy'][1] }}
                        </td>
                        <td class="border text-center">
                            {{ $employee['total']['pm_tardy'][1] }}
                        </td>
                        <td class="border text-center">
                            {{ $employee['total']['undertime'][1] }}
                        </td>
                        <td class="border text-center"></td>
                        <td class="border text-center"></td>
                    </tr>
                    <tr>
                        <th class="border text-right pr-2" colspan="5">TOTAL FREQUENCY</th>
                        <td class="border text-center">
                            {{ $employee['total']['tardy'][0] }}
                        </td>
                        <td class="border text-center">
                            {{ $employee['total']['pm_tardy'][0] }}
                        </td>
                        <td class="border text-center">
                            {{ $employee['total']['undertime'][0] }}
                        </td>
                        <td class="border text-center">
                            {{ $employee['total']['total_flexi'] }}
                        </td>
                        <td class="border text-center"></td>
                    </tr>
                </table>
            </div>

            <table class="w-full"  style="font-size: 9px">
                <tr>
                    <td class="w-full text-center">I certify on my honor that the above are true and correct entries of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</td>
                    <td class="w-full text-center">Verified in the prescribed office hours.</td>
                </tr>
                <tr>
                    <td class="text-center w-full">
                        {{-- @if($employee['blob'])
                            <div class="w-96 border-b-2 h-5 mx-auto" style="border-bottom: 1px solid;">
                                <img src="data:image/png;base64, {{ $employee['blob'] }}" height="40px" alt="" draggable="false">
                            </div>
                        @else
                            <div class="w-96 border-b-2 mx-auto" style="border-bottom: 1px solid;height:40px;"></div>
                        @endif --}}
                        <div class="w-96 border-b-2 mx-auto" style="border-bottom: 1px solid;height:40px;"></div>

                        <div>Employee</div>
                    </td>
                    <td class="text-center w-full">
                        <div class="w-96 border-b-2 mx-auto" style="border-bottom: 1px solid;height:40px;"></div>
                        <div>Center/Department Head</div>
                    </td>
                </tr>
            </table>

            @if(($loop->iteration % 2) != 0)
                <div class="w-full mt-4" style="border-bottom: 2px dashed;"></div>
            @endif

            @if($loop->iteration != $loop->count)
                @if(($loop->iteration % 2) == 0)
                    <div class="page-break"></div>
                @endif
            @endif
        </div>

    @endforeach

</body>
</html>
