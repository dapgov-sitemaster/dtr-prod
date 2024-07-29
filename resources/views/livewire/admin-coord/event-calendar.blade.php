<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            {{ auth()->user()->employee->department->description }} Event Calendar
        </div>
        <div class="mt-6">
            @livewire(\App\Livewire\CalendarWidget::class, [], key(str()->random()))
        </div>
    </div>
</div>
