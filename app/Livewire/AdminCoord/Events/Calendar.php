<?php

namespace App\Livewire\AdminCoord\Events;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class Calendar extends Component
{
    public $eventData = [];

    public $events = [];
    public $month;
    public $year;
    public $day;

    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {

        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        // $this->month = now()->format("m");
        // $this->year = now()->format("Y");
        // $this->day = now()->format("d");
    }

    public function render()
    {
        return view('livewire.admin-coord.events.calendar');
    }

    #[On('refresh-calendar')]
    #[Computed]
    public function days()
    {
        $days = collect();
        $firstDay = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $events = Event::query()
            ->whereHas('employee', fn($query) => $query->departmentCovered())
            ->whereDate('start', '>=', $firstDay->copy()->startOfMonth())
            ->whereDate('end', '<=', $firstDay->copy()->endOfMonth())
            ->orWhereIn('tag', [Events::HOL, Events::SUS, Events::FLAG])
            ->get();
        $dayOfWeek = $firstDay->dayOfWeek;
        for ($i = 0; $i <= $dayOfWeek - 1; $i++) {
            $days[] = null;
        }

        for ($i = 1; $i <= $firstDay->daysInMonth; $i++) {
            $day = Carbon::parse($this->selectedYear . '-' . $this->selectedMonth . '-' . $i);
            $dayEvent = $events->filter(function ($item) use ($day) {
                return $item->start->format('Y-m-d') == $day->format('Y-m-d');
            });
            $tags = $dayEvent->groupBy('tag')->keys();

            $days->push((object) ['day' => $day, 'tags' => $tags]);
        }

        return $days;
    }

    public function viewEvent($event, $date)
    {
        $this->eventData = [
            'tag' => $event,
            'date' => $date,
        ];
        // $this->dispatch('open-modal', id: 'view-event');
        $this->dispatch('viewing-event')->to(ViewEvent::class);
    }
}
