<?php

namespace App\Models;

use App\Enums\Events;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Event extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'tag' => Events::class,
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }

    public function official_time(): HasOne
    {
        return $this->hasOne(OfficialTime::class, 'hris_number', 'hris_number')->where('status', 'approved')->latest();
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
