<?php

namespace App\Models;

use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group',
        'center',
        'office',
    ];

    protected $appends = [
        'description'
    ];

    public function getDescriptionAttribute()
    {
        return "{$this->group}/{$this->center}/{$this->office}";
    }

    public function scopeIsDapcc($query)
    {
        return $query->when(Gate::allows('view-dapcc'), fn($query) => $query->where('center', 'DAPCC'));
    }
}
