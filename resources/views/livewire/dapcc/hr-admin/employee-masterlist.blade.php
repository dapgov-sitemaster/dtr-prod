<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Employee Masterlist
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

</div>
