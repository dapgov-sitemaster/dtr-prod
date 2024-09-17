<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Event Calendar
        </div>
        <div class="mt-6">
            {{-- @livewire(\App\Livewire\HrAdmin\Events\CalendarWidget::class, [], key(str()->random())) --}}
            @livewire(\App\Livewire\HrAdmin\Events\Calendar::class, [], key("event-calendar"))
        </div>
    </div>

    <livewire:hr-admin.events.create-event key="hr-create-event">
    <x-filament::modal width="5xl" id="view-event">
        @if(!empty($eventView))
        <x-slot name="heading">
            View Event
        </x-slot>

        <livewire:hr-admin.events.view-event :events="$eventView" key="hr-view-event">
        @endif
    </x-filament::modal>

</div>
