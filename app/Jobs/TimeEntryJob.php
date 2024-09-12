<?php

namespace App\Jobs;

use App\Models\Location;
use App\Models\TimeEntry;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TimeEntryJob implements ShouldQueue
{
    use Queueable;

    public $type, $data;

    /**
     * Create a new job instance.
     */
    public function __construct($type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
        // $this->hris_number = $hris_number;
        // $this->department_id = $department_id;
        // $this->timestart = $timestart;
        // $this->sched_type = $sched_type;
        // $this->official_time = $official_time;
        // $this->tag = $tag;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->type == 'new') {
            $time_entry = new TimeEntry();
            $time_entry->disableLogging();
            $time_entry->hris_number = $this->data['hris_number'];
            $time_entry->time_start = $this->data['timestart'];
            $time_entry->department_id = $this->data['department_id'];
            $time_entry->schedule_type = $this->data['sched_type'];
            $time_entry->official_time = $this->data['official_time'];
            $time_entry->tag = $this->data['tag'];
            $time_entry->timekeeper_id = $this->data['timekeeper_id'];
            $time_entry->save();
        } else if ($this->type == 'update') {
            $time_entry = $this->data['latest'];
            $time_entry->disableLogging();
            $time_entry->time_end = $this->data['time_end'];
            $time_entry->save();
        }

        if (array_key_exists('location', $this->data)) {
            $new_loc = new Location();
            $new_loc->time_entry_id = $time_entry->id;
            $new_loc->location = $this->data['location']['address'];
            $new_loc->coordinates = $this->data['location']['coordinates'];
            $new_loc->save();
        }
    }
}
