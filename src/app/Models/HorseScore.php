<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorseScore extends Model
{
    protected $fillable = [
        'race_entry_id',
        'raw_time', 'corrected_time', 'time_index',
        'pace_index', 'last_3f_index', 'race_level_score',
        'pace_fit',
    ];

    protected $casts = [
        'raw_time'          => 'float',
        'corrected_time'    => 'float',
        'time_index'        => 'float',
        'pace_index'        => 'float',
        'last_3f_index'     => 'float',
        'race_level_score'  => 'float',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(RaceEntry::class, 'race_entry_id');
    }
}
