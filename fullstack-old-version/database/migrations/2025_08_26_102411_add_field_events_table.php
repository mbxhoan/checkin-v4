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
            $table->after('province_id', function ($table) {
                $table->unsignedBigInteger('type_id')->nullable();

                $table->index('type_id', 'idx_type_id');

                $table->foreign('type_id')
                    ->references('id')
                    ->on('event_types')
                    ->onDelete('set null');
            });
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
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropColumn(['assignee_id']);
        });
    }
};
