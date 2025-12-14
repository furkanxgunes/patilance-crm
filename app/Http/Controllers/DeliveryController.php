<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DeliveryToken;
use App\Http\Controllers\AppointmentController;

class DeliveryController extends Controller
{
    protected $appointmentController;

    public function __construct(AppointmentController $appointmentController)
    {
        $this->appointmentController = $appointmentController;
    }

    public function showPdf($appointmentId, $token)
    {
        // Token'ı doğrula
        $validToken = DeliveryToken::where('appointment_id', $appointmentId)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$validToken) {
            abort(404, 'Geçersiz veya süresi dolmuş bağlantı');
        }

        // Randevuyu bul
        $appointment = Appointment::findOrFail($appointmentId);
        
        if($appointment->status->value == 'checked_in') {
            return $this->appointmentController->deliveryPdf($appointment);
        }
        
        if($appointment->status->value == 'completed') {
            return $this->appointmentController->pdf($appointment);
        }

        return 0;
        
    }
}