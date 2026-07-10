<?php

namespace App\Providers;

use App\Contracts\Services\CoupleServiceInterface;
use App\Contracts\Services\MoodServiceInterface;
use App\Contracts\Services\NoteServiceInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Contracts\Services\WidgetStateServiceInterface;
use App\Services\Couple\CoupleService;
use App\Services\Mood\MoodService;
use App\Services\Note\NoteService;
use App\Services\Notification\NotificationService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CoupleServiceInterface::class, CoupleService::class);
        $this->app->bind(MoodServiceInterface::class, MoodService::class);
        $this->app->bind(NoteServiceInterface::class, NoteService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(WidgetStateServiceInterface::class, WidgetStateService::class);
    }

    public function boot(): void
    {
        //
    }
}
