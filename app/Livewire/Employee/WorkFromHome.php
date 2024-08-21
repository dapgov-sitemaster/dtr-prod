<?php

namespace App\Livewire\Employee;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Jobs\TimeEntryJob;
use App\Enums\ScheduleType;
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
            // $official_time = ($this->employee->official_time) ? $this->employee->official_time->time_in : date('H:i:s', strtotime('08:00:00'));
            $entries = TimeEntry::where('hris_number', $this->employee->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->orderBy('created_at', 'DESC')->get();

            $official_time = null;
            $sched_type = ($this->employee->official_time) ? $this->employee->official_time->schedule_type : ScheduleType::FULLFLEXI;
            if ($sched_type == ScheduleType::FIXED) {
                $official_time = $this->employee->official_time->time_in->format('H:i:s');
            }

            if ($entries->isNotEmpty()) {
                if ($entries->first()->time_end == null) {
                    // TimeEntryJob::dispatch(type: 'update', data: [
                    //     'latest' => $entries->first(),
                    //     'time_end' => $current_time,
                    // ]);
                    $entries->first()->time_end = $current_time;
                    $entries->first()->save();
                    // $this->dispatch('refresh-page');
                    return;
                }
            }

            // TimeEntryJob::dispatch(type: 'new', data: [
            //     'hris_number' => $this->employee->hris_number,
            //     'timestart' => $current_time,
            //     'department_id' => $this->employee->department_id,
            //     'sched_type' => $sched_type,
            //     'official_time' => $official_time,
            //     'tag' => 'wfh',
            //     'timekeeper_id' => null
            // ]);
            // $this->dispatch('refresh-page');
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
