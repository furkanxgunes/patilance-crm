@forelse($appointments as $appointment)
    <div class="appointment-item d-flex justify-content-between align-items-center border-bottom py-2">
        <div class="w-75">
            <div class="font-weight-bold">
                {{ $appointment->customer->name }}
                @if($appointment->pet)
                    - {{ $appointment->pet->name }}
                    @if($appointment->pet->breed)
                        <small class="text-muted">({{ $appointment->pet->breed->name }})</small>
                    @endif
                @endif
            </div>
            <div class="mt-1">
                @foreach($appointment->services as $service)
                    <span class="badge badge-info mr-1 mb-1">{{ $service->name }}</span>
                @endforeach
            </div>
            <small class="text-muted">
                <i class="far fa-clock"></i> {{ $appointment->planned_at->format('d.m.Y H:i') }}
                @if($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN->value)
                    <span class="badge badge-warning ml-2">
                        <i class="fas fa-sign-in-alt"></i> Check-in Yapıldı
                    </span>
                @endif
            </small>
        </div>
        <div class="text-nowrap">
            @if($appointment->status === \App\Enums\AppointmentStatus::SCHEDULED->value)
                <a class="btn btn-xs btn-success" href="{{ route('appointments.checkin.form', $appointment) }}" title="Check-in">
                    <i class="fas fa-sign-in-alt"></i> Check-in
                </a>
                <a class="btn btn-xs btn-primary" href="{{ route('appointments.edit', $appointment) }}" title="Düzenle">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
            @elseif($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN->value)
                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.delivery.pdf', $appointment) }}" title="Teslim Tutanağı">
                    <i class="fas fa-file-pdf"></i> Teslim Tutanağı
                </a>
            @elseif($appointment->status === \App\Enums\AppointmentStatus::COMPLETED->value)
                <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('appointments.pdf', $appointment) }}" title="Randevu PDF">
                    <i class="fas fa-file-pdf"></i> Randevu PDF
                </a>
            @endif
        </div>
    </div>
@empty
    <div class="text-center p-4">
        <i class="far fa-calendar-times fa-3x text-muted mb-3"></i>
        <p class="text-muted">Belirtilen tarih aralığında randevu bulunamadı.</p>
    </div>
@endforelse
