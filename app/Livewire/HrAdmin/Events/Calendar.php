<?php

namespace App\Livewire\HrAdmin\Events;

use Carbon\Carbon;
use App\Enums\Events;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

class Calendar extends Component
{
    public $eventData = [];

    public $events = [];
    public $month;
    public $year;
    public $day;

    #[Url(as: 'month', keep: true, history: true)]
    public $selectedMonth;
    #[Url(as: 'year', keep: true, history: true)]
    public $selectedYear;

    public function mount()
    {

        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function render()
    {
        return view('livewire.hr-admin.events.calendar');
    }

    #[On('refresh-calendar')]
    #[Computed]
    public function days()
    {
        $days = collect();
        $firstDay = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $events = Event::query()
            ->whereDate('start', '>=', $firstDay->copy()->startOfMonth())
            ->whereDate('end', '<=', $firstDay->copy()->endOfMonth())
            ->where('created_by', auth()->user()->hris_number)
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
            $tags = $dayEvent->groupBy('tag');

            $days->push((object) ['day' => $day, 'events' => $tags]);
        }

        return $days;
    }
}
