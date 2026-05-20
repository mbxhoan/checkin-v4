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
        Schema::create('n8n_chat_issue_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('session_id')
                ->nullable()
                ->constrained('n8n_chat_sessions')
                ->nullOnDelete();
            $table->integer('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companys')
                ->nullOnDelete();
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();
            $table->string('category', 100)->default('general');
            $table->string('severity', 20)->default('medium');
            $table->string('status', 20)->default('OPEN');
            $table->string('title', 255)->nullable();
            $table->longText('description')->nullable();
            $table->longText('raw_user_message');
            $table->longText('ai_suggestion')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_n8n_issue_user_status');
            $table->index(['company_id', 'status'], 'idx_n8n_issue_company_status');
            $table->index(['event_id', 'status'], 'idx_n8n_issue_event_status');
            $table->index('created_at', 'idx_n8n_issue_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_chat_issue_reports');
    }
};

