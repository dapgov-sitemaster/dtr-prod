<?php

namespace App\Livewire\AdminCoord;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\Title;

class EventCalendar extends Component
{
    public $departments;
    #[Title('| Event Calendar')]
    public function mount()
    {
        if (auth()->user()->role == Role::CENTERADMINCOORD) {
            $this->departments = Department::where('center', auth()->user()->employee->department->center)->get()->pluck('id')->toArray();
        } else {
            $this->departments = [auth()->user()->employee->department_id];
        }
    }

    public function render()
    {
        return view('livewire.admin-coord.event-calendar');
    }
}
