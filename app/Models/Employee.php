<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'hris_number', 'hris_number');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
