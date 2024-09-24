<?php

namespace App\Livewire\Employee\LeaveFlexiApplication;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;

class Index extends Component
{
    public $activeTab = 'list';
    public $eventView;
    public $eventCreate;

    #[Url(as: 'month', keep: true, history: true)]
    public $selectedMonth;
    #[Url(as: 'year', keep: true, history: true)]
    public $selectedYear;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    #[Title('| Leave & Flexible Schedule Application')]
    public function render()
    {
        return view('livewire.employee.leave-flexi-application.index');
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
        $this->eventCreate = Carbon::parse($date);
        $this->dispatch('open-modal', id: 'create-event');
    }
}
