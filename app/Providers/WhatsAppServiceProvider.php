<?php

// app/Providers/WhatsAppServiceProvider.php

namespace App\Providers;

use App\Services\WhatsAppService;
use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService();
        });
    }

    public function boot()
    {
        //
    }
}