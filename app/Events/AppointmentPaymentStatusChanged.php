<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentPaymentStatusChanged
{
    use Dispatchable, SerializesModels;

    public Appointment $appointment;
    public ?bool $oldPaymentStatus; // null if newly created or first payment status change
    public bool $newPaymentStatus;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Appointment $appointment, ?bool $oldPaymentStatus, bool $newPaymentStatus)
    {
        $this->appointment = $appointment;
        $this->oldPaymentStatus = $oldPaymentStatus;
        $this->newPaymentStatus = $newPaymentStatus;
    }
}