<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doodles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->integer('stroke_count')->nullable();
            $table->text('metadata')->nullable(); // JSON for colors, etc.
            $table->boolean('is_seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['couple_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index('is_seen');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doodles');
    }
};