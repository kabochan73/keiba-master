<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('trainer_id')->unique()->comment('netkeiba 調教師ID');
            $table->string('name')->comment('調教師名');
            $table->string('stable')->nullable()->comment('所属厩舎');
            $table->string('affiliation')->nullable()->comment('所属（美浦/栗東）');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
