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
        Schema::table('campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->dateTime('scheduled_at')
                    ->nullable()
                    ->after('hold_time')
                    ->index('idx_campaigns_scheduled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->dropIndex('idx_campaigns_scheduled_at');
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
