<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_entry_id')->constrained('race_entries')->cascadeOnDelete();

            // 手動補正値（秒単位、プラスが有利補正）
            $table->decimal('distance_loss', 4, 2)->default(0)->comment('距離ロス補正（秒）');
            $table->decimal('interference', 4, 2)->default(0)->comment('詰まり・不利補正（秒）');
            $table->decimal('slow_start', 4, 2)->default(0)->comment('出遅れ補正（秒）');
            $table->decimal('jockey_correction', 4, 2)->default(0)->comment('騎手補正（秒）');
            $table->decimal('other_correction', 4, 2)->default(0)->comment('その他補正（秒）');
            $table->text('note')->nullable()->comment('補正メモ');

            // 計算済み補正タイム
            $table->decimal('corrected_time', 6, 1)->nullable()->comment('補正後タイム（秒）');

            $table->unique('race_entry_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_corrections');
    }
};
