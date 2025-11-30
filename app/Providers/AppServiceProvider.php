<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        
        // Use Passport's vendor migrations (from vendor/laravel/passport/database/migrations)
        // Since we have no OAuth migrations in database/migrations, let Passport use its own
        Passport::ignoreMigrations(false);
    }
}

