@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <div>
                
                    <a href="{{ route('customers.create') }}" class="btn btn-success ml-2 mt-1 mt-md-0 ">
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
    </div>

    <div class="row">
       
        <div class="col-md-5">
            <x-adminlte-card title="Planlanan Randevular" theme="info" icon="fas fa-calendar-plus" collapsible>
                @forelse($scheduledAppointments as $a)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="w-75">
                            <div class="font-weight-bold">{{ $a->customer->name }} @if($a->pet) - {{ $a->pet->name }} @endif</div>
                            <div class="mt-1">
                                @foreach($a->services as $service)
                                    <span class="badge badge-info mr-1 mb-1">{{ $service->name }}</span>
                                @endforeach
                            </div>
                            <small class="text-muted">{{ optional($a->planned_at)->format('d.m.Y H:i') }}</small>
                        </div>
                        <div class="text-nowrap">
                            <a class="btn btn-xs btn-success" href="{{ route('appointments.checkin.form', $a) }}" title="Check-in"><i class="fas fa-sign-in-alt"></i>Check-in</a>
                            <a class="btn btn-xs btn-primary" href="{{ route('appointments.edit', $a) }}" title="Düzenle"><i class="fas fa-edit"></i>Düzenle</a>
                            @if ($a->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.delivery.pdf', $a) }}" title="Teslim Tutanağı"><i class="fas fa-file-pdf"></i>Teslim Tutanağı</a>
                            @elseif ($a->status === \App\Enums\AppointmentStatus::COMPLETED)
                                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.pdf', $a) }}" title="Randevu PDF"><i class="fas fa-file-pdf"></i>Randevu PDF</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Planlanan randevu yok.</p>
                @endforelse
            </x-adminlte-card>

            <x-adminlte-card title="Aktif Randevular" theme="warning" icon="fas fa-user-clock" collapsible>
                @forelse($activeAppointments as $a)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="w-75">
                            <div class="font-weight-bold">{{ $a->customer->name }} @if($a->pet) - {{ $a->pet->name }} @endif</div>
                            <div class="mt-1">
                                @foreach($a->services as $service)
                                    <span class="badge badge-info mr-1 mb-1">{{ $service->name }}</span>
                                @endforeach
                            </div>
                            <small class="text-muted">Check-in: {{ optional($a->checkin_at)->format('d.m.Y H:i') }}</small>
                        </div>
                        <div class="text-nowrap">
                            <a class="btn btn-xs btn-info" href="{{ route('appointments.checkout.form', $a) }}" title="Check-out"><i class="fas fa-sign-out-alt"></i>Check-out</a>
                            @if ($a->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.delivery.pdf', $a) }}" title="Teslim Tutanağı"><i class="fas fa-file-pdf"></i>Teslim Tutanağı</a>
                            @elseif ($a->status === \App\Enums\AppointmentStatus::COMPLETED)
                                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.pdf', $a) }}" title="Randevu PDF"><i class="fas fa-file-pdf"></i>Randevu PDF</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aktif randevu yok.</p>
                @endforelse
            </x-adminlte-card>
            <x-adminlte-card title="Tamamlanan Randevular" theme="success" icon="fas fa-check" collapsible>
                @forelse($completedAppointments as $a)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="w-75">
                            <div class="font-weight-bold">{{ $a->customer->name }} @if($a->pet) - {{ $a->pet->name }} @endif</div>
                            <div class="mt-1">
                                @foreach($a->services as $service)
                                    <span class="badge badge-info mr-1 mb-1">{{ $service->name }}</span>
                                @endforeach
                            </div>
                            <small class="text-muted">Check-in: {{ optional($a->checkin_at)->format('d.m.Y H:i') }}</small>
                        </div>
                        <div class="text-nowrap">
                      
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('appointments.show', $a) }}" title="Randevu Detayları"><i class="fas fa-eye"></i> Detay</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aktif randevu yok.</p>
                @endforelse
            </x-adminlte-card>
        </div>
        <div class="col-md-6">
            <x-adminlte-card title="Randevu Takvimi" theme="primary" icon="fas fa-calendar" collapsible>
                <!-- <div class="d-flex justify-content-between align-items-center mb-3">
                   
                    <div class="service-filters">
                        <div class="btn-group" role="group">
                            @foreach($services as $service)
                                <button type="button" class="btn btn-sm btn-outline-secondary service-filter" 
                                    data-service="{{ $service->id }}">
                                    {{ $service->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div> -->
                <div class="mb-3">
                    @foreach ($colorLegend as $status => $data)
                        <span class="ml-3">
                            <i class="fas fa-square" style="color: {{ $data['color'] }};"></i> 
                            {{ $data['label'] }}
                        </span>
                    @endforeach
                </div>
                <div id="calendar" style="min-height: 400px;"></div>
            </x-adminlte-card>
        </div>
    </div>
    <!-- <div class="row mt-3">
        <div class="col-md-5">
            <x-adminlte-info-box title="Bugünkü Randevular" text="5" icon="fas fa-calendar-check" theme="warning"/>
        </div>
        <div class="col-md-5">
            <x-adminlte-info-box title="Tamamlanan Randevular" text="120" icon="fas fa-calendar-check" theme="primary"/>
        </div>
        <div class="col-md-5">
            <x-adminlte-info-box title="Son 7 Günlük Kazanç" text="15.000 TL" icon="fas fa-money-bill" theme="danger"/>
        </div>
    </div> -->
@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .service-filters {
            max-height: 60px;
            overflow-y: auto;
            white-space: wrap;
            scrollbar-width: thin;
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            padding: 2px 0;
        }
        .service-filters::-webkit-scrollbar {
            height: 5px;
        }
        .service-filters .btn {
            margin: 0;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
            white-space: normal;
            text-align: center;
        }
        .service-filter.active {
            background-color: #17a2b8;
            color: white;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var selectedServices = [];
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'tr',
                initialView: 'dayGridMonth',
                slotMinTime: "08:00:00",     // sabah 8’den başla
                slotMaxTime: "20:00:00",     // akşam 8’de bitir
                height: 'auto',
                events: @json($events),
                eventDidMount: function(info) {
                    // Hizmet filtrelemesi için etkinliklere data attribute ekle
                    if (info.event.extendedProps.services) {
                        info.el.setAttribute('data-services', info.event.extendedProps.services.join(','));
                    }
                }
            });

            // Takvimi render et
            calendar.render();

            // Aktif butonu güncelle
            function updateActiveButton(activeId) {
                document.querySelectorAll('#dayView, #weekView, #monthView').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.getElementById(activeId).classList.add('active');
            }

            // Hizmet filtreleme
            document.querySelectorAll('.service-filter').forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('active');
                    filterEvents();
                });
            });

            // Hizmetlere göre etkinlikleri filtrele
            function filterEvents() {
                selectedServices = [];
                document.querySelectorAll('.service-filter.active').forEach(btn => {
                    selectedServices.push(btn.getAttribute('data-service'));
                });

                if (selectedServices.length === 0) {
                    // Eğer filtre yoksa tüm etkinlikleri göster
                    calendar.getEvents().forEach(event => {
                        event.setProp('display', 'auto');
                    });
                } else {
                    // Seçili hizmetlere göre filtrele
                    calendar.getEvents().forEach(event => {
                        const eventServices = event.extendedProps.services || [];
                        const hasMatchingService = selectedServices.some(serviceId => 
                            event.extendedProps.services && 
                            event.extendedProps.services.includes(parseInt(serviceId))
                        );
                        
                        event.setProp('display', hasMatchingService ? 'auto' : 'none');
                    });
                }
            }

            // Takvim görünümü değiştiğinde boyut ayarlaması yap
            calendar.on('viewDidMount', function() {
                updateCalendarHeight();
            });

            // Pencere boyutu değiştiğinde takvim boyutunu güncelle
            window.addEventListener('resize', function() {
                updateCalendarHeight();
            });

            // Takvim yüksekliğini ayarla
            function updateCalendarHeight() {
                const view = calendar.view;
                if (view.type === 'dayGridMonth') {
                    calendar.setOption('height', 'auto');
                } else {
                    calendar.setOption('height', 'auto');
                }
            }
        });
    </script>
@endpush

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
@endpush