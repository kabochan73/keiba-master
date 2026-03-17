<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jockey extends Model
{
    protected $fillable = ['jockey_id', 'name', 'affiliation'];

    public function entries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
