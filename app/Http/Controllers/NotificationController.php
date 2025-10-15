<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }
    
    public function unreadCount()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Acil görevleri say (sadece giriş yapmış kullanıcının görevleri)
        $urgentCount = 0;
        $userId = Auth::id();
        
        // 1. Kullanıcıya atanmış ve 2 saat içinde başlayacak görevler
        $urgentCount += Appointment::query()
            ->whereDate('checkin_at', $today)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('checkin_at', '<=', $now->copy()->addHours(2))
            ->whereHas('services', function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            })
            ->count();
        
        // 2. Kullanıcıya atanmış ve check-in bekleyen görevler
        $urgentCount += Appointment::query()
            ->where('status', 'scheduled')
            ->whereDate('checkin_at', '<=', $today)
            ->whereTime('checkin_at', '<=', $now->addMinutes(30)->format('H:i:s'))
            ->whereHas('services', function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            })
            ->count();
        
        return response()->json([
            'count' => $urgentCount
        ]);
    }
    
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
    
    public function fetch()
    {
        $user = Auth::user();
        $userId = $user->id;
        $notifications = collect();
        
        // Bugünün tarihleri
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $now = Carbon::now();
        
        // KULLANICIYA ATANMIŞ GÖREVLER
        
        // 1. BUGÜNKÜ GÖREVLERİM (Tamamlanmamış ve bana atanmış)
        $myTodayTasks = Appointment::query()
            ->whereIn('status', ['scheduled'])
            ->whereHas('services', function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            })
            ->with(['customer', 'pet', 'services' => function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            }])
            ->orderBy('planned_at')
            ->take(3)
            ->get();
            
        foreach ($myTodayTasks as $appointment) {
            $checkin = Carbon::parse($appointment->checkin_at);
            $customerName = $appointment->customer->name ?? 'Müşteri';
            $petName = $appointment->pet->name ?? 'Evcil Hayvan';
            
            // Atanmış hizmetleri al
            $myServices = $appointment->services->pluck('name')->join(', ');
            
            // Randevuya ne kadar kaldı?
            $timeUntil = $checkin->diffForHumans();
            $isUrgent = $checkin->diffInHours($now) <= 2;

            
            $notifications->push([
                'id' => 'my_task_' . $appointment->id,
                'title' => $isUrgent ? '📋 Yaklaşan Randevu': '📋 Yaklaşan Randevu',
                'message' => "{$customerName} - {$petName} • {$myServices} • {$checkin->format('H:i')}",
                'time' => $timeUntil,
                'url' => route('appointments.show', $appointment->id),
                'icon' => $isUrgent ? 'fa-exclamation-circle' : 'fa-tasks',
                'type' => 'my_task',
                'priority' => $isUrgent ? 'normal' : 'normal',
            ]);
        }
        
        // 2. TAMAMLANMAYI BEKLEYEN GÖREVLERİM (Checked-in durumda ve bana atanmış)
        $myPendingTasks = Appointment::query()
            ->where('status', 'checked_in')
            ->whereHas('services', function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            })
            ->with(['customer', 'pet', 'services' => function($query) use ($userId) {
                $query->where('appointment_service.user_id', $userId);
            }])
            ->orderBy('checkin_at')
            ->take(2)
            ->get();
            
        foreach ($myPendingTasks as $appointment) {
            $customerName = $appointment->customer->name ?? 'Müşteri';
            $petName = $appointment->pet->name ?? 'Evcil Hayvan';
            $myServices = $appointment->services->pluck('name')->join(', ');
            
            $notifications->push([
                'id' => 'my_pending_' . $appointment->id,
                'title' => '🔄 Giriş Yapmış Randevu',
                'message' => "{$customerName} - {$petName} • {$myServices}",
                'time' => Carbon::parse($appointment->checkin_at)->diffForHumans(),
                'url' => route('appointments.show', $appointment->id),
                'icon' => 'fa-hourglass-half',
                'type' => 'my_pending',
                'priority' => 'low',
            ]);
        }
        
        // 3. YARININ GÖREVLERİM (Hazırlık için)
        if ($notifications->count() < 5) {
             
            $myTomorrowTasks = Appointment::query()
                ->whereDate('checkin_at', $tomorrow)
                ->whereNotIn('status', ['cancelled'])
                ->whereHas('services', function($query) use ($userId) {
                    $query->where('appointment_service.user_id', $userId);
                })
                ->with(['customer', 'pet', 'services' => function($query) use ($userId) {
                    $query->where('appointment_service.user_id', $userId);
                }])
                ->orderBy('checkin_at')
                ->take(max(0, 5 - $notifications->count()))
                ->get();
                
            foreach ($myTomorrowTasks as $appointment) {
                $checkin = Carbon::parse($appointment->checkin_at);
                $customerName = $appointment->customer->name ?? 'Müşteri';
                $petName = $appointment->pet->name ?? 'Evcil Hayvan';
                $myServices = $appointment->services->pluck('name')->join(', ');
                
                $notifications->push([
                    'id' => 'my_tomorrow_' . $appointment->id,
                    'title' => '📆 Yarınki Görevim',
                    'message' => "{$customerName} - {$petName} • {$myServices} • {$checkin->format('H:i')}",
                    'time' => 'Yarın',
                    'url' => route('appointments.show', $appointment->id),
                    'icon' => 'fa-calendar-alt',
                    'type' => 'my_tomorrow',
                    'priority' => 'low',
                ]);
            }
        }
        
        // 4. GÜNLÜK ÖZET (Eğer hiç görev yoksa)
        if ($notifications->isEmpty()) {
            // Bugün tamamladığım görevler
            $completedToday = Appointment::query()
                ->whereDate('checkin_at', $today)
                ->where('status', 'completed')
                ->whereHas('services', function($query) use ($userId) {
                    $query->where('appointment_service.user_id', $userId);
                })
                ->count();
                
            // Bugün toplam görevlerim
            $totalToday = Appointment::query()
                ->whereDate('checkin_at', $today)
                ->whereHas('services', function($query) use ($userId) {
                    $query->where('appointment_service.user_id', $userId);
                })
                ->count();
                
            if ($totalToday > 0) {
                $notifications->push([
                    'id' => 'my_daily_summary',
                    'title' => '✅ Günlük Özetim',
                    'message' => "Bugün {$completedToday}/{$totalToday} görevimi tamamladım",
                    'time' => 'Bugün',
                    'url' => route('dashboard2', ['start_date' => $today->format('Y-m-d'), 'end_date' => $today->format('Y-m-d')]),
                    'icon' => 'fa-check-circle',
                    'type' => 'my_summary',
                    'priority' => 'low',
                ]);
            } else {
                $notifications->push([
                    'id' => 'no_tasks',
                    'title' => '😊 Görev Yok',
                    'message' => "Bugün için atanmış göreviniz bulunmuyor",
                    'time' => 'Bugün',
                    'url' => route('dashboard2'),
                    'icon' => 'fa-coffee',
                    'type' => 'info',
                    'priority' => 'low',
                ]);
            }
        }
        
        // Önceliğe göre sırala (high -> normal -> low)
        $priorityOrder = ['high' => 1, 'normal' => 2, 'low' => 3];
        $sorted = $notifications->sortBy(function($item) use ($priorityOrder) {
            return $priorityOrder[$item['priority']] ?? 99;
        })->values();
        
        return response()->json($sorted->take(5));
    }
}
