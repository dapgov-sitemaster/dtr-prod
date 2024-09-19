<div>
    <x-filament::modal width="4xl" id="create-event" icon="heroicon-m-plus-circle">
        <x-slot name="heading">
            Create Event
        </x-slot>
        <div>
            {{ $this->form }}
        </div>
        <x-slot name="footer" class="flex">
            <x-filament::button wire:click="create">
                Submit
            </x-filament::button>
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'create-event' })" color="gray">
                Cancel
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
