<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use App\Events\AppointmentStatusChanged;
use App\Listeners\SendAppointmentWhatsAppNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // 1) EŞLEMEYİ AÇ
    protected $listen = [
        // AppointmentStatusChanged::class => [
        //     SendAppointmentWhatsAppNotification::class,
        // ],
    ];

    // 2) OBSERVER BAĞLI KALSIN
    public function boot()
    {
        parent::boot(); // Modern Laravel’de genellikle gerekmez; kalsa da sorun değil.
        Appointment::observe(AppointmentObserver::class);
    }
    
}
