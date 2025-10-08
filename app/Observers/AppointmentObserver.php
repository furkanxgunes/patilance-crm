<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Events\AppointmentStatusChanged;
use App\Events\AppointmentPaymentStatusChanged;
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
                        // Yeni oluşturulduğunda ödeme durumu bildirimini de tetikle
            // Sadece send_notification_payment_status true ise ve payment_status varsa
            if ($appointment->send_notification_payment_status && $appointment->payment_status !== null) {
                Log::info('AppointmentObserver: Yeni randevu için ödeme durumu bildirimi tetikleniyor.', [
                    'appointment_id' => $appointment->id,
                    'payment_status' => $appointment->payment_status
                ]);
                event(new AppointmentPaymentStatusChanged(
                    $appointment,
                    null, // Yeni olduğu için önceki ödeme durumu yok
                    $appointment->payment_status
                ));
            }
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
             // Ödeme durumu veya bildirim tercihi değiştiğinde (randevu completed ise) yeni eventi tetikle
            // Not: Sadece 'completed' durumdaki randevular için ödeme bildirimi gönderileceği belirtildiği için bu kontrolü ekledim.
            if ($appointment->status === \App\Enums\AppointmentStatus::COMPLETED &&
                ($appointment->isDirty('payment_status') || $appointment->isDirty('send_notification_payment_status'))) {

                // Yalnızca send_notification_payment_status true ise veya payment_status değiştiyse gönder
                if ($appointment->send_notification_payment_status || $appointment->isDirty('payment_status')) {
                    Log::info('AppointmentObserver: Ödeme durumu veya bildirim tercihi değişikliği tespit edildi, AppointmentPaymentStatusChanged event fırlatılıyor.', [
                        'appointment_id' => $appointment->id,
                        'old_payment_status' => $appointment->getOriginal('payment_status'),
                        'new_payment_status' => $appointment->payment_status
                    ]);
                    event(new AppointmentPaymentStatusChanged(
                        $appointment,
                        $appointment->getOriginal('payment_status'),
                        $appointment->payment_status
                    ));
                }
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
