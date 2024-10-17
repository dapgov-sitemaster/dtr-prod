<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;

class Index extends Component
{
    public $eventView;
    public $eventCreate;

    // #[Url(as: 'month', keep: true, history: true)]
    // public $selectedMonth;
    // #[Url(as: 'year', keep: true, history: true)]
    // public $selectedYear;

    public function mount()
    {
        // $this->selectedMonth = now()->month;
        // $this->selectedYear = now()->year;
        $this->dispatch('open-modal', id: 'changes-notice');
    }

    #[Title('| Event Calendar')]
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
