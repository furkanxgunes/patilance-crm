<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use Illuminate\Http\Request;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index_old()
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
        $services = \App\Models\Service::orderBy('name')->withTrashed()->get();

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

        $completedAppointments = Appointment::with(['customer', 'pet', 'services' => function($query) {
            $query->withTrashed();
        }])
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

    public function index_new(Request $request)
    {
        // Filtre parametrelerini al
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        // Tüm hizmet kategorilerini getir
        $categories = \App\Models\Service::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Ana sorgu
        $query = Appointment::with(['customer', 'pet', 'services'])
            ->where(function($q) use ($startDate, $endDate) {
                // Tarih aralığını sonraki günün başına ayarla (00:00:00)
                $start = $startDate . ' 00:00:00';
                $end = $endDate . ' 23:59:59';
                
                $q->where(function($q) use ($start, $end) {
                    // Durum 1: Giriş tarihi aralık içinde
                    $q->where(function($q) use ($start, $end) {
                        $q->whereNotNull('checkin_at')
                          ->whereBetween('checkin_at', [$start, $end]);
                    })->orWhere(function($q) use ($start, $end) {
                        $q->whereNull('checkin_at')
                          ->whereBetween('planned_at', [$start, $end]);
                    });
                })->orWhere(function($q) use ($start, $end) {
                    // Durum 2: Çıkış tarihi aralık içinde
                    $q->whereNotNull('checkout_at')
                      ->whereBetween('checkout_at', [$start, $end]);
                })->orWhere(function($q) use ($start, $end) {
                    $q->whereNull('checkout_at')
                      ->whereBetween('planned_exit', [$start, $end]);
                })->orWhere(function($q) use ($start, $end) {
                    // Durum 3: Randevu aralığı seçilen aralığı tamamen kapsıyorsa
                    $q->where(function($q) use ($start) {
                        $q->where(function($q) use ($start) {
                            $q->whereNotNull('checkin_at')
                              ->where('checkin_at', '<=', $start);
                        })->orWhere(function($q) use ($start) {
                            $q->whereNull('checkin_at')
                              ->where('planned_at', '<=', $start);
                        });
                    })->where(function($q) use ($end) {
                        $q->where(function($q) use ($end) {
                            $q->whereNotNull('checkout_at')
                              ->where('checkout_at', '>=', $end);
                        })->orWhere(function($q) use ($end) {
                            $q->whereNull('checkout_at')
                              ->where('planned_exit', '>=', $end);
                        });
                    });
                });
            })
            ->orderByRaw("
            CASE
                WHEN status = 'checked_in' THEN 1
                WHEN status = 'scheduled' THEN 2
                WHEN status = 'completed' THEN 3
                WHEN status = 'cancelled' THEN 4
                ELSE 5
            END ASC,
            CASE
                WHEN status IN ('checked_in', 'scheduled') THEN COALESCE(checkin_at, planned_at)
                ELSE checkout_at
            END ASC
        ");

        // Sayfalama ile sonuçları al
        $appointments = $query->paginate(15)->withQueryString();
        
        // Önceki ve sonraki günler için hesaplamalar
        $date = \Carbon\Carbon::parse($startDate);
        $yesterday = $date->copy()->subDay()->format('Y-m-d');
        $tomorrow = $date->copy()->addDay()->format('Y-m-d');
        $today = now()->format('Y-m-d');

      
        return view('dashboard2', compact(
            'appointments',
            'categories',
            'startDate',
            'endDate',
            'yesterday',
            'tomorrow',
            'today',
        ));
    }

    public function index(Request $request)
    {
        // Filtre parametrelerini al
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $category = $request->input('category', 'all');

        // Tüm hizmet kategorilerini getir
        $categories = \App\Models\Service::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Ana sorgu
        $query = Appointment::with(['customer', 'pet', 'services'])
            ->where(function($q) use ($startDate, $endDate) {
                // Tarih aralığını sonraki günün başına ayarla (00:00:00)
                $start = $startDate . ' 00:00:00';
                $end = $endDate . ' 23:59:59';
                
                $q->where(function($q) use ($start, $end) {
                    // Durum 1: Giriş tarihi aralık içinde
                    $q->where(function($q) use ($start, $end) {
                        $q->whereNotNull('checkin_at')
                          ->whereBetween('checkin_at', [$start, $end]);
                    })->orWhere(function($q) use ($start, $end) {
                        $q->whereNull('checkin_at')
                          ->whereBetween('planned_at', [$start, $end]);
                    });
                })->orWhere(function($q) use ($start, $end) {
                    // Durum 2: Çıkış tarihi aralık içinde
                    $q->whereNotNull('checkout_at')
                      ->whereBetween('checkout_at', [$start, $end]);
                })->orWhere(function($q) use ($start, $end) {
                    $q->whereNull('checkout_at')
                      ->whereBetween('planned_exit', [$start, $end]);
                })->orWhere(function($q) use ($start, $end) {
                    // Durum 3: Randevu aralığı seçilen aralığı tamamen kapsıyorsa
                    $q->where(function($q) use ($start) {
                        $q->where(function($q) use ($start) {
                            $q->whereNotNull('checkin_at')
                              ->where('checkin_at', '<=', $start);
                        })->orWhere(function($q) use ($start) {
                            $q->whereNull('checkin_at')
                              ->where('planned_at', '<=', $start);
                        });
                    })->where(function($q) use ($end) {
                        $q->where(function($q) use ($end) {
                            $q->whereNotNull('checkout_at')
                              ->where('checkout_at', '>=', $end);
                        })->orWhere(function($q) use ($end) {
                            $q->whereNull('checkout_at')
                              ->where('planned_exit', '>=', $end);
                        });
                    });
                });
            })
            ->orderByRaw("
            CASE
                WHEN status = 'checked_in' THEN 1
                WHEN status = 'scheduled' THEN 2
                WHEN status = 'completed' THEN 3
                WHEN status = 'cancelled' THEN 4
                ELSE 5
            END ASC,
            CASE
                WHEN status IN ('checked_in', 'scheduled') THEN COALESCE(checkin_at, planned_at)
                ELSE checkout_at
            END ASC
        ");

        // Kategori filtresini uygula
        if ($category !== 'all' && $categories->contains($category)) {
            $query->whereHas('services', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        // Sayfalama ile sonuçları al
        $appointments = $query->paginate(15)->withQueryString();
        
        // Önceki ve sonraki günler için hesaplamalar
        $date = \Carbon\Carbon::parse($startDate);
        $yesterday = $date->copy()->subDay()->format('Y-m-d');
        $tomorrow = $date->copy()->addDay()->format('Y-m-d');
        $today = now()->format('Y-m-d');

      
        return view('dashboard2', compact(
            'appointments',
            'categories',
            'startDate',
            'endDate',
            'yesterday',
            'tomorrow',
            'today',
            'category',
        ));
    }


    /**
     * Get appointment count for a specific status and category
     */
    private function getAppointmentCount($date, $status, $category = 'all')
    {
        $query = Appointment::whereDate('planned_at', $date);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($category !== 'all') {
            $query->whereHas('services', function($q) use ($category) {
                $q->where('category', $category);
            });
        }
        
        return $query->count();
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