<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'time_end',
        'official_time',
        'office',
        'appointment_status',
        'time_entry_type',
    ];

    protected $casts = [
        'time_start' => 'datetime',
        'time_end' => 'datetime',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'hris_number', 'hris_number');
    }
}
