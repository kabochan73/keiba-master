<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('horse_id')->constrained('horses')->cascadeOnDelete();
            $table->foreignId('jockey_id')->nullable()->constrained('jockeys')->nullOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('breeder_id')->nullable()->constrained('breeders')->nullOnDelete();

            // 出走情報
            $table->integer('post_position')->nullable()->comment('枠番');
            $table->integer('horse_number')->nullable()->comment('馬番');
            $table->integer('finish_position')->nullable()->comment('着順');
            $table->boolean('is_disqualified')->default(false)->comment('失格フラグ');

            // タイム
            $table->decimal('finish_time', 6, 1)->nullable()->comment('タイム（秒）');
            $table->string('time_diff', 20)->nullable()->comment('着差（クビ・ハナ・1/2 等）');
            $table->decimal('last_3f', 4, 1)->nullable()->comment('上がり3F');

            // コーナー通過順位
            $table->integer('corner_1')->nullable()->comment('1コーナー通過順');
            $table->integer('corner_2')->nullable()->comment('2コーナー通過順');
            $table->integer('corner_3')->nullable()->comment('3コーナー通過順');
            $table->integer('corner_4')->nullable()->comment('4コーナー通過順');

            // 馬体情報
            $table->integer('weight')->nullable()->comment('馬体重（kg）');
            $table->integer('weight_change')->nullable()->comment('馬体重増減（kg）');
            $table->integer('age')->nullable()->comment('年齢');
            $table->decimal('burden_weight', 4, 1)->nullable()->comment('斤量');

            // オッズ・人気
            $table->decimal('odds', 7, 1)->nullable()->comment('単勝オッズ');
            $table->integer('popularity')->nullable()->comment('人気順');

            $table->unique(['race_id', 'horse_id']);
            $table->index(['horse_id', 'race_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_entries');
    }
};
