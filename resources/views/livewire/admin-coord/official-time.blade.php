<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="flex justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                {{ auth()->user()->employee->department->center.'/'.auth()->user()->employee->department->office }} Official Time
            </div>
            <div>
                <x-filament::button outlined size="sm" icon="heroicon-m-question-mark-circle" wire:click.prevent="$dispatchTo('docs.official-time-tutorial', 'open-official-time-guide')">
                    Guide
                </x-filament::button>
            </div>
        </div>
        @livewire(\App\Livewire\Docs\OfficialTimeTutorial::class, [], key(str()->random()))
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</div>
