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

    public function createEvent($date)
    {

        // $this->eventCreate = (object) [
        //     'date' => Carbon::parse($date),
        // ];
        $this->dispatch(
            'creating-event',
            date: $date
        )->to(CreateEvent::class);
    }
}
