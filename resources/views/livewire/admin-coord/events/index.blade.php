<div>
    <x-loading wire:loading />
    <livewire:admin-coord.events.create-event key="create-event">

    <div class="w-full bg-white p-8 rounded-xl">
        <div class="flex justify-between">
            <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
                {{ auth()->user()->employee->department->center.'/'.auth()->user()->employee->department->office }} Event Calendar
            </div>
            <div>
                <x-filament::button outlined size="sm" icon="heroicon-m-question-mark-circle" wire:click.prevent="$dispatchTo('docs.calendar-tutorial', 'open-guide')">
                    Guide
                </x-filament::button>
            </div>
        </div>
        <div class="hidden md:block mt-6" x-data="{ activeTab: 'list' }">
            <x-filament::tabs label="Content tabs" class="w-64">
                <x-filament::tabs.item icon="heroicon-m-list-bullet" class="mx-auto"
                    alpine-active="activeTab === 'list'"
                    x-on:click="activeTab = 'list'">
                    List
                </x-filament::tabs.item>

                <x-filament::tabs.item icon="heroicon-m-calendar-days" class="mx-auto"
                    alpine-active="activeTab === 'calendar'"
                    x-on:click="activeTab = 'calendar'">
                    Calendar
                </x-filament::tabs.item>
            </x-filament::tabs>
            <div class="w-full my-4">
                <div class="block md:flex md:justify-between text-center mx-auto">
                    <div class="w-full mx-auto text-2xl font-semibold">
                        {{ Carbon\Carbon::create($selectedYear, $selectedMonth)->format('F Y') }}
                    </div>
                    <div class="w-full block md:flex md:justify-end mx-auto my-2 md:my-0">
                        <x-filament::input.wrapper class="mx-2 my-1 md:my-0">
                            <x-slot name="prefix">
                                Year
                            </x-slot>
                            <x-filament::input.select wire:model.live="selectedYear">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ 2022 + $i }}">{{ 2022 + $i }}</option>
                                @endfor
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
            <div x-show="activeTab === 'list'" x-transition class="my-4">
                <livewire:admin-coord.events.table-list :year="$selectedYear" :month="$selectedMonth" />
            </div>
            <div x-show="activeTab === 'calendar'" x-transition class="my-4">
                <livewire:admin-coord.events.calendar :year="$selectedYear" :month="$selectedMonth" />
            </div>
        </div>
        {{-- <div class="hidden md:block mt-6">
            @livewire(\App\Livewire\AdminCoord\Events\Calendar::class, [], key('event-calendar'))
        </div>
        <div class="md:hidden block mt-6">
            test
        </div> --}}
    </div>
    @livewire(\App\Livewire\Docs\CalendarTutorial::class, [], key(str()->random()))

    <x-filament::modal width="5xl" id="view-event">
        @if(!empty($eventView))
        <x-slot name="heading">
            View Event
        </x-slot>

        <livewire:admin-coord.events.view-event :events="$eventView" :key="str()->random()">
        @endif
    </x-filament::modal>
    <x-filament::modal
        id="changes-notice"
        width="3xl"
        icon="heroicon-o-exclamation-triangle"
        :close-button="false"
        :close-by-escaping="false"
        :close-by-clicking-away="false"
        sticky-header>
        <x-slot name="heading">
            Notice of Changes in Event Calendar
        </x-slot>
        <div>
            <div class="list-disc">
                <li>
                    <span class="font-bold">Changes in Uploading of MOV</span>
                    <div class="indent-8">Please be advised that we are suspending the <span class="font-semibold">Uploading of MOV</span> as per HR ComBen. The reason for this was that the MOVs are still need to be submitted in hard copy. You can still view the uploaded files for future references.</div>
                    <div class="indent-8">If you have any concerns regarding this changes, please contact our HR ComBen.</div>
                </li>
                <li>
                    <span class="font-bold">Calendar User Interface</span>
                    <div class="indent-8">We are reverting back the old Event Calendar. There are multiple reports that the last Event Calendar was too slow to reload when there are too many events was set. We also did improve the display when viewing an Event, and the creation of the Event.</div>
                    <div class="indent-8">If you have any concerns regarding this changes, please do fill up this link (<a href="https://forms.gle/4jdH3KnLu1HKnx6L8" target="_blank" class="underline text-blue-700">https://forms.gle/4jdH3KnLu1HKnx6L8</a>), for us to document the Bug/Error Report in the system. This will help us track the changes in the system. Thank you.</div>
                </li>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button outlined color="secondary" class="mx-2" onclick="dismissModal()">
                    Do not show this again
                </x-filament::button>
                <x-filament::button outlined color="gray" class="mx-2" x-on:click="$dispatch('close-modal', { id: 'changes-notice' })">
                    Close
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            if (!localStorage.getItem('dapccModalDismissed')) {
                Livewire.dispatch('open-modal', { id: 'changes-notice' });
            }
        })

        function dismissModal() {
            localStorage.setItem('dapccModalDismissed', 'true');
            Livewire.dispatch('close-modal', { id: 'changes-notice' });
        }
    </script>
@endpush
