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
        Schema::table('users', function (Blueprint $table) {
            // Remove the 'name' column
            $table->dropColumn('name');

            // Add 'firstName' and 'lastName' columns
            $table->string('firstName');
            $table->string('lastName');

            // Add 'country' column
            $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function (Blueprint $table) {
            // Reverse the changes if needed
            $table->string('name');
            $table->dropColumn('firstName');
            $table->dropColumn('lastName');
            $table->dropColumn('country');
        });
    }
};
