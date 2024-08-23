<?php

namespace App\Livewire\Dapcc\HrAdmin\Events;

use Livewire\Component;
use Livewire\Attributes\Title;

class Index extends Component
{
    // public function mount()
    // {
    //     dd(\App\Models\Dapcc\Event::all()->first()->employee);
    // }
    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.dapcc.hr-admin.events.index');
    }
}
