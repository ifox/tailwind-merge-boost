<?php

namespace App\Providers;

use App\Services\TailwindMergeBoost;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TailwindMergeBoost::class, function () {
            return new TailwindMergeBoost();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
