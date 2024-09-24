<?php

namespace App\Livewire\Employee\LeaveFlexiApplication\Event;

use Livewire\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class CreateEvent extends Component implements HasForms
{
    use InteractsWithForms;
    public ?array $data = [];

    public $eventData;

    public function render()
    {
        return view('livewire.employee.leave-flexi-application.event.create-event');
    }
}
