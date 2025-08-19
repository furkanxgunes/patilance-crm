{{-- customers/show.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Müşteri Detayları: ' . $customer->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Müşteri Detayları: {{ $customer->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Müşteriler</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    {{-- Flash Mesajları --}}
    @if (session('success'))
        <x-adminlte-alert theme="success" title="Başarılı">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="Hata">{{ session('error') }}</x-adminlte-alert>
    @endif
    <div class="row">
        <div class="col-md-6">
            {{-- Müşteri Bilgileri Kutusu --}}
            <x-adminlte-card title="Müşteri Bilgileri" theme="info" icon="fas fa-user-circle">
                <p>
                    <strong>Ad Soyad:</strong> {{ $customer->name }}<br>
                    <strong>E-posta:</strong> {{ $customer->email }}<br>
                    <strong>Telefon:</strong> {{ $customer->phone ?? 'N/A' }}<br>
                    <strong>Adres:</strong> {{ $customer->address ?? 'N/A' }}
                </p>
                <x-slot name="footer">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Bilgileri Düzenle
                    </a>
                </x-slot>
            </x-adminlte-card>
        </div>
        <div class="col-md-6">
            {{-- Müşteri Evcil Hayvanları --}}
            <x-adminlte-card title="Evcil Hayvanları" theme="secondary" icon="fas fa-paw">
                
                {{-- Başarı Mesajı --}}
                @if (session('success'))
                    <x-adminlte-alert theme="success" title="Başarılı">
                        {{ session('success') }}
                    </x-adminlte-alert>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Adı</th>
                                <th>Tür</th>
                                <th>Irk</th>
                                <th style="width: 150px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->pets as $pet)
                                <tr>
                                    <td>{{ $pet->name }}</td>
                                    <td>{{ $pet->species }}</td>
                                    <td>{{ $pet->breed }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('pets.edit', $pet) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form class="d-inline" action="{{ route('pets.destroy', $pet) }}" method="POST" onsubmit="return confirm('Bu evcil hayvanı silmek istediğinizden emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Bu müşteriye ait evcil hayvan bulunamadı.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot name="footer">
                    <a href="{{ route('pets.create', $customer) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Yeni Pet Ekle
                    </a>
                </x-slot>
            </x-adminlte-card>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Randevu Geçmişi" theme="primary" icon="fas fa-history">
                @if(isset($appointments) && $appointments->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">#</th>
                                    <th>Planlanan</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Durum</th>
                                    <th>Pet</th>
                                    <th>Alınan Hizmetler</th>
                                    <th style="width: 120px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appt)
                                    <tr>
                                        <td><a href="{{ route('appointments.show', $appt) }}">#{{ $appt->id }}</a></td>
                                        <td>{{ optional($appt->planned_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ optional($appt->checkin_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ optional($appt->checkout_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ __($appt->status->value) }}</td>
                                        <td>{{ $appt->pet?->name ?? '-' }}</td>
                                        <td>
                                            @if($appt->services->isNotEmpty())
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Hizmet</th>
                                                            <th>Orijinal</th>
                                                            <th>Checkout</th>
                                                            <th>Adet</th>
                                                            <th>Tutar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($appt->services as $srv)
                                                            @php
                                                                $orig = $srv->base_price;
                                                                $unit = $srv->pivot->unit_price ?? $orig;
                                                                $qty = $srv->pivot->quantity ?? 1;
                                                                $line = ($unit ?? 0) * $qty;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $srv->name }}</td>
                                                                <td>{{ number_format($orig, 2) }} TL</td>
                                                                <td>{{ $unit !== null ? number_format($unit, 2) . ' TL' : '-' }}</td>
                                                                <td>{{ $qty }}</td>
                                                                <td>{{ number_format($line, 2) }} TL</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-secondary" href="{{ route('appointments.show', $appt) }}"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        {{ $appointments->links() }}
                    </div>
                @else
                    <div class="text-muted">Bu müşteriye ait randevu kaydı bulunamadı.</div>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop