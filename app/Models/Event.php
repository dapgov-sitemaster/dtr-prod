<?php

namespace App\Models;

use App\Observers\EventObserver;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class Event extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hris_number',
        'start',
        'end',
        'tag',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tag' => Gate::allows('view-dapcc') ? \App\Enums\Dapcc\Events::class : \App\Enums\Events::class,
            // 'tag' => \App\Enums\Events::class,
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('event')
            ->setDescriptionForEvent(fn(string $eventName) => auth()->user()->employee->full_name . " has {$eventName} an event")
            ->dontSubmitEmptyLogs();
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }

    public function official_time(): HasOne
    {
        return $this->hasOne(OfficialTime::class, 'hris_number', 'hris_number')->where('status', 'approved')->latest();
    }

    public function event_created_by(): HasOne
    {
        return $this->hasOne(Employee::class, 'hris_number', 'created_by');
    }

    public function mov(): MorphOne
    {
        return $this->morphOne(Mov::class, 'movable');
    }
}
