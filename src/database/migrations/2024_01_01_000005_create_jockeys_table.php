<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jockeys', function (Blueprint $table) {
            $table->id();
            $table->string('jockey_id')->unique()->comment('netkeiba 騎手ID');
            $table->string('name')->comment('騎手名');
            $table->string('affiliation')->nullable()->comment('所属（美浦/栗東/地方）');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jockeys');
    }
};
