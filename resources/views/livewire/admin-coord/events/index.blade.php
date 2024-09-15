<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        @livewire(\App\Livewire\Docs\CalendarTutorial::class, [], key(str()->random()))
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
        <div class="hidden md:block mt-6">
            @livewire(\App\Livewire\AdminCoord\Events\Calendar::class, [], key(str()->random()))
            {{-- @livewire(\App\Livewire\AdminCoord\CalendarWidget::class, [], key(str()->random())) --}}
        </div>
        <div class="md:hidden block mt-6">
            test
        </div>
        <livewire:admin-coord.events.create-event key="create-event">

        <x-filament::modal width="5xl" id="view-event">
            @if(!empty($eventView))
            <x-slot name="heading">
                View Event
            </x-slot>

            <livewire:admin-coord.events.view-event :events="$eventView" :key="str()->random()">
            @endif
        </x-filament::modal>
    </div>
</div>
