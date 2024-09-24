<div>
    <div class="border-2 rounded-xl p-6">
        <div class="lg:grid lg:grid-cols-7 lg:gap-1 text-center mb-2 hidden ">
            <div>Sunday</div>
            <div>Monday</div>
            <div>Tuesday</div>
            <div>Wednesday</div>
            <div>Thursday</div>
            <div>Friday</div>
            <div>Saturday</div>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center mb-2 lg:hidden">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>
        <div class="grid grid-cols-7">
            @foreach ($items as $item)
                @if ($item)
                    <div class="overflow-y-auto aspect-square border border-gray-400" @if($type == "pasig") wire:click="$parent.createEvent('{{ $item->day->format('Y-m-d') }}')" @endif>
                        <div class="pt-2 text-center font-semibold">
                            <div class="">
                                <span
                                @class([
                                    'text-gray-700',
                                    'text-red-400' => $item->day->isWeekend()
                                ])>
                                    {{ $item->day->day }}

                                    @if(count($item->events) > 0)
                                    {{-- <x-filament::button size="xs">
                                        New user

                                        <x-slot name="badge">
                                            3
                                        </x-slot>
                                    </x-filament::button> --}}
                                        @foreach($item->events as $event => $value)
                                        @php
                                            $event_enum = ($type == "pasig") ? App\Enums\Events::parse($event) : App\Enums\Dapcc\Events::parse($event);
                                        @endphp
                                        <div class="px-2 pb-1 lg:flex w-full text-xs relative inline-flex">
                                            <button
                                                x-data="{}"
                                                x-tooltip="{
                                                    content: @js($event_enum->getLabel()),
                                                }"
                                                wire:click.stop="$parent.viewEvent('{{ $event }}', '{{ $item->day->format('Y-m-d') }}')"
                                                class="w-full rounded-md text-left py-1 px-2 focus:shadow text-gray-700 focus:text-white truncate ... {{ $event_enum->customColor() }}">
                                                {{ $event_enum->getLabel() }}
                                            </button>
                                            @if($value->count() > 1)
                                                <div class="fi-btn-badge-ctn absolute start-full top-0 z-[1] w-max -translate-x-5 -translate-y-1/2 rounded-md bg-white dark:bg-gray-900 rtl:translate-x-1/2">
                                                    <x-filament::badge :color="$event_enum->getColor()" size="xs">
                                                        {{ $value->count() }}
                                                    </x-filament::badge>
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="aspect-square flex justify-center items-center bg-gray-100"></div>
                @endif
            @endforeach
        </div>
    </div>
</div>
