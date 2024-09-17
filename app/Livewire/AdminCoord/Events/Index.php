<?php

namespace App\Livewire\AdminCoord\Events;

use App\Enums\Events;
use App\Models\Event;
use Carbon\Carbon;
use Livewire\Component;

class Index extends Component
{
    public $eventView;
    public $eventCreate;

    public function mount()
    {
        $this->dispatch('open-modal', id: 'changes-notice');
    }

    public function render()
    {
        return view('livewire.admin-coord.events.index');
    }

    public function viewEvent($tag, $date)
    {
        $this->eventView = (object) [
            'tag' => $tag,
            'date' => $date,
        ];
        $this->dispatch('open-modal', id: 'view-event');
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
