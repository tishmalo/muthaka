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
        Schema::create('note_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->text('content');
            $table->boolean('is_seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['couple_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index('is_seen');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_events');
    }
};
