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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_type');
            $table->decimal('usd_equivalent', 10, 2);
            $table->string('QR_image_url')->nullable();
            $table->string('LOGO_image_url')->nullable();
            $table->string('address');
            $table->decimal('normal_fee', 10, 2);
            $table->decimal('pro_fee', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
