<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    protected $fillable = ['trainer_id', 'name', 'stable', 'affiliation'];

    public function horses(): HasMany
    {
        return $this->hasMany(Horse::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
