<?php

namespace App\Livewire\AdminCoord;

use Livewire\Component;
use Livewire\Attributes\Title;

class EventCalendar extends Component
{
    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.admin-coord.event-calendar');
    }
}
