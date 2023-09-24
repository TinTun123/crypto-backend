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
        Schema::create('swap_fee', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee', 10, 2);
            $table->unsignedBigInteger('from_wallet_id');
            $table->unsignedBigInteger('to_wallet_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swap_fee');
    }
};
