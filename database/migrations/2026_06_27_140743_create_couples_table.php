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
        Schema::create('couples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_one_id');
            $table->uuid('partner_two_id')->nullable();
            $table->string('status')->default('pending'); // pending, active, blocked, disconnected
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->string('disconnect_reason')->nullable();
            $table->json('settings')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('partner_one_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('partner_two_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('partner_one_id');
            $table->index('partner_two_id');
            $table->index('status');
            $table->index('connected_at');
            $table->index(['partner_one_id', 'partner_two_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couples');
    }
};
