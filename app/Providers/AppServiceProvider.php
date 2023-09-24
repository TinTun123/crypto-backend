<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserBalance;
use App\Observers\UserBalanceObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Validator;
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
        //
        Validator::extend('string_boolean', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['true', 'false'], true);
        });

        User::observe(UserObserver::class);
        UserBalance::observe(UserBalanceObserver::class);

        
    }
}
