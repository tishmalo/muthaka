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
        Schema::create('countdowns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('couple_id');
            $table->uuid('user_id');
            $table->string('event_name');
            $table->date('event_date');
            $table->string('background_color', 7)->default('#FFFFFF');
            $table->string('icon_emoji', 10)->default('🎉');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_birthday')->default(false);
            $table->timestamps();

            $table->foreign('couple_id')->references('id')->on('couples')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('couple_id');
            $table->index('event_date');
            $table->index('is_active');
            $table->index('is_birthday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countdowns');
    }
};
