<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->decimal('pace_balance_5f', 5, 2)->nullable()->after('pace_balance')
                ->comment('前後バランス5F（前半5F - 後半5F）');
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('pace_balance_5f');
        });
    }
};
