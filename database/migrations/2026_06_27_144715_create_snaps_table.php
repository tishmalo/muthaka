<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snaps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration')->default(10); // seconds
            $table->boolean('is_seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['couple_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index('expires_at');
            $table->index('is_seen');
            $table->index('sent_at');
        });

        Schema::create('snap_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('snap_id');
            $table->uuid('viewer_id');
            $table->timestamp('viewed_at')->useCurrent();

            $table->foreign('snap_id')->references('id')->on('snaps')->onDelete('cascade');
            $table->foreign('viewer_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['snap_id', 'viewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snap_views');
        Schema::dropIfExists('snaps');
    }
};