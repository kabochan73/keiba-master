<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceCorrection extends Model
{
    protected $fillable = [
        'race_entry_id',
        'distance_loss', 'interference', 'slow_start',
        'jockey_correction', 'other_correction', 'note',
        'corrected_time',
    ];

    protected $casts = [
        'distance_loss'      => 'float',
        'interference'       => 'float',
        'slow_start'         => 'float',
        'jockey_correction'  => 'float',
        'other_correction'   => 'float',
        'corrected_time'     => 'float',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(RaceEntry::class, 'race_entry_id');
    }

    /**
     * 補正タイムを再計算して保存
     */
    public function recalculate(): void
    {
        $entry = $this->entry;
        if (!$entry || !$entry->finish_time) {
            return;
        }

        $totalCorrection = $this->distance_loss
            + $this->interference
            + $this->slow_start
            + $this->jockey_correction
            + $this->other_correction;

        $this->corrected_time = round($entry->finish_time - $totalCorrection, 1);
        $this->save();
    }
}
