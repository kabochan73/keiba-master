<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeders', function (Blueprint $table) {
            $table->id();
            $table->string('breeder_id')->unique()->comment('netkeiba 生産者ID');
            $table->string('name')->comment('生産者名');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeders');
    }
};
