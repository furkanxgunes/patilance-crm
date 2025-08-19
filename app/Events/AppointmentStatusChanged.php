<?php
// app/Events/AppointmentStatusChanged.php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Appointment $appointment;
    public ?\App\Enums\AppointmentStatus $oldStatus;
    public \App\Enums\AppointmentStatus $newStatus;

    public function __construct(
        Appointment $appointment, 
        ?\App\Enums\AppointmentStatus $oldStatus, 
        \App\Enums\AppointmentStatus $newStatus
    ) {
        $this->appointment = $appointment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        
        // Log the event creation
        \Log::info('AppointmentStatusChanged event oluşturuldu', [
            'appointment_id' => $appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }
}