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
        Schema::create('coinswap_transactions', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_wallet_id');
            $table->unsignedBigInteger('to_wallet_id');
            $table->decimal('transfer_amount', 10, 10);
            $table->decimal('received_amount', 10, 10);

            $table->enum('status', ['pending', 'approve', 'denied'])->default('pending');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('to_wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coinswap_transactions');
    }
};
