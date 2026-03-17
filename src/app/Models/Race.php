<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'race_name',
        'race_date',
        'venue',
        'race_number',
        'course_type',
        'distance',
        'weather',
        'track_condition',
        'grade',
    ];

    protected $casts = [
        'race_date' => 'date',
    ];

    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
