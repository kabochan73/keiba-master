<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horse extends Model
{
    use HasFactory;

    protected $fillable = [
        'horse_id',
        'horse_name',
        'sex',
        'birth_year',
        'trainer',
        'owner',
    ];

    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
