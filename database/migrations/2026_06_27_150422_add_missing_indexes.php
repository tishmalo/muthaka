<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add any missing indexes or columns here
        Schema::table('users', function (Blueprint $table) {
            $table->index(['status', 'phone_verified_at']);
            $table->index(['phone_number', 'status']);
        });

        Schema::table('couples', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('mood_events', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'created_at']);
        });

        Schema::table('note_events', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'created_at']);
        });

        Schema::table('notification_tokens', function (Blueprint $table) {
            $table->index(['user_id', 'is_active', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status', 'phone_verified_at']);
            $table->dropIndex(['phone_number', 'status']);
        });

        Schema::table('couples', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('mood_events', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'receiver_id', 'created_at']);
        });

        Schema::table('note_events', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'receiver_id', 'created_at']);
        });

        Schema::table('notification_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active', 'platform']);
        });
    }
};