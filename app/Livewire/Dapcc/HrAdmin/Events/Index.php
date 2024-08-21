<?php

namespace App\Livewire\Dapcc\HrAdmin\Events;

use Livewire\Component;

class Index extends Component
{
    // public function mount()
    // {
    //     dd(\App\Models\Dapcc\Event::all()->first()->employee);
    // }
    public function render()
    {
        return view('livewire.dapcc.hr-admin.events.index');
    }
}
