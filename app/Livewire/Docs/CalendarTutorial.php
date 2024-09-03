<?php

namespace App\Livewire\Docs;

use Livewire\Component;
use Livewire\Attributes\On;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class CalendarTutorial extends Component
{
    // use InteractsWithActions, InteractsWithForms;
    public function mount() {}

    public function render()
    {
        return view('livewire.docs.calendar-tutorial');
    }

    #[On('open-guide')]
    public function openModal()
    {
        $this->dispatch('open-modal', id: 'calendar-tut');
    }
}
