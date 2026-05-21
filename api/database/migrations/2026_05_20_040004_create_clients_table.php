<?php

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('qrcode', 255);
            $table->string('status', 20)->default(ClientStatus::Registered->value);
            $table->json('custom_fields')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('source', 20)->default(ClientSource::Manual->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('event_id');
            $table->index('company_id');
            $table->index('email');
            $table->index('status');
            $table->unique(['event_id', 'qrcode']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
