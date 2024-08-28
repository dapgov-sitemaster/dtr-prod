<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
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
        <div class="mt-6">
            @livewire(\App\Livewire\Docs\CalendarTutorial::class, [], key(str()->random()))
            @livewire(\App\Livewire\AdminCoord\CalendarWidget::class, [], key(str()->random()))
        </div>
    </div>
</div>
