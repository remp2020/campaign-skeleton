<?php

namespace App\Providers;

use App\Observers\CampaignObserver;
use Illuminate\Support\ServiceProvider;
use Remp\CampaignModule\Campaign;

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
        Campaign::observe(CampaignObserver::class);
    }
}
