<div>
    <x-loading wire:loading />
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
        <div class="hidden md:block mt-6">
            @livewire(\App\Livewire\AdminCoord\Events\Calendar::class, [], key('event-calendar'))
            {{-- @livewire(\App\Livewire\AdminCoord\CalendarWidget::class, [], key(str()->random())) --}}
        </div>
        <div class="md:hidden block mt-6">
            test
        </div>
    </div>
    <livewire:admin-coord.events.create-event key="create-event">

    <x-filament::modal width="5xl" id="view-event">
        @if(!empty($eventView))
        <x-slot name="heading">
            View Event
        </x-slot>

        <livewire:admin-coord.events.view-event :events="$eventView" :key="str()->random()">
        @endif
    </x-filament::modal>
    @livewire(\App\Livewire\Docs\CalendarTutorial::class, [], key(str()->random()))

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
                    <div class="indent-8">We are reverting back the old Event Calendar. There are multiple reports that the last Event Calendar was too slow to reload. We also did improve the display when viewing an Event, and the creation of the Event.</div>
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
            if (!localStorage.getItem('modalDismissed')) {
                Livewire.dispatch('open-modal', { id: 'changes-notice' });
            }
        })

        function dismissModal() {
            localStorage.setItem('modalDismissed', 'true');
            Livewire.dispatch('close-modal', { id: 'changes-notice' });
        }
    </script>
@endpush
