<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lap_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->integer('lap_number')->comment('ラップ番号（1始まり）');
            $table->decimal('lap_time', 5, 1)->comment('ラップタイム（秒）');
            $table->decimal('cumulative_time', 6, 1)->nullable()->comment('累計タイム（秒）');
            $table->timestamps();

            $table->unique(['race_id', 'lap_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lap_times');
    }
};
