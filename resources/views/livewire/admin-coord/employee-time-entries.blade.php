<div>
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            {{ $employee->first_name }} | Time Entries
        </div>
        <div class="my-2">
            <x-filament::breadcrumbs :breadcrumbs="[
                '/admin/daily-time-records' => 'Daily Time Records',
                '#' => 'Time Entries',
            ]" />
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</div>
