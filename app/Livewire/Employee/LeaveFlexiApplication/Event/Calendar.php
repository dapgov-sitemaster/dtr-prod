<?php

namespace App\Livewire\Employee\LeaveFlexiApplication\Event;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

class Calendar extends Component
{
    #[Reactive]
    public $year, $month;

    public function mount($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function render()
    {
        return view('livewire.employee.leave-flexi-application.event.calendar');
    }

    #[On('refresh-calendar')]
    #[Computed]
    public function days()
    {
        $days = collect();
        $firstDay = Carbon::create($this->year, $this->month, 1);
        $events = Event::query()
            ->whereHas('employee', fn($query) => $query->departmentCovered())
            ->when(auth()->user()->employee->department->office == "ICTD", fn($query) => $query->whereNotIn('hris_number', ['212469', '210798']))
            ->whereDate('start', '>=', $firstDay->copy()->startOfMonth())
            ->whereDate('end', '<=', $firstDay->copy()->endOfMonth())
            // ->orWhereIn('tag', [Events::HOL, Events::SUS, Events::FLAG])
            ->get();

        $dayOfWeek = $firstDay->dayOfWeek;
        for ($i = 0; $i <= $dayOfWeek - 1; $i++) {
            $days[] = null;
        }

        for ($i = 1; $i <= $firstDay->daysInMonth; $i++) {
            $day = Carbon::parse($this->year . '-' . $this->month . '-' . $i);
            $dayEvent = $events->filter(function ($item) use ($day) {
                return $item->start->format('Y-m-d') == $day->format('Y-m-d');
            });
            $tags = $dayEvent->groupBy('tag');

            $days->push((object) ['day' => $day, 'events' => $tags]);
        }

        return $days;
    }
}
