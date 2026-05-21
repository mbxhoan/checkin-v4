<?php

use App\Enums\EventStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('code', 100);
            $table->text('description')->nullable();
            $table->string('location', 500)->nullable();
            $table->string('venue', 500)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status', 20)->default(EventStatus::Draft->value);
            $table->json('settings')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('status');
            $table->unique(['company_id', 'code']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
