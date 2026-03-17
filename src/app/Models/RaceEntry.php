<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'horse_id',
        'gate_number',
        'horse_number',
        'jockey',
        'weight',
        'horse_weight',
        'horse_weight_diff',
        'odds',
        'popularity',
        'finish_position',
        'finish_time',
        'margin',
    ];

    protected $casts = [
        'odds' => 'decimal:1',
        'weight' => 'decimal:1',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function horse(): BelongsTo
    {
        return $this->belongsTo(Horse::class);
    }
}
