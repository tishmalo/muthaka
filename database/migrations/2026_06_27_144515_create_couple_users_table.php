<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couple_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('user_id');
            $table->string('role')->default('partner'); // partner, admin
            $table->string('status')->default('active'); // active, blocked, left
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['couple_id', 'user_id']);
            $table->index(['couple_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_user');
    }
};