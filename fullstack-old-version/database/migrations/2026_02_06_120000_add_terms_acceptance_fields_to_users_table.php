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
            $table->boolean('must_accept_terms')
                ->default(false)
                ->after('session_id');

            $table->timestamp('terms_accepted_at')
                ->nullable()
                ->after('must_accept_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_accept_terms',
                'terms_accepted_at',
            ]);
        });
    }
};
