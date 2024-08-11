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
@script
    <script>
        $wire.on('redirectToDtrReport', (data) => {
            let url;
            if(data.dtrtype == 'employee') {
                url = `daily-time-records/${data.dtrtype}/${data.hris_number}/dtr-report?yearmonth=${data.yearmonth}&cutoff=${data.cutoff}`;
            }
            else if(data.dtrtype == 'bulk')  {
                url = `daily-time-records/${data.dtrtype}/${data.office_id}/dtr-report?yearmonth=${data.yearmonth}&cutoff=${data.cutoff}&appointment_status=${data.appointment_status}`;

            }
            window.open(url, '_blank');
        });
    </script>
@endscript
