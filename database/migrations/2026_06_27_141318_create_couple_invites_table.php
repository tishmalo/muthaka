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
        Schema::create('couple_invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inviter_id');
            $table->string('invitee_phone');
            $table->string('invite_code')->unique();
            $table->string('status')->default('pending'); // pending, accepted, rejected, expired, canceled
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('inviter_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('inviter_id');
            $table->index('invitee_phone');
            $table->index('invite_code');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couple_invites');
    }
};
