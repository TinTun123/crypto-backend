<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('user_balances', function (Blueprint $table) {
            $table->decimal('balance_amount', 20, 10)->change(); // 20 total digits, 18 decimal places
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('user_balances', function (Blueprint $table) {
            $table->decimal('balance_amount', 20, 18)->change(); // Change back to previous precision and scale if needed
        });
    }
};
