<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horse_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_entry_id')->constrained('race_entries')->cascadeOnDelete();

            // タイム指数
            $table->decimal('raw_time', 6, 1)->nullable()->comment('実際タイム（秒）');
            $table->decimal('corrected_time', 6, 1)->nullable()->comment('補正タイム（秒）');
            $table->decimal('time_index', 6, 2)->nullable()->comment('タイム指数');
            $table->decimal('pace_index', 6, 2)->nullable()->comment('ペース指数');
            $table->decimal('last_3f_index', 6, 2)->nullable()->comment('上がり指数');
            $table->decimal('race_level_score', 6, 2)->nullable()->comment('レースレベルスコア');

            // 脚質パフォーマンス
            $table->string('pace_fit')->nullable()->comment('ペース適性評価（向き/不向き/普通）');

            $table->unique('race_entry_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horse_scores');
    }
};
