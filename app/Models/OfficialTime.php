<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficialTime extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hris_number',
        'time_in',
        'schedule_type',
        'effectivity_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'effectivity_date' => 'datetime',
        'schedule_type' => ScheduleType::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hris_number', 'hris_number');
    }

    public function created_by(): HasOne
    {
        return $this->hasOne(Employee::class, 'hris_number', 'created_by');
    }

    public function mov(): MorphOne
    {
        return $this->morphOne(Mov::class, 'movable');
    }
}
