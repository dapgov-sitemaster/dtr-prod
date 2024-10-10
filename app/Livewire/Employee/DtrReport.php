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
use Livewire\Attributes\Computed;
use App\Actions\ProcessDapccReport;
use Filament\Notifications\Notification;

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
        $date_to = Carbon::parse($range['date_to']);

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

            $this->showDtr = true;
            if ($this->employee->department->center == "DAPCC") {
                $process = new ProcessDapccReport;
                $processed = $process->handle($date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $this->employee, $events);
            } else {
                $generate = new GenerateReport;
                $process = new ProcessReport;
                $dtr_report = $generate->handle($this->employee, $date_from->format('Y-m-d'), $date_to->copy()->format('Y-m-d'));
                $processed = $process->handle($this->employee, $dtr_report, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $events);
            }
            // dd($processed);
            // info($this->showDtr);
            return $processed;
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

    public function printDtrReport()
    {
        if ($this->showDtr) {
            $hris_number = auth()->user()->hris_number;
            $yearmonth = $this->yearmonth;
            $cutoff = $this->cutoff;

            $this->dispatch('redirectToDtrReport', hris_number: $hris_number, yearmonth: $yearmonth, cutoff: $cutoff);
        } else {
            Notification::make()
                ->title("Unable to print DTR Report!")
                ->body("Selected Year, Month, and Cutoff are not yet ready! Please select other year, month and cutoff!")
                ->warning()
                ->color('warning')
                ->send();
        }
    }
}
