<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
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
        'break_start',
        'break_end',
        'time_end',
        'schedule_type',
        'official_time',
        'office',
        'appointment_status',
        'time_entry_type',
    ];

    protected $casts = [
        'time_start' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'time_end' => 'datetime',
        'schedule_type' => ScheduleType::class,
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }
}
