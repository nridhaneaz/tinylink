<?php

namespace App\Providers;

use App\Models\Url;
use App\Policies\UrlPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Explicitly register the Url policy
        Gate::policy(Url::class, UrlPolicy::class);
    }
}
