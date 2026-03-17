<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_pace_averages', function (Blueprint $table) {
            $table->id();
            $table->string('race_name')->comment('レース名');
            $table->integer('distance')->comment('距離（m）');
            $table->string('venue')->comment('開催場所');

            // 過去平均値
            $table->decimal('avg_pace_3f_front', 5, 1)->nullable()->comment('前半3F平均');
            $table->decimal('avg_pace_5f_front', 5, 1)->nullable()->comment('前半5F平均');
            $table->decimal('avg_pace_3f_back', 5, 1)->nullable()->comment('後半3F平均');
            $table->decimal('avg_final_time', 6, 1)->nullable()->comment('勝ちタイム平均');
            $table->integer('sample_count')->default(0)->comment('サンプル数');
            $table->date('last_calculated_at')->nullable()->comment('最終計算日');

            $table->unique(['race_name', 'distance', 'venue']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_pace_averages');
    }
};
