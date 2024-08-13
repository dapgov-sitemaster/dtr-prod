<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hris_number',
        'time_start',
        'time_end',
        'department_id',
        'schedule_type',
        'official_time',
        'timekeeper_id',
        'tag',
    ];

    protected $casts = [
        'time_start' => 'datetime',
        'time_end' => 'datetime',
        'schedule_type' => ScheduleType::class,
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }

    public function created_by()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'created_by');
    }
}
