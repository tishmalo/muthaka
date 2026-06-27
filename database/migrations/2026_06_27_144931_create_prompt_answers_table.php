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
        Schema::create('prompt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
            $table->uuid('couple_id');
            $table->uuid('user_id');
            $table->text('answer');
            $table->integer('reaction')->nullable(); // 1-5
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['prompt_id', 'couple_id', 'user_id']);
            $table->index(['couple_id', 'answered_at']);
            $table->index(['user_id', 'answered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_answers');
    }
};
