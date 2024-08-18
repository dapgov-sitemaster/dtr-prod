<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Enums\AppointmentStatus;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hris_number',
        'last_name',
        'first_name',
        'middle_name',
        'department_id',
        'appointment_status',
        'employment_status',
        'signature_path',
        'identity_photo_path',
    ];

    protected $casts = [
        'appointment_status' => AppointmentStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('employee')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => auth()->user()->employee->full_name . " has created a new employee: " . ucwords("{$this->last_name}, {$this->first_name} {$this->middle_initial}"),
                    'updated' => auth()->user()->employee->full_name . " has updated info of " . ucwords("{$this->last_name}, {$this->first_name} {$this->middle_initial}"),
                    default => auth()->user()->employee->full_name . " has {$eventName} a employee"
                };
            })
            ->dontSubmitEmptyLogs();
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::title($value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::title($value),
        );
    }

    protected function middleName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value) ? Str::title($value) : null,
        );
    }

    public function getFullNameAttribute()
    {
        $middle_initial = ($this->middle_name) ?  substr($this->middle_name, 0, 1) . "." : '';
        return ucwords("{$this->last_name}, {$this->first_name} {$middle_initial}");
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'hris_number', 'hris_number');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function official_time(): HasOne
    {
        return $this->hasOne(OfficialTime::class, 'hris_number', 'hris_number')->where('status', 'approved')->orderBy('created_at', 'DESC');
    }

    public function latest_official_time(): HasOne
    {
        return $this->hasOne(OfficialTime::class, 'hris_number', 'hris_number')->latest();
    }

    public function time_entries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'hris_number', 'hris_number')->latest();
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'hris_number', 'hris_number')->latest();
    }

    public function scopeIsDapcc($query)
    {
        return $query->whereHas('department', fn ($query) => (Gate::allows('view-dapcc')) ? $query->where('center', 'TEST') : $query);
    }

    public function scopeSearchEmployee($query, $search)
    {
        return $query->where('last_name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('hris_number', 'like', "%{$search}%");
    }
}
