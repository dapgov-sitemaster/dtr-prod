<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Time Entries
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

</div>
