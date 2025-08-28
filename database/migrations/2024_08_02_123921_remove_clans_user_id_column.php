<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveClansUserIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            // Drop the foreign key constraint if it exists
            $foreignKeyName = 'clans_user_id_foreign';
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $sm->listTableForeignKeys('clans');
            $exists = collect($foreignKeys)->contains(fn($fk) => $fk->getName() === $foreignKeyName);

            if ($exists) {
                $table->dropForeign($foreignKeyName);
            }

            // Drop the 'user_id' column
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            // Recreate the 'user_id' column
            $table->unsignedBigInteger('user_id')->nullable();

            // Recreate the foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
}
