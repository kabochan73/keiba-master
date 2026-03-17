<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LapTime extends Model
{
    protected $fillable = ['race_id', 'lap_number', 'lap_time', 'cumulative_time'];

    protected $casts = [
        'lap_time'        => 'float',
        'cumulative_time' => 'float',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
