<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distance_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('user_id');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->string('place_name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('distance_to_partner', 10, 2)->nullable(); // in kilometers
            $table->boolean('is_sharing')->default(true);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['couple_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('recorded_at');
            $table->index('is_sharing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distance_events');
    }
};