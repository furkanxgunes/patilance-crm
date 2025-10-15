@extends('adminlte::page')

@section('title', 'Randevu Takvimi')

@section('content_header')
    <h1>Randevu Takvimi</h1>
@stop

@section('content_top_nav_right')
    <!-- /<x-notification-dropdown :user="auth()->user()" /> -->
@stop
  
@section('content')
<style>
        /* Simple pagination styles */
        .pagination {
            justify-content: center;
            margin: 1rem 0;
        
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
            text-align: center;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #3490dc;
            border-color: #3490dc;
        }
    </style>
    <!-- <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('customers.create') }}" class="btn btn-success ml-2 mt-1 mt-md-0">
                        <i class="fas fa-user-plus"></i> Müşteri Ekle
                    </a>
                    <a href="{{ route('pets.create') }}" class="btn btn-info ml-2 mt-1 mt-md-0">
                        <i class="fas fa-paw"></i> Evcil Hayvan Ekle
                    </a>
                    <a href="{{ route('appointments.create') }}" class="btn btn-warning ml-2 mt-1 mt-md-0">
                        <i class="fas fa-calendar-plus"></i> Randevu Oluştur
                    </a>
                </div>
            </div>
        </div>
    </div> -->
   <div class="row mb-3">
   
   <!-- Filtreleme -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column">
                        <!-- Tarih Kısayolları -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('dashboard', ['start_date' => $yesterday, 'end_date' => $yesterday, 'category' => $category]) }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-chevron-left"></i> 
                                </a>
                                <a href="{{ route('dashboard', ['start_date' => $today, 'end_date' => $today, 'category' => $category]) }}" 
                                   class="btn {{ $startDate === $today && $endDate === $today ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                    Bugün
                                </a>
                                <a href="{{ route('dashboard', ['start_date' => $tomorrow, 'end_date' => $tomorrow, 'category' => $category]) }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                     <i class="fas fa-chevron-right"></i>
                                </a>
                                @php
                                    $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                                    $endOfWeek = now()->endOfWeek()->format('Y-m-d');
                                    $isCurrentWeek = $startDate === $startOfWeek && $endDate === $endOfWeek;
                                    
                                    $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                                    $endOfMonth = now()->endOfMonth()->format('Y-m-d');
                                    $isCurrentMonth = $startDate === $startOfMonth && $endDate === $endOfMonth;
                                @endphp
                                <a href="{{ route('dashboard', ['start_date' => $startOfWeek, 'end_date' => $endOfWeek, 'category' => $category]) }}" 
                                   class="ml-2 btn {{ $isCurrentWeek ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                    Bu Hafta
                                </a>
                                <a href="{{ route('dashboard', ['start_date' => $startOfMonth, 'end_date' => $endOfMonth, 'category' => $category]) }}" 
                                   class="ml-1 btn {{ $isCurrentMonth ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                    {{ now()->translatedFormat('F') }}
                                </a>
                            </div>
                        </div>
                        
                        <!-- Tarih Filtreleme -->
                        <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
                            <div class="d-flex  flex-wrap align-items-center">
                                <div class="input-group input-group-sm mr-2 mb-2 ">
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" 
                                           title="Başlangıç Tarihi" onchange="document.getElementById('endDate').min = this.value;">
                                    <div class="input-group-append">
                                        <span class="input-group-text">-</span>
                                    </div>
                                    <input type="date" name="end_date" id="endDate" class="form-control" value="{{ $endDate }}" 
                                           title="Bitiş Tarihi" min="{{ $startDate }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <select name="category" class="form-control" onchange="this.form.submit()">
                                        <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Tüm Kategoriler</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>

   <!-- Özet İstatistikler -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-white" data-toggle="collapse" data-target="#statisticsCollapse" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @php
                                // Hizmet türlerine ve kategorilere göre sayım yap
                                $serviceCounts = [];
                                $categoryCounts = [];
                                $processedAppointments = []; // Aynı randevuyu birden fazla saymamak için
                                
                                foreach($appointments as $appointment) {
                                    $appointmentCategories = [];
                                    
                                    foreach($appointment->services as $service) {
                                        // Hizmet sayıları
                                        if (!isset($serviceCounts[$service->name])) {
                                            $serviceCounts[$service->name] = 0;
                                        }
                                        $serviceCounts[$service->name]++;
                                        
                                        // Kategori sayıları (her randevu için bir kez sayılacak)
                                        if (!empty($service->category) && !in_array($service->category, $appointmentCategories)) {
                                            if (!isset($categoryCounts[$service->category])) {
                                                $categoryCounts[$service->category] = 0;
                                            }
                                            $categoryCounts[$service->category]++;
                                            $appointmentCategories[] = $service->category;
                                        }
                                    }
                                }
                                
                                // Özet metnini oluştur
                                $summary = 'Toplam Randevu: ' . $appointments->total();
                                
                            @endphp
                            @php
                                // Tarih formatlama için Carbon kullanımı
                                $startDateObj = \Carbon\Carbon::parse($startDate)->locale('tr');
                                $endDateObj = \Carbon\Carbon::parse($endDate)->locale('tr');
                                $dateRange = $startDateObj->translatedFormat('d F') . ' (' . $startDateObj->translatedFormat('l') . ')';
                                
                                if ($startDate !== $endDate) {
                                    $dateRange .= ' - ' . $endDateObj->translatedFormat('d F') . ' (' . $endDateObj->translatedFormat('l') . ')';
                                }
                            @endphp
                            <h5 class="mb-2">
                                <i class="fas fa-chart-pie mr-2"></i>Özet <small class="text-muted">{{ $dateRange }}</small>

                                <br><span class="badge badge-success mt-3 bagde">{{ $summary }}</span>
                            </h5>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse" id="statisticsCollapse">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="mr-1 mb-2">
                                <span class="badge badge-primary p-2">
                                    <i class="fas fa-calendar-alt "></i>
                                    Toplam Randevu: <strong>{{ $appointments->total() }}</strong>
                                </span>
                            </div>
                            
                            @php
                                // Renk listesi
                                $badgeColors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                $colorIndex = 0;
                            @endphp
                            
                            @foreach($serviceCounts as $serviceName => $count)
                                <div class="mr-1 mb-2">
                                    <span class="badge badge-{{ $badgeColors[$colorIndex % count($badgeColors)] }}">
                                        {{ $serviceName }}: <strong>{{ $count }}</strong>
                                    </span>
                                </div>
                                @php $colorIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" data-toggle="collapse" data-target="#appointmentsCollapse" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                        <small> <i class="fas fa-calendar-alt mr-2"></i>
                           {{ $dateRange }} Tarihli Randevular</small>
                            <span class="badge badge-primary ml-2">{{ $appointments->total() }}</span>
                        </h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse show" id="appointmentsCollapse">
                    <div class="card-body p-0">
                        @if($appointments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                 
                                    <tbody>

                                        @foreach($appointments as $appointment)
                                            @php
                                                $entryDate = $appointment->checkin_at ?? $appointment->planned_at;
                                                $exitDate = $appointment->checkout_at ?? $appointment->planned_exit;                                   
                                            @endphp
                                            @php
                                                        $colorStyle = [
                                                            'scheduled' => '#ffbe471c',
                                                            'checked_in' => '#adff2f29',
                                                            'completed' => '#4ab6011c',
                                                            'cancelled' => '#ff000059'
                                                        ][$appointment->status->value] ?? 'transparent';
                                                        
                                                        $colorText = [
                                                            'scheduled' => 'text-gray',
                                                            'checked_in' => 'text-green',
                                                            'completed' => 'text-blue',
                                                            'cancelled' => 'text-red'
                                                        ][$appointment->status->value] ?? $appointment->status->value;
                                                        $badgeStyle = [
                                                            'scheduled' => 'warning',
                                                            'checked_in' => 'success',
                                                            'completed' => 'primary',
                                                            'cancelled' => 'danger'
                                                        ][$appointment->status->value] ?? 'secondary';
                                                    @endphp
                                            <tr style="background-color: {{ $colorStyle }}">
                                            <td>
                                                <div class="row">
                                                    <div class="col-6">
                                                    <span class="font-weight-bold {{ $colorText }}">{{ $appointment->customer->name ?? 'Müşteri Yok' }}</span>
                                                    <span class="font-weight-bold {{ $colorText }}"> - {{ $appointment->pet->name ?? 'Evcil Hayvan Yok ' }} |</span> 
                                                    @foreach($appointment->services as $service)
                                                    @if ($appointment->services->count() == 1)
                                                        <strong><small class="{{ $colorText }}">({{ $service->name }})</small></strong>
                                                    @elseif ($loop->last)
                                                        <strong><small class="{{ $colorText }}">{{ $service->name }})</small></strong>
                                                    @elseif ($loop->first)
                                                        <strong><small class="{{ $colorText }}">({{ $service->name }},</small></strong>
                                                    @else
                                                        <strong><small class="{{ $colorText }}">{{ $service->name }},</small></strong>
                                                    @endif

                                                    
                                                    @endforeach
                                                  </div>
                                                    <div class="col-6">
                                                    <div class="dateRange">
                                                    @php
                                                        // Use checkin_at if available, otherwise use planned_at
                                                        $entryDate = $appointment->checkin_at 
                                                            ? \Carbon\Carbon::parse($appointment->checkin_at)
                                                            : \Carbon\Carbon::parse($appointment->planned_at);
                                                            
                                                        // Use checkout_at if available, otherwise use planned_exit if it exists
                                                        $exitDate = $appointment->checkout_at 
                                                            ? \Carbon\Carbon::parse($appointment->checkout_at)
                                                            : ($appointment->planned_exit ? \Carbon\Carbon::parse($appointment->planned_exit) : null);
                                                        
                                                        // Set Turkish locale for day names
                                                        \Carbon\Carbon::setLocale('tr');
                                                        
                                                        if ($exitDate) {
                                                            if ($entryDate->isSameDay($exitDate)) {
                                                                // Same day format: 15 Ekim Salı 17:00-20:00
                                                                echo "<small><badge class='badge badge-$badgeStyle'>   ".$entryDate->isoFormat('D MMMM dddd HH:mm') . ' - ' . $exitDate->format('H:i')."</badge></small>";
                                                            } else {
                                                                // Different days format: 15 Ekim Salı 17:00 - 17 Ekim Cuma 20:00
                                                                echo "<small><badge class='ml-1 badge badge-$badgeStyle '>".$entryDate->isoFormat('D MMMM dddd HH:mm') . "</badge></small>"."<small><badge class='ml-1 badge badge-$badgeStyle'>".$exitDate->isoFormat("D MMMM dddd HH:mm")."</badge></small>";
                                                            }
                                                        } else {
                                                            // No exit date, just show entry date
                                                            echo "<small><badge class='badge badge-$badgeStyle'>".$entryDate->isoFormat('D MMMM dddd HH:mm')."</badge></small>";
                                                        }
                                                    @endphp
                                                    </div>
                                                </div>
                                               
                                                   
                                           <div class="col-6">
                                                    @if($appointment->notes)
                                                    <small class="{{ $colorText }} ">
                                                        <i class="fas fa-info-circle " 
                                                           data-toggle="tooltip" 
                                                           title="{{ $appointment->notes }}" 
                                                           style="cursor: pointer;">
                                                        </i>
                                                         {{$appointment->notes}}
                                                         </small>
                                                    @endif
                                            </div>
                                            <div class="col-12 mt-4">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="btn btn-xs  btn-{{$badgeStyle}}" title="Detay">
                                                        <i class="fas fa-eye"></i> Detay
                                                    </a>
                                                    
                                                    @if($appointment->status->value === 'scheduled')
                                                            <a href="{{ route('appointments.checkin', $appointment) }}" class="btn btn-xs btn-success" title="Check-in Yap">
                                                                <i class="fas fa-sign-in-alt"></i> Check-in
                                                            </a>
                                                    @elseif($appointment->status->value === 'checked_in')
                                                            <a href="{{ route('appointments.checkout', $appointment) }}" class="btn btn-xs btn-info" title="Checkout Yap">
                                                                <i class="fas fa-sign-out-alt"></i> Checkout
                                                    </a>
                                                    @endif
                                            </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer clearfix">
                                <div class="float-right">
                                    @if($appointments->hasPages())
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $appointments->onEachSide(1)->links('pagination::bootstrap-4') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center p-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Seçilen tarih ve filtre kriterlerine uygun randevu bulunamadı.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Yeni Personel Tablosu -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" data-toggle="collapse" data-target="#staffTasksCollapse" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <small><i class="fas fa-users mr-2"></i>Personeller ve Görevleri</small>
                            <small class="text-muted">{{ $dateRange }}</small>
                        </h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse show" id="staffTasksCollapse">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Personel</th>
                                        <th>Görevler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Tüm personelleri al
                                        $staffMembers = \App\Models\User::all();
                                        
                                        // Her personel için atanmış hizmetleri ve randevu detaylarını topla
                                        $userServices = [];
                                        $userAppointments = [];
                                        
                                        foreach($appointments as $appointment) {
                                            foreach($appointment->services as $service) {
                                                if ($service->pivot->user_id) {
                                                    $userId = $service->pivot->user_id;
                                                    
                                                    // Hizmet sayıları için
                                                    if (!isset($userServices[$userId][$service->name])) {
                                                        $userServices[$userId][$service->name] = 0;
                                                    }
                                                    $userServices[$userId][$service->name]++;
                                                    
                                                    // Randevu detayları için
                                                    $userAppointments[$userId][] = [
                                                        'appointment_id' => $appointment->id,
                                                        'service_name' => $service->name,
                                                        'customer_name' => $appointment->customer->name ?? 'Müşteri Yok',
                                                        'pet_name' => $appointment->pet->name ?? 'Evcil Hayvan Yok',
                                                        'status' => $appointment->status->value,
                                                        'entry_date' => $appointment->checkin_at ?? $appointment->planned_at,
                                                        'exit_date' => $appointment->checkout_at ?? $appointment->planned_exit,
                                                        'notes' => $appointment->notes
                                                    ];
                                                }
                                            }
                                        }
                                    @endphp

                                    @foreach($staffMembers as $user)
                                        @if(isset($userServices[$user->id]) || $user->role === 'staff' || $user->role === 'admin')
                                            <tr data-toggle="collapse" data-target="#details-{{ $user->id }}" class="accordion-toggle">
                                                <td>
                                                    <strong class="text-primary" style="cursor: pointer;">
                                                        <i class="fas fa-user mr-1"></i> {{ $user->name }}
                                                        <small class="text-muted">({{ count($userAppointments[$user->id] ?? []) }} işlem)</small>
                                                    </strong>
                                                </td>
                                                <td>
                                                    @if(isset($userServices[$user->id]) && count($userServices[$user->id]) > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($userServices[$user->id] as $serviceName => $count)
                                                                <span class="badge badge-info mr-md-1 mb-1">{{ $serviceName }} x{{ $count }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Atanmış görev yok</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            
                                            <!-- Detay Satırı -->
                                            @if(isset($userAppointments[$user->id]))
                                                <tr class="hiddenRow">
                                                    <td colspan="2" class="p-0">
                                                        <div class="collapse" id="details-{{ $user->id }}">
                                                            <div class="p-3 bg-light">
                                                                <h5 class="mb-3">{{ $user->name }} - İşlem Detayları</h5>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead class="bg-light">
                                                                            <tr>
                                                                                <th>Hizmet</th>
                                                                                <th>Evcil Hayvan</th>
                                                                                <th>Durum</th>
                                                                                <th>Tarih/Saat</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($userAppointments[$user->id] as $appt)
                                                                                <tr>
                                                                                    <td>{{ $appt['service_name'] }}</td>
                                                                                    <td>{{ $appt['pet_name'] }}</td>
                                                                                    <td>
                                                                                        @php
                                                                                            $badgeClass = [
                                                                                                'scheduled' => 'warning',
                                                                                                'checked_in' => 'success',
                                                                                                'completed' => 'info',
                                                                                                'cancelled' => 'danger'
                                                                                            ][$appt['status']] ?? 'secondary';
                                                                                            
                                                                                            $statusText = [
                                                                                                'scheduled' => 'Planlandı',
                                                                                                'checked_in' => 'Giriş Yapmış',
                                                                                                'completed' => 'Tamamlandı',
                                                                                                'cancelled' => 'İptal Edildi'
                                                                                            ][$appt['status']] ?? $appt['status'];
                                                                                        @endphp
                                                                                        <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                                                                                    </td>
                                                                                    <td>
                                                                                        @if($appt['entry_date'])
                                                                                            {{ $appt['entry_date']->locale('tr')->isoFormat('DD MMMM dddd HH:mm') }}
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 90%;
        }
        .btn-group > .btn {
            flex: 1;
            white-space: nowrap;
        }
        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
            }
            .btn-group > .btn {
                border-radius: 0;
                margin: 1px 0;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(function () {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip({
                placement: 'top',
                html: true
            });
        });
    </script>
@endpush