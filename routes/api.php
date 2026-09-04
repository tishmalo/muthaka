<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/auth/verify-email', [App\Http\Controllers\Api\V1\AuthController::class, 'verifyEmail']);
    Route::post('/auth/verify-phone', [App\Http\Controllers\Api\V1\AuthController::class, 'verifyPhone']);
    Route::post('/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('/auth/google', [App\Http\Controllers\Api\V1\AuthController::class, 'loginWithGoogle']);
    Route::post('/auth/resend-email-otp', [App\Http\Controllers\Api\V1\AuthController::class, 'resendEmailOtp']);
    Route::post('/auth/resend-otp', [App\Http\Controllers\Api\V1\AuthController::class, 'resendOtp']);
    Route::post('/auth/forgot-password', [App\Http\Controllers\Api\V1\AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [App\Http\Controllers\Api\V1\AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/auth/me', [App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        Route::put('/auth/profile', [App\Http\Controllers\Api\V1\AuthController::class, 'updateProfile']);
        Route::post('/auth/refresh', [App\Http\Controllers\Api\V1\AuthController::class, 'refresh']);

        Route::post('/couple/invite', [App\Http\Controllers\Api\V1\CoupleController::class, 'invite']);
        Route::post('/couple/accept/{code}', [App\Http\Controllers\Api\V1\CoupleController::class, 'accept']);
        Route::post('/couple/reject/{code}', [App\Http\Controllers\Api\V1\CoupleController::class, 'reject']);
        Route::post('/couple/cancel', [App\Http\Controllers\Api\V1\CoupleController::class, 'cancel']);
        Route::delete('/couple/disconnect', [App\Http\Controllers\Api\V1\CoupleController::class, 'disconnect']);
        Route::post('/couple/block', [App\Http\Controllers\Api\V1\CoupleController::class, 'block']);
        Route::get('/couple/status', [App\Http\Controllers\Api\V1\CoupleController::class, 'status']);
        Route::get('/couple/partner', [App\Http\Controllers\Api\V1\CoupleController::class, 'partner']);

        Route::get('/widget-state', [App\Http\Controllers\Api\V1\WidgetStateController::class, 'index']);
        Route::get('/widget-state/version', [App\Http\Controllers\Api\V1\WidgetStateController::class, 'version']);
        Route::match(['HEAD'], '/widget-state', [App\Http\Controllers\Api\V1\WidgetStateController::class, 'check']);

        Route::post('/moods', [App\Http\Controllers\Api\V1\MoodController::class, 'store']);
        Route::get('/moods', [App\Http\Controllers\Api\V1\MoodController::class, 'index']);
        Route::get('/moods/unseen', [App\Http\Controllers\Api\V1\MoodController::class, 'unseen']);
        Route::put('/moods/mark-seen', [App\Http\Controllers\Api\V1\MoodController::class, 'markSeen']);

        Route::post('/notes', [App\Http\Controllers\Api\V1\NoteController::class, 'store']);
        Route::get('/notes', [App\Http\Controllers\Api\V1\NoteController::class, 'index']);
        Route::get('/notes/unseen', [App\Http\Controllers\Api\V1\NoteController::class, 'unseen']);
        Route::put('/notes/{noteId}/seen', [App\Http\Controllers\Api\V1\NoteController::class, 'markSeen']);

        Route::post('/countdowns', [App\Http\Controllers\Api\V1\CountdownController::class, 'store']);
        Route::get('/countdowns', [App\Http\Controllers\Api\V1\CountdownController::class, 'index']);
        Route::get('/countdowns/active', [App\Http\Controllers\Api\V1\CountdownController::class, 'active']);
        Route::put('/countdowns/{id}', [App\Http\Controllers\Api\V1\CountdownController::class, 'update']);
        Route::delete('/countdowns/{id}', [App\Http\Controllers\Api\V1\CountdownController::class, 'destroy']);

        Route::post('/devices/register', [App\Http\Controllers\Api\V1\DeviceController::class, 'register']);
        Route::delete('/devices/unregister', [App\Http\Controllers\Api\V1\DeviceController::class, 'unregister']);
        Route::get('/devices', [App\Http\Controllers\Api\V1\DeviceController::class, 'index']);

        Route::post('/doodles', [App\Http\Controllers\Api\V1\DoodleController::class, 'store']);
        Route::get('/doodles', [App\Http\Controllers\Api\V1\DoodleController::class, 'index']);
        Route::get('/doodles/unseen', [App\Http\Controllers\Api\V1\DoodleController::class, 'unseen']);
        Route::put('/doodles/{id}/seen', [App\Http\Controllers\Api\V1\DoodleController::class, 'markSeen']);

        Route::post('/snaps', [App\Http\Controllers\Api\V1\SnapController::class, 'store']);
        Route::get('/snaps', [App\Http\Controllers\Api\V1\SnapController::class, 'index']);
        Route::get('/snaps/unseen', [App\Http\Controllers\Api\V1\SnapController::class, 'unseen']);
        Route::get('/snaps/{id}/view', [App\Http\Controllers\Api\V1\SnapController::class, 'view']);
        Route::delete('/snaps/{id}', [App\Http\Controllers\Api\V1\SnapController::class, 'destroy']);

        Route::post('/distance', [App\Http\Controllers\Api\V1\DistanceController::class, 'store']);
        Route::get('/distance', [App\Http\Controllers\Api\V1\DistanceController::class, 'show']);
        Route::get('/distance/history', [App\Http\Controllers\Api\V1\DistanceController::class, 'history']);

        Route::get('/prompts/daily', [App\Http\Controllers\Api\V1\PromptController::class, 'daily']);
        Route::post('/prompts/{id}/answers', [App\Http\Controllers\Api\V1\PromptController::class, 'answer']);
        Route::get('/prompts/history', [App\Http\Controllers\Api\V1\PromptController::class, 'history']);

        Route::get('/subscriptions/current', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'current']);
        Route::post('/payments/initiate', [App\Http\Controllers\Api\V1\PaymentController::class, 'initiate']);
    });
});

