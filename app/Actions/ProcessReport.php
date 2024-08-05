<?php

namespace App\Actions;

use App\Models\Event;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;

class ProcessReport
{
    public function handle(Collection $dtr, $date_from, $date_to)
    {
        $dates = CarbonPeriod::create($date_from, $date_to)->toArray();
        $flag_sched = Event::query()
            ->where('tag', 'fc')
            ->whereBetween('time_start', [$date_from, $date_to])
            ->get();

        foreach ($dates as $date) {
            $time = clone $dtr->whereBetween('time_start', [$date->format('Y-m-d') . ' 00:00:00', $date->format('Y-m-d') . ' 23:59:59'])->values();
            $tardy = null;
            $undertime = null;
            $time_in = null;
            $time_end = null;
            $no_out = null;
            $graced = false;

            if ($time->isNotEmpty()) {
                $time_in = $time->first()->time_start;
                $time_end = $time->first()->time_end;
                $grace_period = 900;
            }
        }
        dd($dtr);
    }
}
