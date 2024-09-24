<div>
    {{-- <x-loading wire:loading /> --}}
    <x-filament::modal width="5xl" id="view-event">
        @if(!empty($eventView))
        <x-slot name="heading">
            View Event
        </x-slot>

        <livewire:employee.leave-flexi-application.event.view-event :events="$eventView" :key="str()->random()">
        @endif
    </x-filament::modal>
    <x-filament::modal width="xl" id="create-event">
        @if(!empty($eventCreate))
        <x-slot name="heading">
            Request Schedule for {{ $eventCreate->format('F d, Y') }}
        </x-slot>

        <livewire:employee.leave-flexi-application.event.create-event :date="$eventCreate" :key="str()->random()">

        <x-slot name="footer">
            <x-filament::button wire:click="$dispatchTo('employee.leave-flexi-application.event.create-event', 'event-create')">
                Submit
            </x-filament::button>
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'create-event' })" color="gray">
                Cancel
            </x-filament::button>
        </x-slot>
        @endif
    </x-filament::modal>
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="flex justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                Leave & Flexible Schedule Application
            </div>
            <div>
                {{-- <x-filament::button outlined size="sm" icon="heroicon-m-question-mark-circle" wire:click.prevent="$dispatchTo('docs.calendar-tutorial', 'open-guide')">
                    Guide
                </x-filament::button> --}}
            </div>
        </div>
        <div class="hidden md:block mt-6" x-data="{ activeTab: 'calendar' }">
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
                <livewire:employee.leave-flexi-application.table-list :year="$selectedYear" :month="$selectedMonth" />
            </div>
            <div x-show="activeTab === 'calendar'" x-transition class="my-4">
                <livewire:employee.leave-flexi-application.event.calendar :year="$selectedYear" :month="$selectedMonth" />
            </div>
        </div>
        <div class="md:hidden block mt-6">
            test
        </div>
    </div>
</div>
