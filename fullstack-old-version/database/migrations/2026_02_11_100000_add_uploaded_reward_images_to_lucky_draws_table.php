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
            $table->json('uploaded_reward_images')->nullable()->after('field_mappings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lucky_draws', function (Blueprint $table) {
            $table->dropColumn('uploaded_reward_images');
        });
    }
};
