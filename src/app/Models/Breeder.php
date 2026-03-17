<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Breeder extends Model
{
    protected $fillable = ['breeder_id', 'name'];

    public function horses(): HasMany
    {
        return $this->hasMany(Horse::class);
    }
}
