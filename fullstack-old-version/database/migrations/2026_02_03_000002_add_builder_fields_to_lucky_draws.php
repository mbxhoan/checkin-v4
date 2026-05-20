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
        Schema::table('lucky_draws', function (Blueprint $table) {
            $table->json('builder_settings')->nullable()->after('type');
            $table->json('field_mappings')->nullable()->after('builder_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lucky_draws', function (Blueprint $table) {
            $table->dropColumn(['builder_settings', 'field_mappings']);
        });
    }
};
