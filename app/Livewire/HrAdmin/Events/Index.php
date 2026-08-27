<?php

namespace App\Livewire\HrAdmin\Events;

use Livewire\Component;
use Livewire\Attributes\Title;

class Index extends Component
{
    public $eventView;
    public $eventCreate;

    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.hr-admin.events.index');
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
        $this->dispatch(
            'creating-event',
            date: $date
        )->to(CreateEvent::class);
    }
}
