<?php

namespace App\Livewire\Dapcc\HrAdmin\Events;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\Title;

class Index extends Component
{
    #[Title('| Event Calendar')]
    public function render()
    {
        return view('livewire.dapcc.hr-admin.events.index');
    }
}
