<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = [
        'race_id', 'race_name', 'race_date', 'venue', 'race_number',
        'course_type', 'distance', 'turn_direction', 'weather', 'track_condition',
        'grade', 'field_size',
        'pace_3f_front', 'pace_5f_front', 'pace_3f_back', 'pace_5f_back', 'final_time',
        'pace_index_3f', 'pace_index_5f', 'pace_balance', 'pace_category',
        'race_url',
    ];

    protected $casts = [
        'race_date'     => 'date',
        'pace_3f_front' => 'float',
        'pace_5f_front' => 'float',
        'pace_3f_back'  => 'float',
        'pace_5f_back'  => 'float',
        'final_time'    => 'float',
        'pace_index_3f' => 'float',
        'pace_index_5f' => 'float',
        'pace_balance'  => 'float',
    ];

    public function lapTimes(): HasMany
    {
        return $this->hasMany(LapTime::class)->orderBy('lap_number');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RaceEntry::class)->orderBy('finish_position');
    }
}
