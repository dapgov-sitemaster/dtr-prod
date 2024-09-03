<?php

namespace App\Livewire\Dapcc\AdminCoord\Events;

use Livewire\Component;
use Livewire\Attributes\Title;

class Index extends Component
{
    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.dapcc.admin-coord.events.index');
    }
}
