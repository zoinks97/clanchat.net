<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            // Drop the foreign key explicitly by name
            $foreignKeyName = 'clans_user_id_foreign';

            // Only drop if it exists
            try {
                $table->dropForeign($foreignKeyName);
            } catch (\Illuminate\Database\QueryException $e) {
                // Foreign key doesn't exist; ignore
            }

            // Drop the 'user_id' column if it exists
            if (Schema::hasColumn('clans', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            // Re-add the 'user_id' column
            $table->unsignedBigInteger('user_id')->nullable();

            // Re-add the foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
