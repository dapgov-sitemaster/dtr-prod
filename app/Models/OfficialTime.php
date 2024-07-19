<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'mov',
        'status',
        'created_by',
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
