<?php

namespace App\Livewire\HrAdmin\Events;

use Livewire\Component;
use Livewire\Attributes\Title;

class Index extends Component
{
    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.hr-admin.events.index');
    }
}
