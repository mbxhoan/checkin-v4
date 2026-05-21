<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('scanned_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('type', 20);
            $table->timestamp('scanned_at');
            $table->string('device_info', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->index('client_id');
            $table->index('scanned_by');
            $table->index('scanned_at');
            $table->index(['event_id', 'client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
