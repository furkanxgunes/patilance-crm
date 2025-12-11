<?php

// app/Providers/NetGSMServiceProvider.php

namespace App\Providers;

use App\Services\NetGSMService;
use Illuminate\Support\ServiceProvider;

class NetGSMServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(NetGSMService::class, function ($app) {
            return new NetGSMService();
        });
    }

    public function boot()
    {
        //
    }
}