<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->string('category')->nullable(); // conversation, reflection, fun, deep
            $table->string('emoji')->nullable();
            $table->string('type')->default('daily'); // daily, weekly, custom
            $table->boolean('is_active')->default(true);
            $table->date('scheduled_date')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('scheduled_date');
            $table->index('sent_at');
            $table->index('type');
        });

       
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_answers');
    }
};