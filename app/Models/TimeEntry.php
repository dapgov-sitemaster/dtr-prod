<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeEntry extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('time_entries')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => $this->employee->full_name . " has created a new employee",
                    'updated' => $this->employee->full_name . " has updated info",
                    default => $this->employee->full_name . " has {$eventName} a employee"
                };
            })
            ->dontSubmitEmptyLogs();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }

    public function created_by()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeIsDapcc($query)
    {
        return $query->whereHas('department', fn ($query) => (Gate::allows('view-dapcc')) ? $query->where('center', 'TEST') : $query);
    }
}
