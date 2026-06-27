<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('plan'); // free, premium, premium_plus
            $table->string('status')->default('active'); // active, expired, canceled, pending
            $table->string('provider'); // stripe, mpesa, apple, google
            $table->string('provider_subscription_id')->nullable();
            $table->string('provider_customer_id')->nullable();
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['user_id', 'provider']);
            $table->index('status');
            $table->index('ends_at');
            $table->index('provider_subscription_id');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};