<div>
    <div class="w-full mb-4">
        <div class="block md:flex md:justify-between text-center mx-auto">
            <div class="w-full text-left">
                <x-filament::button icon="heroicon-m-plus-circle" wire:click.prevent="$parent.createEvent">
                    Create Event
                </x-filament::button>
            </div>
            <div class="w-full mx-auto text-2xl font-semibold">
                {{ Carbon\Carbon::create($selectedYear, $selectedMonth)->format('F Y') }}
            </div>
            <div class="w-full block md:flex md:justify-end mx-auto my-2 md:my-0">
                <x-filament::input.wrapper class="mx-2 my-1 md:my-0">
                    <x-slot name="prefix">
                        Year
                    </x-slot>
                    <x-filament::input.select wire:model.live="selectedYear">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper class="mx-2 my-1 md:my-0">
                    <x-slot name="prefix">
                        Month
                    </x-slot>
                    <x-filament::input.select wire:model.live="selectedMonth">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ Carbon\Carbon::create(null, $i)->format('F') }}</option>
                        @endfor
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </div>
    <x-calendar type="dapcc" :items="$this->days" />
</div>
