@extends('adminlte::page')

@section('title', 'Randevu Detayları')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Randevu Detayları</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Randevular</a></li>
                <li class="breadcrumb-item active" aria-current="page">#{{ $appointment->id }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-md-10 mx-auto">
            <x-adminlte-card title="Randevu #{{ $appointment->id }}" theme="primary" icon="fas fa-calendar-check" collapsible>

                <div class="row">
                    {{-- Müşteri Bilgileri --}}
                    <div class="col-md-6">
                        <h4>Müşteri Bilgileri</h4>
                        <p><strong>Adı Soyadı:</strong> <a href="{{ route('customers.show', $appointment->customer) }}">{{ $appointment->customer->name }}</a></p>
                        <p><strong>Telefon:</strong> {{ $appointment->customer->phone }}</p>
                        <p><strong>Adres:</strong> {{ $appointment->customer->address }}</p>
                    </div>

                    {{-- Evcil Hayvan Bilgileri --}}
                    <div class="col-md-6">
                        <h4>Evcil Hayvan Bilgileri</h4>
                        @if ($appointment->pet)
                            <p><strong>Adı:</strong> <a href="{{ route('pets.edit', $appointment->pet) }}">{{ $appointment->pet->name }}</a></p>
                            <p><strong>Türü:</strong> {{ $appointment->pet->species }}</p>
                            <p><strong>Cinsiyeti:</strong> {{ $appointment->pet->gender }}</p>
                        @else
                            <p class="text-danger">Bu randevuya atanmış bir evcil hayvan bulunmamaktadır.</p>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="row">
                    {{-- Randevu Bilgileri --}}
                    <div class="col-md-6">
                        <h4>Randevu Bilgileri</h4>
                        <p><strong>Planlanan:</strong> {{ optional($appointment->planned_at)->format('d.m.Y H:i') ?? '-' }}</p>
                        <p><strong>Check-in:</strong> {{ optional($appointment->checkin_at)->format('d.m.Y H:i') ?? '-' }}</p>
                        <p><strong>Check-out:</strong> {{ optional($appointment->checkout_at)->format('d.m.Y H:i') ?? '-' }}</p>
                        <p><strong>Durum:</strong> 
                            @php
                                $theme = 'secondary';
                                if ($appointment->status === \App\Enums\AppointmentStatus::SCHEDULED) $theme = 'info';
                                if ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN) $theme = 'warning';
                                if ($appointment->status === \App\Enums\AppointmentStatus::COMPLETED) $theme = 'success';
                                if ($appointment->status === \App\Enums\AppointmentStatus::CANCELLED) $theme = 'danger';
                            @endphp
                            <span class="badge badge-{{ $theme }}">
                                {{ __($appointment->status->value) }}
                            </span>
                        </p>
                        @if ($appointment->notes)
                            <p><strong>Notlar:</strong> {{ $appointment->notes }}</p>
                        @endif
                    </div>
                
                    {{-- Alınan Hizmetler --}}
                    <div class="col-md-12">
                        <h4>Alınan Hizmetler</h4>
                        @if ($appointment->services->isNotEmpty())
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">Hizmet Özeti</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Hizmet</th>
                                                    <th class="text-nowrap">Birim Fiyat</th>
                                                    <th class="text-nowrap">İndirimli Fiyat</th>
                                                    <th class="text-nowrap">Miktar</th>
                                                    <th class="text-nowrap">Ara Toplam</th>
                                                    <th class="text-nowrap">İndirimli Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php 
                                                    $originalGrand = 0;
                                                    $discountedGrand = 0;
                                                @endphp
                                                @foreach ($appointment->services as $service)
                                                    @php
                                                        $originalPrice = $service->pivot->unit_price ?? $service->base_price;
                                                        $discountedPrice = $service->pivot->discounted_price ?? $originalPrice;
                                                        $quantity = $service->pivot->quantity ?? 1;
                                                        $originalSubtotal = $originalPrice * $quantity;
                                                        $discountedSubtotal = $discountedPrice * $quantity;
                                                        $originalGrand += $originalSubtotal;
                                                        $discountedGrand += $discountedSubtotal;
                                                        $discount = $originalPrice - $discountedPrice;
                                                        $discountPercent = $originalPrice > 0 ? round(($discount / $originalPrice) * 100, 0) : 0;
                                                        $totalDiscount = $originalSubtotal - $discountedSubtotal;
                                                        $totalDiscountPercent = $originalSubtotal > 0 ? round(($totalDiscount / $originalSubtotal) * 100, 0) : 0;
                                                        
                                                        $unitText = match($service->unit) {
                                                            'day' => 'Gün',
                                                            'hour' => 'Saat',
                                                            default => 'Seans',
                                                        };
                                                    @endphp
                                                    <tr class="{{ $discount > 0 ? 'table-warning' : '' }}">
                                                        <td>{{ $service->name }}</td>
                                                        <td class="text-nowrap">{{ number_format($originalPrice, 2) }} TL</td>
                                                        <td class="text-nowrap {{ $discount > 0 ? 'text-success font-weight-bold' : '' }}">
                                                            {{ number_format($discountedPrice, 2) }} TL
                                                            @if($discount > 0)
                                                                <div class="text-muted small">
                                                                    <s class="text-muted">{{ number_format($originalPrice, 2) }} TL</s>
                                                                    <span class="badge badge-success ml-1">-%{{ $discountPercent }}</span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">{{ $quantity }} {{ $unitText }}</td>
                                                        <td class="text-nowrap">{{ number_format($originalSubtotal, 2) }} TL</td>
                                                        <td class="text-nowrap {{ $totalDiscount > 0 ? 'text-success font-weight-bold' : '' }}">
                                                            {{ number_format($discountedSubtotal, 2) }} TL
                                                            @if($totalDiscount > 0)
                                                                <div class="text-muted small">
                                                                    <span class="badge badge-success">-{{ number_format($totalDiscount, 2) }} TL (%{{ $totalDiscountPercent }})</span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light">
                                                @php
                                                    $totalDiscount = $originalGrand - $discountedGrand;
                                                    $totalDiscountPercent = $originalGrand > 0 ? round(($totalDiscount / $originalGrand) * 100, 2) : 0;
                                                @endphp
                                                <tr class="font-weight-bold">
                                                    <td colspan="4" class="text-right">GENEL TOPLAM:</td>
                                                    <td class="text-nowrap">{{ number_format($originalGrand, 2) }} TL</td>
                                                    <td class="text-nowrap text-success">
                                                        {{ number_format($discountedGrand, 2) }} TL
                                                        @if($totalDiscount > 0)
                                                            <div class="text-muted small">
                                                                <span class="text-success">Toplam İndirim: -{{ number_format($totalDiscount, 2) }} TL (%{{ $totalDiscountPercent }})</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-1 ml-3">
                                                <strong>Toplam:</strong>
                                                <span class="font-weight-bold">{{ number_format($discountedGrand, 2) }} TL</span>
                                            </div>
                                            @if($totalDiscount > 0)
                                                <div class="d-flex justify-content-between align-items-center text-success ml-3">
                                                    <strong>Toplam İndirim:</strong>
                                                    <span class="font-weight-bold">-{{ number_format($totalDiscount, 2) }} TL ({{ $totalDiscountPercent }}%)</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1 ml-3">
                                                    <strong>Ödenecek Tutar:</strong>
                                                    <span class="font-weight-bold text-success">{{ number_format($discountedGrand, 2) }} TL</span>
                                                </div>
                                            @endif
                                        </div>
                            </div>
                        @else
                            <div class="alert alert-info">Randevu için henüz bir hizmet seçilmemiş.</div>
                        @endif
                    </div>
                </div>
                
                

                {{-- İşlem Butonları --}}
                <div class="d-flex justify-content-between align-items-center mb-2 ml-2">
                    <div>
                        <a href="{{ route('appointments.delivery.pdf', $appointment) }}" target="_blank" class="btn btn-outline-primary mr-2 mb-2">
                            <i class="fas fa-file-pdf"></i> Teslim Tutanağı (PDF)
                        </a>
                        <a href="{{ route('appointments.pdf', $appointment) }}" target="_blank" class="btn btn-outline-secondary mb-2">
                            <i class="fas fa-file-pdf"></i> Randevu Detayları (PDF)
                        </a>
                    </div>
                    @if ($appointment->status === \App\Enums\AppointmentStatus::SCHEDULED)
                        <a href="{{ route('appointments.checkin.form', $appointment) }}" class="btn btn-success mr-2">
                            <i class="fas fa-sign-in-alt"></i> Check-in
                        </a>
                    @elseif ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                        <!-- <div class="mr-3 text-muted">
                            <p class="mb-0">Müşteri çıkış yapmaya hazır. Lütfen hizmetleri kontrol edin.</p>
                        </div> -->
                        <a href="{{ route('appointments.checkout.form', $appointment) }}" class="btn btn-success">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </a>
                    @endif

                    @if ($appointment->status !== \App\Enums\AppointmentStatus::COMPLETED && $appointment->status !== \App\Enums\AppointmentStatus::CANCELLED)
                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning ml-2">
                            <i class="fas fa-edit"></i> Düzenle
                        </a>
                        @if ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                            <a href="{{ route('appointments.delivery.pdf', $appointment) }}" target="_blank" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-file-pdf"></i> Teslim Tutanağı
                            </a>
                        @elseif ($appointment->status === \App\Enums\AppointmentStatus::COMPLETED)
                            <a href="{{ route('appointments.pdf', $appointment) }}" target="_blank" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-file-pdf"></i> Randevu PDF
                            </a>
                        @endif
                        <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline ml-2" onsubmit="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?');">
                            @csrf
                            @method('DELETE')
                            <x-adminlte-button type="submit" label="Sil" theme="danger" icon="fas fa-trash"/>
                        </form>
                    @endif
                </div>

            </x-adminlte-card>
        </div>
    </div>
@stop