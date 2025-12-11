<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
    public function boot()
    {
        DB::statement("SET time_zone = '+03:00'");
        \App::setLocale('tr');
        Carbon::setLocale('tr');
        setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'tr-TR', 'tr', 'turkish');
        
        if (env('APP_ENV') === 'production') {
        URL::forceScheme('https');
    }
    }
}
