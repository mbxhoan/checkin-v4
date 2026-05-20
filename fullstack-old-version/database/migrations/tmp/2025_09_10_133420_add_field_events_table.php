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
        Schema::table('events', function (Blueprint $table) {
            $table->after('type_id', function ($table) {
                $table->integer('assignee_id')->unsigned()
                    ->nullable();

                $table->index('assignee_id', 'idx_assignee_id');

                $table->foreign('assignee_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']); // Correct way
            $table->dropColumn(['assignee_id']); // Drop both columns
        });
    }
};
