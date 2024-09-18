<div>

    <div class="w-full mb-4">
        <div class="block md:flex md:justify-between text-center mx-auto">
            <div class="w-full mx-auto text-2xl font-semibold">
                {{ Carbon\Carbon::create($selectedYear, $selectedMonth)->format('F Y') }}
            </div>
            <div class="w-full block md:flex md:justify-end mx-auto my-2 md:my-0">
                <x-filament::input.wrapper class="mx-2 my-1 md:my-0">
                    <x-slot name="prefix">
                        Year
                    </x-slot>
                    <x-filament::input.select wire:model.live="selectedYear">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper class="mx-2 my-1 md:my-0">
                    <x-slot name="prefix">
                        Month
                    </x-slot>
                    <x-filament::input.select wire:model.live="selectedMonth">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ Carbon\Carbon::create(null, $i)->format('F') }}</option>
                        @endfor
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </div>
    <x-calendar type="pasig" :items="$this->days" />
    {{-- <div class="border-2 rounded-xl p-6">
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
            @foreach ($this->days as $item)
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
                                            wire:click.stop="$parent.viewEvent('{{ $event }}', '{{ $item->day->format('Y-m-d') }}')"
                                            class="w-full rounded-md text-left py-1 px-2 focus:shadow focus:border-1 text-gray-700 hover:bg-gray-100 focus:text-white @if($event == 'holiday') focus:bg-green-500 focus:border-green-800 @elseif($event == 'suspension') focus:bg-red-500 focus:border-red-800 @else focus:bg-blue-500 focus:border-blue-800 @endif">
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
    </div> --}}
</div>
