<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Generate DTR Report
        </div>
        <div class="mt-6 px-32">
            {{-- {{ $this->table }} --}}
            <div x-data="{ tab: 'employeeTab' }">
                <div>
                    <x-filament::tabs label="Content tabs">
                        <x-filament::tabs.item icon="heroicon-m-user" @click="tab = 'employeeTab'" :alpine-active="'tab === \'employeeTab\''">
                            Employee
                        </x-filament::tabs.item>

                        <x-filament::tabs.item icon="heroicon-m-user-group" @click="tab = 'officeTab'" :alpine-active="'tab === \'officeTab\''">
                            Office/Division
                        </x-filament::tabs.item>
                    </x-filament::tabs>
                </div>
                <div class="mt-4 p-2">
                    <div x-show="tab === 'employeeTab'">
                        <form wire:submit.prevent="generate">
                            {{ $this->individualForm }}
                        </form>
                    </div>
                    <div x-show="tab === 'officeTab'">
                        <form wire:submit.prevent="generate">
                            {{ $this->bulkForm }}
                        </form>
                    </div>
                </div>
            </div>
            <x-filament-actions::modals />
        </div>
    </div>
</div>
@script
    <script>
        $wire.on('redirectToDtrReport', (data) => {
            let url;
            if(data.dtrtype == 'employee') {
                url = `dtr-report/${data.dtrtype}/${data.hris_number}/dtr-report?yearmonth=${data.yearmonth}&cutoff=${data.cutoff}&week=${data.week}`;
            }
            else if(data.dtrtype == 'bulk')  {
                url = `dtr-report/${data.dtrtype}/${data.office_id}/dtr-report?yearmonth=${data.yearmonth}&cutoff=${data.cutoff}&appointment_status=${data.appointment_status}&week=${data.week}`;
            }
            window.open(url, '_blank');
        });
    </script>
@endscript
