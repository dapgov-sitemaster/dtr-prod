<div>
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="block lg:flex lg:justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                Work from Home
            </div>
            <div>
                <div class="lg:my-0 my-2">
                    @if($employee->official_time)
                        <span>Your Official Time: <strong>{{ $employee->official_time->time_in->format('g:i A'). ' - ' .$employee->official_time->time_in->copy()->addHours(9)->format('g:i A') }}</strong></span>
                    @else
                        <span>Your Official Time: <strong>8:00 AM - 5:00 PM</strong></span>
                    @endif
                </div>
            </div>
        </div>


        <div class="w-auto my-6 flex justify-center text-center">
            <div class="relative">
                <div class="flex justify-center ">
                    <div class="">
                        <div class="text-lg">{{ now()->format('F d, Y g:i A') }}</div>
                        <div class="mt-4">{{ (($entries->count() % 2) == 0) ? 'Time Rendered for Today' : 'Current Time Rendered' }}</div>
                        <div class="lg:text-9xl text-4xl">
                            {{-- @if(!$start)
                                <span>00:00:00</span>
                            @else
                                @if($entries->first()->time_end != null)
                                <span>{{ $current_timediff }}</span>
                                @else
                                    <div x-data="{
                                            endDate: new Date('{{ $start }}').getTime(),
                                            remainingTime: 0,
                                            formatTime(time) {
                                                const hours = Math.floor((time % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
                                                const seconds = Math.floor((time % (1000 * 60)) / 1000);
                                                return { hours, minutes, seconds };
                                            }
                                        }"
                                        x-init="() => {
                                            setInterval(() => {
                                                const now = new Date().getTime();
                                                const remainingTime = now - endDate;
                                                console.log(remainingTime);
                                                $data.remainingTime = remainingTime > 0 ? remainingTime : 0;
                                            }, 1000);
                                        }"
                                        class="flex">
                                        <div
                                            x-text="formatTime(remainingTime).hours">
                                        </div>
                                        <div>:</div>
                                        <div
                                            x-text="formatTime(remainingTime).minutes">
                                        </div>
                                        <div>:</div>
                                        <div
                                            x-text="formatTime(remainingTime).seconds">
                                        </div>
                                    </div>
                                @endif
                            @endif --}}
                            @if($entries->first()->time_end != null)
                                <span>{{ $current_timediff }}</span>
                                @else
                                    <div x-data="{
                                            endDate: new Date('{{ $start }}').getTime(),
                                            remainingTime: 0,
                                            formatTime(time) {
                                                const hours = Math.floor((time % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
                                                const seconds = Math.floor((time % (1000 * 60)) / 1000);
                                                return { hours, minutes, seconds };
                                            }
                                        }"
                                        x-init="() => {
                                            setInterval(() => {
                                                const now = new Date().getTime();
                                                const remainingTime = now - endDate;
                                                console.log(remainingTime);
                                                $data.remainingTime = remainingTime > 0 ? remainingTime : 0;
                                            }, 1000);
                                        }"
                                        class="flex justify-center">
                                        <div
                                            x-text="formatTime(remainingTime).hours">
                                        </div>
                                        <div>:</div>
                                        <div
                                            x-text="formatTime(remainingTime).minutes">
                                        </div>
                                        <div>:</div>
                                        <div
                                            x-text="formatTime(remainingTime).seconds">
                                        </div>
                                    </div>
                                @endif
                        </div>
                        <div class="flex justify-between  mx-14">
                            <div class="mx-4">Hours</div><div class="mx-4">Minutes</div><div class="mx-4">Seconds</div>
                        </div>
                        <div class="my-4 lg:text-3xl text-lg">
                            @if($entries->count() > 0)
                                <div>
                                    <span class="font-semibold">Time Captures: </span> <br/>
                                    {{-- @php
                                        $temp = null;
                                        $ctr = 0;
                                    @endphp --}}
                                    @foreach($entries as $entry)
                                        {{-- @php
                                            $ctr++;
                                            $display = null;
                                            if(($ctr % 2) == 0) {
                                                $diff = Carbon\Carbon::parse($temp)->diff(Carbon\Carbon::parse($te->time_start))->format('%H:%I:%S');
                                                $display = date('g:i A', strtotime($temp)). ' - ' .date('g:i A', strtotime($te->time_start)). ' = '.$diff;
                                            }
                                            else {
                                                $temp = $te->time_start;

                                                if($ctr == count($time_entries)) {
                                                    $display = date('g:i A', strtotime($te->time_start)). ' - ';
                                                }
                                            }

                                        @endphp --}}
                                        <div>
                                            {{ $entry->time_start->format('g:i A') }} -
                                            @if ($entry->time_end)
                                                {{ $entry->time_end->format('g:i A') }} = {{ $entry->time_start->diff($entry->time_end)->format('%H:%I:%S') }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        {{-- <div>{{ $entry->time_start->format('g:i A') . ' - ' . (($entry->time_end) ? $entry->time_end->format('g:i A') : 'N/A') }}</div> --}}
                                    @endforeach

                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="w-52 h-48 items-center absolute lg:-right-36 -right-24">
                        <div class="lg:block hidden">
                            <img src="{{ asset('image/mochi/mochi.png') }}" class="w-52 m-auto" draggable="false">
                        </div>
                        <div class="absolute top-0 right-14 mt-8 h-48">
                            <img src="{{ asset('image/mochi/clock.png') }}" class="w-8 m-auto {{($start) ? 'spin-clock-logo' : ''}}" draggable="false">
                        </div>
                    </div>
                </div>


                <div class="my-4">
                    <x-button wire:click="time_capture" disabled="{{ ($entries->first()->time_end == null) ? 'true' : 'false' }}" color="primary" class="py-1 px-5 mx-1">
                        Start
                    </x-button>
                    <x-button wire:click="time_capture" disabled="{{ ($entries->first()->time_end != null) ? 'true' : 'false' }}" color="primary" class="py-1 px-5 mx-1">
                        Stop
                    </x-button>
                </div>

            </div>
        </div>
        {{-- <div class="mt-6" wire:poll.1s>
            {{ $start }}
        </div> --}}
        {{-- <div x-data="{
            startTime: new Date('{{ $start }}').getTime(),
            endDate: new Date('{{ $start }}').getTime(),
            remainingTime: 0,
            formatTime(time) {
                const hours = Math.floor((time % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((time % (1000 * 60)) / 1000);
                return { hours, minutes, seconds };
            }
        }"
        x-init="() => {
            setInterval(() => {
                const now = new Date().getTime();
                const remainingTime = now - endDate;
                console.log(remainingTime);
                $data.remainingTime = remainingTime > 0 ? remainingTime : 0;
            }, 1000);
        }"
        >
            <div>
                <div
                    x-text="formatTime(remainingTime).hours">
                </div>
                <div>Hours</div>
            </div>
            <div>
                <div
                    x-text="formatTime(remainingTime).minutes">
                </div>
                <div>Minutes</div>
            </div>
            <div>
                <div
                    x-text="formatTime(remainingTime).seconds">
                </div>
                <div>Seconds</div>
            </div>
        </div> --}}
    </div>
</div>
