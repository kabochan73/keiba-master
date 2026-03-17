<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RaceEntry extends Model
{
    protected $fillable = [
        'race_id', 'horse_id', 'jockey_id', 'trainer_id', 'breeder_id',
        'post_position', 'horse_number', 'finish_position', 'is_disqualified',
        'finish_time', 'time_diff', 'last_3f',
        'corner_1', 'corner_2', 'corner_3', 'corner_4',
        'weight', 'weight_change', 'age', 'burden_weight',
        'odds', 'popularity',
    ];

    protected $casts = [
        'finish_time'   => 'float',
        'time_diff'     => 'float',
        'last_3f'       => 'float',
        'burden_weight' => 'float',
        'odds'          => 'float',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function horse(): BelongsTo
    {
        return $this->belongsTo(Horse::class);
    }

    public function jockey(): BelongsTo
    {
        return $this->belongsTo(Jockey::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function correction(): HasOne
    {
        return $this->hasOne(RaceCorrection::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(HorseScore::class);
    }
}
