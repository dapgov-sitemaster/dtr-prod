<?php

namespace App\Livewire\AdminCoord;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\Title;

class EventCalendar extends Component
{
    #[Title('| Event Calendar')]

    public function render()
    {
        return view('livewire.admin-coord.event-calendar');
    }
}
