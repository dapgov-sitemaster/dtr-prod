<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

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
    ];

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
            get: fn (string $value) => Str::title($value),
        );
    }

    public function getFullNameAttribute()
    {
        return ucwords("{$this->last_name}, {$this->first_name} ".substr($this->middle_name, 0, 1).".");
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'hris_number', 'hris_number');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
