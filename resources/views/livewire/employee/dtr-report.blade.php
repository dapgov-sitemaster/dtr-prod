<div>
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="flex justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                My DTR Report
            </div>
            <div class="hidden md:flex">
                <div class="mx-4">
                    <label class="mr-2 my-auto font-semibold text-sm">Year and Month: </label>
                    <x-filament::input.wrapper :valid="! $errors->has('yearmonth')">
                        <x-filament::input
                            type="month"
                            wire:model.live="yearmonth"
                        />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mr-2 my-auto font-semibold text-sm">Cut-off: </label>
                    <x-filament::input.wrapper :valid="! $errors->has('cutoff')">
                        <x-filament::input.select wire:model.live="cutoff">
                            <option hidden selected>Select Cut-off</option>
                            <option value="1">1st Cut-off</option>
                            <option value="2">2nd Cut-off</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </div>

        <div class="my-6 italic font-bold block md:hidden">This feature is currently not available on mobile devices</div>

        <div class="mt-6 hidden md:block">
            @if ($showDtr)
                <div id="main" class="border-2 border-gray-700 rounded-t-xl px-1">
                    <table class="w-full text-center">
                        <thead class="border-b-2 border-gray-600">
                            <th class="py-4">Date</th>
                            <th class="py-4">AM IN</th>
                            <th class="py-4">AM OUT</th>
                            <th class="py-4">PM IN</th>
                            <th class="py-4">PM OUT</th>
                            <th class="py-4">TARDY</th>
                            <th class="py-4">UNDERTIME</th>
                            {{-- <th class="py-4">HALF-DAY</th> --}}
                            <th class="py-4">FLEXI</th>
                            <th class="py-4">REMARKS</th>
                        </thead>
                        <tbody>
                            @foreach ($this->dtrReport['reports'] as $date => $report)
                            <tr class="border-t-2 border-b-2 border-gray-600">
                                <td class="py-2 text-left pl-2">
                                    {{ Carbon\Carbon::parse($date)->format('m-d-Y, D') }}
                                </td>
                                <td class="py-2">
                                    @if($report['time_in'])
                                        {{ (date('H:i:s', strtotime($report['time_in'])) < date('H:i:s', strtotime('12:00:00'))) ? date('g:i A', strtotime($report['time_in'])) : '' }}
                                    @endif
                                    @if($report['graced'])
                                        <span style="color: red;">*</span>
                                    @endif
                                </td>
                                <td class="py-2">
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
                                <td class="py-2">
                                    @if($report['time_end'])
                                        @if (date('H:i:s', strtotime($report['time_in'])) > date('H:i:s', strtotime('12:00:00')))
                                            {{ $report['time_in'] }}
                                        @else
                                            @if (date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('13:00:00')))
                                                1:00 PM
                                            @endif
                                        @endif
                                    @endif
                                </td>
                                <td class="py-2">
                                    {{ (date('H:i:s', strtotime($report['time_end'])) > date('H:i:s', strtotime('12:00:00'))) ? date('g:i A', strtotime($report['time_end'])) : '' }}
                                </td>
                                <td class="py-2">
                                    @if($report['no_out'])
                                        08:00:00
                                    @else
                                        {{ $report['tardy'] }}
                                    @endif
                                </td>
                                <td class="py-2">
                                    {{ $report['undertime'] }}
                                </td>
                                <td class="py-2">
                                    {{ $report['flexied'] ? 'Yes' : '' }}
                                </td>
                                <td class="py-2 text-center">
                                    {!! $report['remarks']->pluck('value')->implode(',') !!}
                                </td>
                            </tr>
                            @endforeach
                            <tr class="border-t-2 border-b-2 border-gray-600">
                                <td class="py-2 text-right font-semibold" colspan="5">Total Deducted Time</td>
                                <td class="py-2">
                                    {{ $this->dtrReport['total']['tardy'][1] }}
                                </td>
                                <td class="py-2">{{ $this->dtrReport['total']['undertime'][1] }}</td>
                            </tr>
                            <tr class="border-t-2 border-b-2 border-gray-600">
                                <td class="py-2 text-right font-semibold" colspan="5">Total Frequency</td>
                                <td class="py-2">{{ $this->dtrReport['total']['tardy'][0] }}</td>
                                <td class="py-2">{{ $this->dtrReport['total']['undertime'][0] }}</td>
                                <td class="py-2">{{ $this->dtrReport['total']['total_flexi'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
            <div class="bg-dap-secondary p-8 flex justify-between rounded-2xl shadow-lg">
                <div>
                    <div class="text-xl font-bold mb-2">Notice!!</div>
                    <div class=" font-semibold text-lg indent-5">
                        {{ $this->dtrReport['message'] }}
                    </div>
                </div>
                <div>
                    <img src="{{ asset('image/mochi/notice.png') }}" class="w-48" draggable="false">
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
