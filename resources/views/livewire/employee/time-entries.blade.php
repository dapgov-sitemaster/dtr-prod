<div>
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        {{-- <div class="flex justify-between"> --}}
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                My Daily Time Entries
            </div>
            {{-- <div class="flex">
                <label class="mr-2 my-auto font-semibold">Year and Month: </label>
                <x-filament::input.wrapper :valid="! $errors->has('yearmonth')">
                    <x-filament::input
                        type="month"
                        wire:model.live="yearmonth"
                    />
                </x-filament::input.wrapper>
            </div>
        </div> --}}

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</div>
