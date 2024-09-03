<?php

namespace App\Livewire\Docs;

use Livewire\Component;
use Livewire\Attributes\On;

class OfficialTimeTutorial extends Component
{
    public function render()
    {
        return view('livewire.docs.official-time-tutorial');
    }

    #[On('open-official-time-guide')]
    public function openModal()
    {
        $this->dispatch('open-modal', id: 'official-time-tut');
    }
}
