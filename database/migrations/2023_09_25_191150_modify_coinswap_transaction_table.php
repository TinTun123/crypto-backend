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
        Schema::table('coinswap_transactions', function (Blueprint $table) {
            // Modify transfer_amount column
            $table->decimal('transfer_amount', 18, 10)->change();

            // Modify received_amount column
            $table->decimal('received_amount', 18, 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('coinswap_transactions', function (Blueprint $table) {
            // Define the reverse changes for the down method if needed
        });

    }
};
