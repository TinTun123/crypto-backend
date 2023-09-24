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
        Schema::table('user_private_keys', function (Blueprint $table) {
            //  
            $table->dropForeign(['user_id']); // Drop the existing foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Add the new constraint
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_private_keys', function (Blueprint $table) {
            //
            $table->dropForeign(['user_id']); // Drop the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users'); // You can add onDelete(null) here if you want to remove the cascade on rollback
        
        });
    }
};
