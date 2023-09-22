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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('isVerified')->default(false);
            $table->boolean('status')->default(false);
            $table->string('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->date('birthdat')->nullable();
            $table->string('id_card')->nullable();
            $table->string('profile_img')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
