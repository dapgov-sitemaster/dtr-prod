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
                    <div class="overflow-y-auto aspect-square border border-gray-400" wire:click="$parent.createEvent('{{ $item->day->format('Y-m-d') }}')">
                        <div class="pt-2 text-center font-semibold">
                            <div class="">
                                <span
                                @class([
                                    'text-gray-700',
                                    'text-red-400' => $item->day->isWeekend()
                                ])>
                                    {{ $item->day->day }}

                                    @if($item->tags)
                                        @foreach($item->tags as $event)
                                        <div class="px-2 pb-1 lg:flex hidden w-full text-xs"
                                            >
                                            <button
                                            x-data="{ tooltip: {} }"
                                            x-init="
                                                Alpine.effect(() => {
                                                    tooltip = {
                                                        content: '{{ App\Enums\Events::parse($event)->getLabel() }}',
                                                        placement: 'top'
                                                    }
                                                })
                                            "
                                            x-tooltip.html="tooltip"
                                            wire:click.stop="$parent.viewEvent('{{ $event }}', '{{ $item->day->format('Y-m-d') }}')"
                                            class="w-full rounded-md text-left py-1 px-2 focus:shadow focus:border-1 text-gray-700 hover:bg-gray-100 focus:text-white truncate ... {{ App\Enums\Events::parse($event)->customColor() }}"
                                            >
                                                {{ App\Enums\Events::parse($event)->getLabel() }}
                                            </button>
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
