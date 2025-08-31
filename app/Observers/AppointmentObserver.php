<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Events\AppointmentStatusChanged;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    protected static $processing = [];

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment)
    {
        // Check if we're already processing this appointment
        if (isset(self::$processing[$appointment->id])) {
            return;
        }

        self::$processing[$appointment->id] = true;

        try {
            Log::info('AppointmentObserver: created tetiklendi', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status
            ]);

            // Only fire the event for new appointments
            event(new AppointmentStatusChanged(
                $appointment,
                null, // No previous status for new appointments
                $appointment->status
            ));
        } finally {
            // Clean up
            unset(self::$processing[$appointment->id]);
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment)
    {
        // Skip if we're already processing this appointment
        if (isset(self::$processing[$appointment->id])) {
            return;
        }

        self::$processing[$appointment->id] = true;

        try {
            Log::info('AppointmentObserver: updated tetiklendi', [
                'appointment_id' => $appointment->id,
                'old_status' => $appointment->getOriginal('status'),
                'new_status' => $appointment->status,
                'is_dirty_status' => $appointment->isDirty('status')
            ]);

            if ($appointment->wasChanged('status')) {
                Log::info('AppointmentObserver: Status değişikliği tespit edildi, event fırlatılıyor');
                event(new AppointmentStatusChanged(
                    $appointment,
                    $appointment->getOriginal('status'),
                    $appointment->status
                ));
            }
        } finally {
            // Clean up
            unset(self::$processing[$appointment->id]);
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
