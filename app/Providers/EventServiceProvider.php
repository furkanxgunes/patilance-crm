<?php 
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use App\Events\AppointmentStatusChanged;
use App\Listeners\SendAppointmentWhatsAppNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AppointmentStatusChanged::class => [
            SendAppointmentWhatsAppNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}