<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mov extends Model
{
    // use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
    ];

    /**
     * Get the parent movable model.
     */
    public function movable(): MorphTo
    {
        return $this->morphTo();
    }
}
