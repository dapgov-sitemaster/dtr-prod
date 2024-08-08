<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Generate DTR Report
        </div>
        <div class="mt-6">
            {{-- {{ $this->table }} --}}
            <form wire:submit.prevent="generate">
                {{ $this->individualForm }}
            </form>
            <x-filament-actions::modals />
        </div>
    </div>
</div>
