<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('race_id')->unique()->comment('netkeiba レースID');
            $table->string('race_name')->comment('レース名');
            $table->date('race_date')->comment('開催日');
            $table->string('venue')->comment('開催場所');
            $table->integer('race_number')->comment('レース番号');
            $table->string('course_type')->comment('コース種別（芝/ダート）');
            $table->integer('distance')->comment('距離（m）');
            $table->string('turn_direction')->nullable()->comment('回り（右/左）');
            $table->string('weather')->nullable()->comment('天候');
            $table->string('track_condition')->nullable()->comment('馬場状態（良/稍重/重/不良）');
            $table->string('grade')->nullable()->comment('グレード（G1/G2/G3）');
            $table->integer('field_size')->nullable()->comment('出走頭数');

            // ラップ・ペース
            $table->decimal('pace_3f_front', 5, 1)->nullable()->comment('前半3F');
            $table->decimal('pace_5f_front', 5, 1)->nullable()->comment('前半5F');
            $table->decimal('pace_3f_back', 5, 1)->nullable()->comment('後半3F');
            $table->decimal('pace_5f_back', 5, 1)->nullable()->comment('後半5F');
            $table->decimal('final_time', 6, 1)->nullable()->comment('勝ちタイム（秒）');

            // 過去平均との差（ペース指数）
            $table->decimal('pace_index_3f', 5, 2)->nullable()->comment('前半3Fペース指数（過去平均との差）');
            $table->decimal('pace_index_5f', 5, 2)->nullable()->comment('前半5Fペース指数（過去平均との差）');
            $table->decimal('pace_balance', 5, 2)->nullable()->comment('前後バランス（前半3F - 後半3F）');
            $table->string('pace_category')->nullable()->comment('ペース区分（ハイ/ミドル/スロー）');

            $table->text('race_url')->nullable()->comment('netkeibaのURL');
            $table->timestamps();

            $table->index(['race_date', 'venue']);
            $table->index(['race_name', 'race_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
