<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Mov;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $id = 0;
        $num = 0;
        for ($f = 1; $f <= 16; $f++) {
            $schedules = json_decode(Storage::disk('local')->get('/new_schedule/schedules-' . $f . '.json'), true);

            $event = [];
            $mov = [];

            for ($i = 0; $i < count($schedules); $i++) {
                $emp = Employee::where('hris_number', $schedules[$i]['hris_number'])->first();
                if ($emp) {
                    $id++;
                    $event[] = [
                        'id' => $id,
                        'hris_number' => $schedules[$i]['hris_number'],
                        'start' => $schedules[$i]['start'],
                        'end' => $schedules[$i]['end'],
                        'tag' => strtolower($schedules[$i]['tag']),
                        'description' => $schedules[$i]['description'],
                        'status' => $schedules[$i]['status'],
                        'created_by' => '000000',
                        'created_at' => Carbon::parse($schedules[$i]['created_at'])->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($schedules[$i]['updated_at'])->format('Y-m-d H:i:s'),
                    ];

                    if ($schedules[$i]['mov'] != null) {
                        $num++;
                        $mov[] = ['id' => $num, 'filename' => $schedules[$i]['mov'], 'movable_id' => $id, 'movable_type' => 'App\Modes\Event', 'created_at' => now()->format('Y-m-d H:i:s'), 'updated_at' => now()->format('Y-m-d H:i:s')];
                    }
                } else if ($schedules[$i]['hris_number'] == null) {
                    $id++;
                    $event[] = [
                        'id' => $id,
                        'hris_number' => null,
                        'start' => $schedules[$i]['start'],
                        'end' => $schedules[$i]['end'],
                        'tag' => strtolower($schedules[$i]['tag']),
                        'description' => $schedules[$i]['description'],
                        'status' => $schedules[$i]['status'],
                        'created_by' => '000000',
                        'created_at' => Carbon::parse($schedules[$i]['created_at'])->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($schedules[$i]['updated_at'])->format('Y-m-d H:i:s'),
                    ];
                }
            }

            $insertion_data = collect($event);
            $data = $insertion_data->chunk(50);
            foreach ($data as $key => $d) {
                try {
                    DB::table('events')->insert($d
                        ->toArray());
                } catch (\Illuminate\Database\QueryException $e) {
                    $error = $e->getMessage();
                    echo $error;
                }
            }

            $insertionmov_data = collect($mov);
            $datamov = $insertionmov_data->chunk(50);
            foreach ($datamov as $key => $dmov) {
                try {
                    DB::table('movs')->insert($dmov
                        ->toArray());
                } catch (\Illuminate\Database\QueryException $e) {
                    $error = $e->getMessage();
                    echo $error;
                }
            }
        }
        dd('done');

        // foreach ($schedules as $schedule) {
        //     $emp = Employee::where('hris_number', $schedule['hris_number'])->first();

        //     if ($emp) {
        //         $event = Event::create([
        //             'hris_number' => $schedule['hris_number'],
        //             'start' => $schedule['start'],
        //             'end' => $schedule['end'],
        //             'tag' => strtolower($schedule['tag']),
        //             'description' => $schedule['description'],
        //             'status' => $schedule['status'],
        //             'created_by' => '000000',
        //             'created_at' => $schedule['created_at'],
        //             'updated_at' => $schedule['updated_at'],
        //         ]);

        //         if ($schedule['mov'] != null) {
        //             $event->mov()->create(['filename' => 'movs/' . $schedule['mov']]);
        //         }
        //     }
        // }

        return 'done';
    }

    function insertOrUpdate(array $rows)
    {
        $table = 'events';

        $first = reset($rows);

        $columns = implode(
            ',',
            array_map(function ($value) {
                return "$value";
            }, array_keys($first))
        );

        $values = implode(
            ',',
            array_map(function ($row) {
                return '(' . implode(
                    ',',
                    array_map(function ($value) {
                        return '"' . str_replace('"', '""', $value) . '"';
                    }, $row)
                ) . ')';
            }, $rows)
        );

        $updates = implode(
            ',',
            array_map(function ($value) {
                return "$value = VALUES($value)";
            }, array_keys($first))
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";
        dd($sql);

        return DB::statement($sql);
    }
}
