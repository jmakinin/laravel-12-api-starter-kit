<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\SMS\SMSChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\SMSChannel as SMSChannelContract;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Schema::defaultStringLength(191);

        // This closure tells Laravel how to create an instance of the SmsChannel
        Notification::extend('sms', function ($app) {
            return $app->make(SMSChannelContract::class);
        });
    }
}
