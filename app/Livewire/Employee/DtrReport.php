<?php

namespace App\Livewire\Employee;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Report;
use Livewire\Component;
use App\Models\Employee;
use App\Actions\ProcessReport;
use Livewire\Attributes\Title;
use App\Actions\GenerateReport;
use App\Actions\ProcessDapccReport;
use Livewire\Attributes\Computed;

class DtrReport extends Component
{
    public $employee;
    public $yearmonth;
    public $cutoff;

    public $showDtr = false;

    #[Title('| My DTR Report')]
    public function mount()
    {
        $this->yearmonth = now()->format('Y-m');
        $this->cutoff = 1;
        $this->employee = Employee::with('official_time')->where('hris_number', auth()->user()->hris_number)->first();
    }

    public function render()
    {
        $this->dtrReport();
        return view('livewire.employee.dtr-report');
    }

    #[Computed]
    public function dtrReport()
    {
        $appointment_status = $this->employee->appointment_status->value;
        $yearmonth = $this->yearmonth;
        $cutoff = $this->cutoff;
        $date_from = null;
        $date_to = null;

        $range = $this->date_range($appointment_status, $cutoff, $yearmonth);

        $date_from = Carbon::parse($range['date_from']);
        $date_to = Carbon::parse($range['date_to'])->addDay();

        if ($date_to < now()) {
            if ($this->employee->department->center == "DAPCC") {
                $events = Event::query()
                    ->select('id', 'tag', 'start', 'hris_number')
                    ->where('hris_number', $this->employee->hris_number)
                    ->where('tag', 'LIKE', 'dapcc_%')
                    ->whereBetween('start', [$date_from, $date_to])
                    ->get();
            } else {
                $events = Event::query()
                    ->select('id', 'tag', 'start', 'hris_number')
                    ->where('hris_number', $this->employee->hris_number)
                    ->orWhere('hris_number', NULL)
                    ->whereBetween('start', [$date_from, $date_to])
                    ->get();
            }

            // $dtr_report = Report::query()
            //     ->where('hris_number', $this->employee->hris_number)
            //     ->whereBetween('time_start', [$date_from, $date_to])
            //     ->get();
            $generate = new GenerateReport;
            $dtr_report = $generate->handle($this->employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));

            if ($dtr_report->isNotEmpty()) {
                $this->showDtr = true;
                if ($this->employee->department->center == "DAPCC") {
                    $process = new ProcessDapccReport;
                    $processed = $process->handle($date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $this->employee, $events);
                } else {
                    $process = new ProcessReport;
                    $processed = $process->handle($this->employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $events);
                }
                // dd($processed);
                // info($this->showDtr);
                return $processed;
            }
        }


        $this->showDtr = false;
        return ["message" => "DTR Report for " . Carbon::parse($yearmonth)->format('F Y') . " " . (($cutoff == 1) ? 'First Cut-off' : 'Second Cut-off') . " are not yet Generated. Please wait for the Admin Coordinator to generate the DTR Report."];
    }

    private function date_range($appointment_status, $cutoff, $yearmonth): array
    {
        $date_from = null;
        $date_to = null;

        if ($appointment_status == 'pbp') {
            $date_from = ($cutoff == 1) ? $yearmonth . '-01' : $yearmonth . '-16';
            $date_to = ($cutoff == 1) ? $yearmonth . '-15' : $yearmonth . '-' . date('t', strtotime($yearmonth . '-' . '01'));
        } else if ($appointment_status == 'npp') {
            $yearmonth_ex = explode('-', $yearmonth);
            $prevMonth = (int) $yearmonth_ex[1] - 1;

            $date_from = ($cutoff == 1) ? $yearmonth_ex[0] . '-' . $prevMonth . '-26' : $yearmonth . '-11';
            $date_to = ($cutoff == 1) ? $yearmonth . '-10' : $yearmonth . '-25';
        }

        return ['date_from' => $date_from, 'date_to' => $date_to];
    }
}
