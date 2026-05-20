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
        Schema::create('n8n_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('status', 20)->default('ACTIVE');
            $table->string('mode', 20)->default('UNSET');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_n8n_chat_sessions_user_status');
            $table->index('created_at', 'idx_n8n_chat_sessions_created_at');
        });

        Schema::create('n8n_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('n8n_chat_sessions')
                ->cascadeOnDelete();
            $table->integer('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('role', 20);
            $table->longText('content');
            $table->longText('content_html')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'id'], 'idx_n8n_chat_messages_session_id');
            $table->index(['user_id', 'id'], 'idx_n8n_chat_messages_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_chat_messages');
        Schema::dropIfExists('n8n_chat_sessions');
    }
};
