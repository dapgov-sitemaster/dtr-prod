<?php

namespace App\Livewire\Employee;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use App\Models\TimeEntry;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Gate;

class WorkFromHome extends Component
{
    public Employee $employee;
    public $start;
    public $entries;
    public $current_timediff;
    // public $start = false;

    #[Title('| Work from Home')]
    public function mount()
    {
        // $this->start = Carbon::parse('2024-08-11 17:24:22');
        $this->employee = auth()->user()->employee;
    }

    public function render()
    {
        $this->entries = TimeEntry::where('hris_number', $this->employee->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->orderBy('created_at', 'DESC')->get();
        $this->start = ($this->entries->first()->time_end) ? null : $this->entries->first()->time_start;
        if ($this->entries->first()->time_end != null) {
            $this->current_timediff = $this->entries->first()->time_start->diff($this->entries->first()->time_end)->format('%H:%I:%s');
        }
        return view('livewire.employee.work-from-home');
    }

    public function time_capture()
    {
        if (Gate::allows('has-wfh-schedule')) {
            $current_time = now();
            $official_time = ($this->employee->official_time) ? $this->employee->official_time->time_in : date('H:i:s', strtotime('08:00:00'));
            $entries = TimeEntry::where('hris_number', $this->employee->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->orderBy('created_at', 'DESC')->get();

            if ($entries->isNotEmpty()) {
                if ($entries->first()->time_end == null) {
                    $entries->first()->time_end = $current_time;
                    $entries->first()->save();
                    return;
                }
            }

            TimeEntry::create([
                'hris_number' => $this->employee->hris_number,
                'time_start' => $current_time,
                'department_id' => $this->employee->department_id,
                'official_time' => $official_time,
                'tag' => 'wfh',
            ]);
        }
    }
}
