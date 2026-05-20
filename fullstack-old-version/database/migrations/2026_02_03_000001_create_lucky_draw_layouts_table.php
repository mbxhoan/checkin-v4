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
        Schema::create('lucky_draw_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_draw_id')
                ->constrained('lucky_draws')
                ->cascadeOnDelete();
            $table->foreignId('reward_id')
                ->nullable()
                ->constrained('lucky_draw_rewards')
                ->cascadeOnDelete();
            $table->string('name', 255);
            $table->integer('canvas_width')->default(1920);
            $table->integer('canvas_height')->default(1080);
            $table->enum('background_type', ['color', 'image', 'video'])->default('color');
            $table->text('background_value')->nullable();
            $table->json('blocks');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('lucky_draw_id', 'idx_layout_lucky_draw_id');
            $table->index('reward_id', 'idx_layout_reward_id');
            $table->unique(['lucky_draw_id', 'reward_id'], 'unique_draw_reward');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lucky_draw_layouts');
    }
};
