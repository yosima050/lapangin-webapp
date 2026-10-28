<?php

namespace App\Providers;

use App\Listeners\MergeGuestOrdersOnRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            config('app.env') === 'production'
            || config('app.env') === 'staging'
            || str_starts_with((string) config('app.url'), 'https://')
        ) {
            URL::forceScheme('https');
        }

        Event::listen(
            Registered::class,
            MergeGuestOrdersOnRegister::class,
        );
    }
}
