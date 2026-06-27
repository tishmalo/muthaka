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
        Schema::create('widget_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('user_id');
            $table->uuid('partner_id');
            $table->uuid('latest_mood_event_id')->nullable();
            $table->uuid('latest_note_event_id')->nullable();
            $table->uuid('latest_doodle_event_id')->nullable();
            $table->uuid('latest_snap_event_id')->nullable();
            $table->uuid('latest_distance_event_id')->nullable();
            $table->uuid('active_countdown_id')->nullable();
            $table->integer('version')->default(0);
            $table->json('summary')->nullable();
            $table->timestamp('updated_at');

            // Foreign keys
            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->unique(['couple_id', 'user_id']);
            $table->index('partner_id');
            $table->index('version');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_states');
    }
};
