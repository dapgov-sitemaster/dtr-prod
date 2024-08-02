<div>
    <div class="w-full bg-white p-8 rounded-xl">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            {{ auth()->user()->employee->department->center.'/'.auth()->user()->employee->department->office }} Daily Time Records
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</div>
