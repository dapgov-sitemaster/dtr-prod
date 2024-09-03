<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Mov;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Testing2Controller extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        $events = Event::where('created_at', '>', '2024-08-27 17:44:03')->orderBy('created_at')->limit(10)->get();
        $delete = [];
        $temp = null;

        dd($events);
        for ($i = 0; $i < count($events); $i++) {
            if ($i == 0) {
                $temp = $events[$i];
            } else {
                if ($events[$i]->hris_number == $temp->hris_number) {
                    if ($events[$i]->tag == $temp->tag) {
                        dd($events[$i]->created_at);
                        if ($events[$i]->created_at == $temp->created_at) {
                            dd($temp->created_at);
                        }
                    }
                }
            }
        }
        dd($events);

        // $id = 23731;
        // $num = 0;

        // for ($f = 4; $f <= 9; $f++) {
        //     $entries = json_decode(Storage::disk('local')->get('/time_entries/timentries-' . $f . '.json'), true);

        //     $time_entries = [];
        //     for ($i = 0; $i < count($entries); $i++) {
        //         $emp = Employee::where('hris_number', $entries[$i]['hris_number'])->first();
        //         if ($emp) {
        //             $id++;
        //             $time_entries[] = [
        //                 // 'id' => $id,
        //                 'hris_number' => $entries[$i]['hris_number'],
        //                 'time_start' => $entries[$i]['time_start'],
        //                 'time_end' => $entries[$i]['time_end'],
        //                 'department_id' => $entries[$i]['office_id'],
        //                 'schedule_type' => $entries[$i]['schedule_type'],
        //                 'official_time' => $entries[$i]['official_time'],
        //                 'timekeeper_id' => 1,
        //                 'tag' => strtolower($entries[$i]['tag']),
        //                 'created_at' => Carbon::parse($entries[$i]['created_at'])->format('Y-m-d H:i:s'),
        //                 'updated_at' => Carbon::parse($entries[$i]['updated_at'])->format('Y-m-d H:i:s'),
        //             ];
        //         }
        //     }

        //     $insertion_data = collect($time_entries);
        //     $data = $insertion_data->chunk(100);
        //     foreach ($data as $key => $d) {
        //         try {
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
