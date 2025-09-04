<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use Illuminate\Http\Request;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        // Randevuları ilgili modellerle birlikte çekin
        $appointments = Appointment::with(['customer', 'pet', 'services'])->get();

        // Takvim etkinlikleri: planlananlar için planned_at, diğerleri için checkin_at kullan
        $events = [];
        foreach ($appointments as $appointment) {
            $start = $appointment->status->value === AppointmentStatus::SCHEDULED->value
                ? $appointment->planned_at
                : $appointment->checkin_at;

            $events[] = [
                'id' => $appointment->id,
                'title' =>  $appointment->pet->name ?? 'Pet Yok',
                'start' => $start,
                'end' => $appointment->checkout_at ?? $appointment->planned_exit,
                'url' => route('appointments.show', $appointment),
                'backgroundColor' => $this->getEventColor($appointment->status->value),
                'borderColor' => $this->getEventColor($appointment->status->value),
                'extendedProps' => [
                    'customer_name' => $appointment->customer->name,
                    'pet_name' => $appointment->pet->name ?? 'Yok',
                    'services' => $appointment->services->pluck('name')->toArray(),
                    'status' => $appointment->status->value,
                ],
            ];
        }

        // Tüm hizmetleri çek (filtreleme için)
        $services = \App\Models\Service::orderBy('name')->get();

        // Dashboard kartları için listeler (hizmetlerle birlikte)
        $scheduledAppointments = Appointment::with(['customer', 'pet', 'services'])
            ->where('status', AppointmentStatus::SCHEDULED)
            ->orderBy('planned_at', 'asc')
            ->limit(8)
            ->get();

        $activeAppointments = Appointment::with(['customer', 'pet', 'services'])
            ->where('status', AppointmentStatus::CHECKED_IN)
            ->orderBy('checkin_at', 'desc')
            ->limit(8)
            ->get();

        $completedAppointments = Appointment::with(['customer', 'pet', 'services'])
            ->where('status', AppointmentStatus::COMPLETED)
            ->orderBy('checkout_at', 'desc')
            ->limit(5)
            ->get();

        // Renk efsanesi
        $colorLegend = [
            'scheduled' => ['color' => '#28a745', 'label' => 'Planlanmış'],
            'checked_in' => ['color' => '#ffc107', 'label' => 'Check-in Yapılmış'],
            'completed' => ['color' => '#007bff', 'label' => 'Tamamlandı'],
            'cancelled' => ['color' => '#dc3545', 'label' => 'İptal Edildi'],
        ];

        return view('dashboard', compact('events', 'colorLegend', 'scheduledAppointments', 'activeAppointments', 'services', 'completedAppointments'));
    }

    private function getEventColor(string $status): string
    {
        return match ($status) {
            'scheduled' => '#28a745', // Yeşil (Planlandı)
            'checked_in' => '#ffc107', // Sarı (Giriş Yaptı)
            'completed' => '#007bff', // Mavi (Tamamlandı)
            'cancelled' => '#dc3545', // Kırmızı (İptal Edildi)
            default => '#6c757d', // Gri (Varsayılan)
        };
    }
}