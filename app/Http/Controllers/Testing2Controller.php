<?php

namespace App\Http\Controllers;

use App\Actions\Azure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TimeEntry;
use App\Models\Employee;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Testing2Controller extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $schedules = json_decode(Storage::disk('local')->get('/testo/lgdo_events.json'), true);
        $datas = [];
        foreach ($schedules as $schedule) {
            $event = Event::with('mov')->where('hris_number', $schedule['hris_number'])->where('start', $schedule['datetime_start'])->where('tag', $schedule['tag'])->first();
            if ($event) {
                if ($event->mov == null) {
                    if (!array_key_exists($event->id, $datas)) {
                        $datas[$event->id] = [
                            'filename' => $schedule['mov'],
                            'movable_type' => 'App\Models\Event',
                            'movable_id' => $event->id,
                        ];
                    }
                    info('success uploaded mov: ' . $event->id);
                } else {
                    info('already have mov: ' . $event->id);
                }
            }
        }
        // dd($data);

        // DB::table('movs')->insert($data[207]);
        // return $data . " done!";
        foreach ($datas as $data) {
            try {
                // DB::table('time_entries')->insert()
                // DB::table('time_entries')->upsert($d
                //     ->toArray(), ['hris_number', 'time_start', 'time_end']);
                DB::table('movs')->insert($data);
            } catch (\Illuminate\Database\QueryException $e) {
                $error = $e->getMessage();
                echo $error;
            }
        }
        return "done!";

        // $events = Event::whereHas('employee', function ($query) {
        //     return $query->where('department_id', 16);
        // })->where('tag', '<>', 'wfh')
        // ->where('start', '>=', '2024-06-03')
        // ->where('start', '<=', '2024-08-30')
        // ->chunk(50, function($events) {
        //     foreach($events as $event) {

        //     }
        // });

        // $events = Event::where('created_at', '>', '2024-08-27 17:44:03')->orderBy('created_at')->limit(10)->get();
        // $delete = [];
        // $temp = null;

        // dd($events);
        // for ($i = 0; $i < count($events); $i++) {
        //     if ($i == 0) {
        //         $temp = $events[$i];
        //     } else {
        //         if ($events[$i]->hris_number == $temp->hris_number) {
        //             if ($events[$i]->tag == $temp->tag) {
        //                 dd($events[$i]->created_at);
        //                 if ($events[$i]->created_at == $temp->created_at) {
        //                     dd($temp->created_at);
        //                 }
        //             }
        //         }
        //     }
        // }
        // dd($events);

        // $id = 23731;
        // $num = 0;

        // $entries = json_decode(Storage::disk('local')->get('/213262-timentries-9.json'), true);
        // // $old = json_decode(Storage::disk('local')->get('/time_entries_new/time_entries_oldies/timentries-' . $f . '.json'), true);

        // $time_entries = [];
        // for ($i = 0; $i < count($entries); $i++) {
        //     // $entry = TimeEntry::where('hris_number', $entries[$i]['hris_number'])->whereDate('time_start', Carbon::parse($entries[$i]['time_start'])->format('Y-m-d'))->first();
        //     // if (!$entry) {
        //     $time_entries[] = [
        //         // 'id' => $id,
        //         'hris_number' => $entries[$i]['hris_number'],
        //         'time_start' => $entries[$i]['time_start'],
        //         'time_end' => ($entries[$i]['time_end']) ? $entries[$i]['time_end'] : null,
        //         'department_id' => $entries[$i]['office_id'],
        //         'schedule_type' => $entries[$i]['schedule_type'],
        //         'official_time' => $entries[$i]['official_time'],
        //         'timekeeper_id' => 1,
        //         'tag' => strtolower($entries[$i]['tag']),
        //         'created_at' => Carbon::parse($entries[$i]['created_at'])->format('Y-m-d H:i:s'),
        //         'updated_at' => Carbon::parse($entries[$i]['updated_at'])->format('Y-m-d H:i:s'),
        //     ];
        //     // }
        // }

        // $insertion_data = collect($time_entries);
        // $data = $insertion_data->chunk(100);
        // foreach ($data as $key => $d) {
        //     try {
        //         // DB::table('time_entries')->insert()
        //         // DB::table('time_entries')->upsert($d
        //         //     ->toArray(), ['hris_number', 'time_start', 'time_end']);
        //         DB::table('time_entries')->insert($d
        //             ->toArray());
        //     } catch (\Illuminate\Database\QueryException $e) {
        //         $error = $e->getMessage();
        //         echo $error;
        //     }
        // }

        // for ($f = 1; $f <= 9; $f++) {
        //     $entries = json_decode(Storage::disk('local')->get('/time_entries_new/timentries-' . $f . '.json'), true);
        //     // $old = json_decode(Storage::disk('local')->get('/time_entries_new/time_entries_oldies/timentries-' . $f . '.json'), true);

        //     $time_entries = [];
        //     for ($i = 0; $i < count($entries); $i++) {
        //         // $entry = TimeEntry::where('hris_number', $entries[$i]['hris_number'])->whereDate('time_start', Carbon::parse($entries[$i]['time_start'])->format('Y-m-d'))->first();
        //         // if (!$entry) {
        //         $time_entries[] = [
        //             // 'id' => $id,
        //             'hris_number' => $entries[$i]['hris_number'],
        //             'time_start' => $entries[$i]['time_start'],
        //             'time_end' => ($entries[$i]['time_end']) ? $entries[$i]['time_end'] : null,
        //             'department_id' => $entries[$i]['office_id'],
        //             'schedule_type' => $entries[$i]['schedule_type'],
        //             'official_time' => $entries[$i]['official_time'],
        //             'timekeeper_id' => 1,
        //             'tag' => strtolower($entries[$i]['tag']),
        //             'created_at' => Carbon::parse($entries[$i]['created_at'])->format('Y-m-d H:i:s'),
        //             'updated_at' => Carbon::parse($entries[$i]['updated_at'])->format('Y-m-d H:i:s'),
        //         ];
        //         // }
        //     }

        //     $insertion_data = collect($time_entries);
        //     $data = $insertion_data->chunk(100);
        //     foreach ($data as $key => $d) {
        //         try {
        //             // DB::table('time_entries')->insert()
        //             // DB::table('time_entries')->upsert($d
        //             //     ->toArray(), ['hris_number', 'time_start', 'time_end']);
        //             DB::table('time_entries')->insert($d
        //                 ->toArray());
        //         } catch (\Illuminate\Database\QueryException $e) {
        //             $error = $e->getMessage();
        //             echo $error;
        //         }
        //     }
        // }
        // dd('done');
    }
}
