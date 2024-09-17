<div>
    <x-filament::modal width="5xl" id="create-event">
        @if(!empty($eventData))

            <x-slot name="heading">
                Create Event for {{ $eventData->date->format('F d, Y') }}
            </x-slot>
            <div>
                {{ $this->form }}
            </div>
            <x-slot name="footer">
                <x-filament::button wire:click="create">
                    Submit
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'create-event' })" color="gray">
                    Cancel
                </x-filament::button>
            </x-slot>
        @endif
    </x-filament::modal>
</div>
