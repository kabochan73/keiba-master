<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horse extends Model
{
    protected $fillable = [
        'horse_id', 'name', 'sex', 'birth_date', 'coat_color',
        'trainer_id', 'breeder_id',
        'father', 'mother', 'mother_father',
        'running_style', 'avg_corner_position_rate',
    ];

    protected $casts = [
        'birth_date'               => 'date',
        'avg_corner_position_rate' => 'float',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function breeder(): BelongsTo
    {
        return $this->belongsTo(Breeder::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
