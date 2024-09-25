<?php

namespace App\Livewire\Employee\LeaveFlexiApplication;

use Carbon\Carbon;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Filament\Notifications\Notification;

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
        if ($this->eventCreate->format('Y-m-d') < now()->addDays(3)->format('Y-m-d')) {
            return Notification::make()
                ->warning()
                ->color('warning')
                ->title('Create Event error!')
                ->body('You can only request a schedule two (2) days after the current date')
                ->send();
        }


        $event = Event::where('hris_number', auth()->user()->hris_number)->whereDate('start', $this->eventCreate->format('Y-m-d'))->first();
        if ($event) {
            return Notification::make()
                ->warning()
                ->color('warning')
                ->title('Create Event has been cancelled!')
                ->body('You have already applied for ' . $event->tag->getLabel() . ' event on ' . $this->eventCreate->format('F d, Y') . '. If you want to change your request, you can just update your current request.')
                ->send();
        }

        $this->dispatch('open-modal', id: 'create-event');
    }
}
