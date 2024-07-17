<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Filament\Notifications\Notification;

class Home extends Component
{
    public $now;
    public $greetings;

    #[Title('| Home')]
    public function render()
    {
        $this->now = now();
        $user = auth()->user();
        $t = $this->now->format('a');

        switch ($t) {
            case 'am':
                $this->greetings = 'Good Morning, ' . $user->employee->first_name;
                break;
            case 'pm' && $this->now->hour < 18:
                $this->greetings = 'Good Afternoon, ' . $user->employee->first_name;
                break;
            default:
                $this->greetings = 'Good Evening, ' . $user->employee->first_name;
                break;
        }

        return view('livewire.home');
    }
}
