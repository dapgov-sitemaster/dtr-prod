<div>
    <x-loading wire:loading />
    <livewire:admin-coord.events.create-event key="create-event">

    <div class="w-full bg-white p-8 rounded-xl">
        <div class="flex justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                {{ auth()->user()->employee->department->center.'/'.auth()->user()->employee->department->office }} Event Calendar
            </div>
            <div>
                <x-filament::button outlined size="sm" icon="heroicon-m-question-mark-circle" wire:click.prevent="$dispatchTo('docs.calendar-tutorial', 'open-guide')">
                    Guide
                </x-filament::button>
            </div>
        </div>
        <div class="hidden md:block mt-6" x-data="{ activeTab: 'list' }">
            <x-filament::tabs label="Content tabs" class="w-64">
                <x-filament::tabs.item icon="heroicon-m-list-bullet" class="mx-auto"
                    alpine-active="activeTab === 'list'"
                    x-on:click="activeTab = 'list'">
                    List
                </x-filament::tabs.item>

                <x-filament::tabs.item icon="heroicon-m-calendar-days" class="mx-auto"
                    alpine-active="activeTab === 'calendar'"
                    x-on:click="activeTab = 'calendar'">
                    Calendar
                </x-filament::tabs.item>
            </x-filament::tabs>
            <div class="w-full my-4">
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
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ 2022 + $i }}">{{ 2022 + $i }}</option>
                                @endfor
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
            <div x-show="activeTab === 'list'" x-transition class="my-4">
                <livewire:admin-coord.events.table-list :year="$selectedYear" :month="$selectedMonth" />
            </div>
            <div x-show="activeTab === 'calendar'" x-transition class="my-4">
                <livewire:admin-coord.events.calendar :year="$selectedYear" :month="$selectedMonth" />
            </div>
        </div>
        <div class="md:hidden block mt-6">
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
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ 2022 + $i }}">{{ 2022 + $i }}</option>
                            @endfor
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
            <div class="my-4">
                <livewire:admin-coord.events.table-list :year="$selectedYear" :month="$selectedMonth" />
            </div>
        </div>
        {{-- <div class="hidden md:block mt-6">
            @livewire(\App\Livewire\AdminCoord\Events\Calendar::class, [], key('event-calendar'))
        </div>
        <div class="md:hidden block mt-6">
            test
        </div> --}}
    </div>
    @livewire(\App\Livewire\Docs\CalendarTutorial::class, [], key(str()->random()))

    <x-filament::modal width="5xl" id="view-event">
        @if(!empty($eventView))
        <x-slot name="heading">
            View Event
        </x-slot>

        <livewire:admin-coord.events.view-event :events="$eventView" :key="str()->random()">
        @endif
    </x-filament::modal>
</div>
