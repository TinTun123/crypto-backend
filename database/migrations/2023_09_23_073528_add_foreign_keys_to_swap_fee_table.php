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
        Schema::table('swap_fee', function (Blueprint $table) {
            //
            $table->foreign('from_wallet_id')->references('id')->on('wallets');
            $table->foreign('to_wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('swap_fee', function (Blueprint $table) {
            //
            $table->dropForeign(['from_wallet_id']);
            $table->dropForeign(['to_wallet_id']);
        });
    }
};
