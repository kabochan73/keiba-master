<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horses', function (Blueprint $table) {
            $table->id();
            $table->string('horse_id')->unique()->comment('netkeiba 馬ID');
            $table->string('name')->comment('馬名');
            $table->string('sex')->nullable()->comment('性別（牡/牝/騸）');
            $table->date('birth_date')->nullable()->comment('生年月日');
            $table->string('coat_color')->nullable()->comment('毛色');
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('breeder_id')->nullable()->constrained('breeders')->nullOnDelete();
            $table->string('father')->nullable()->comment('父');
            $table->string('mother')->nullable()->comment('母');
            $table->string('mother_father')->nullable()->comment('母父');

            // 脚質（計算値）
            $table->string('running_style')->nullable()->comment('脚質（逃げ/先行/差し/追込）');
            $table->decimal('avg_corner_position_rate', 5, 3)->nullable()->comment('平均コーナー通過ポジション率');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horses');
    }
};
