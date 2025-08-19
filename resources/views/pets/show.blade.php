@extends('adminlte::page')

@section('title', 'Pet Detay')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Pet Detay</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Petler</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pet->name }}</li>
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
        <div class="col-12 col-lg-8">
            <x-adminlte-card title="Pet Bilgileri" theme="primary" icon="fas fa-paw" collapsible>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 240px;">Ad</th>
                                <td>{{ $pet->name }}</td>
                            </tr>
                            <tr>
                                <th>Tür</th>
                                <td>{{ $pet->species }}</td>
                            </tr>
                            <tr>
                                <th>Irk</th>
                                <td>{{ $pet->breed }}</td>
                            </tr>
                            <tr>
                                <th>Cinsiyet</th>
                                <td>{{ $pet->gender ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Yaş</th>
                                <td>{{ $pet->age !== null ? $pet->age : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kilo (kg)</th>
                                <td>{{ $pet->weight_kg !== null ? $pet->weight_kg : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Çip Numarası</th>
                                <td>{{ $pet->chip_no ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Veteriner Bilgileri</th>
                                <td>{{ $pet->veterinarian_info ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Eşgali</th>
                                <td>{{ $pet->appearance ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Özel işaretleri</th>
                                <td>{{ $pet->special_marks ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Huyları / Tuvalet alışkanlığı</th>
                                <td>{{ $pet->habits_toilet ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Aşılar ve tarihleri</th>
                                <td>{{ $pet->vaccines ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alerjiler</th>
                                <td>{{ $pet->allergies ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kullandığı preparatlar</th>
                                <td>{!! $pet->medications_text ?: '-' !!}</td>
                            </tr>
                            <tr>
                                <th>Oluşturulma</th>
                                <td>{{ optional($pet->created_at)->format('d.m.Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Güncellenme</th>
                                <td>{{ optional($pet->updated_at)->format('d.m.Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('pets.edit', $pet) }}" class="btn btn-warning mr-2">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <a href="{{ route('pets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Listeye Dön
                    </a>
                </div>
            </x-adminlte-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-adminlte-card title="Sahip Bilgileri" theme="light" icon="fas fa-user" collapsible>
                @if($pet->customer)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 180px;">Ad Soyad</th>
                                    <td>{{ $pet->customer->name }}</td>
                                </tr>
                                @if(!empty($pet->customer->email))
                                <tr>
                                    <th>E-posta</th>
                                    <td>
                                        <a href="mailto:{{ $pet->customer->email }}">{{ $pet->customer->email }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($pet->customer->phone))
                                <tr>
                                    <th>Telefon</th>
                                    <td>
                                        <a href="tel:{{ $pet->customer->phone }}">{{ $pet->customer->phone }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($pet->customer->address))
                                <tr>
                                    <th>Adres</th>
                                    <td>{{ $pet->customer->address }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.show', $pet->customer) }}" class="btn btn-info mr-2">
                            <i class="fas fa-eye"></i> Müşteri Detayı
                        </a>
                        <a href="{{ route('customers.edit', $pet->customer) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Müşteriyi Düzenle
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        Bu pet için ilişkilendirilmiş bir müşteri bulunamadı.
                    </div>
                @endif
            </x-adminlte-card>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <x-adminlte-card title="Randevu Geçmişi" theme="primary" icon="fas fa-history" collapsible>
                @if(isset($appointments) && $appointments->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:80px;">#</th>
                                    <th>Müşteri</th>
                                    <th>Planlanan</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Durum</th>
                                    <th>Alınan Hizmetler</th>
                                    <th style="width:100px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appt)
                                    <tr>
                                        <td><a href="{{ route('appointments.show', $appt) }}">#{{ $appt->id }}</a></td>
                                        <td>{{ $appt->customer?->name ?? '-' }}</td>
                                        <td>{{ optional($appt->planned_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ optional($appt->checkin_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ optional($appt->checkout_at)->format('d.m.Y H:i') ?? '-' }}</td>
                                        <td>{{ __($appt->status->value) }}</td>
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
                                                        @php $sum=0; @endphp
                                                        @foreach($appt->services as $srv)
                                                            @php
                                                                $orig = $srv->base_price;
                                                                $unit = $srv->pivot->unit_price ?? $orig;
                                                                $qty = $srv->pivot->quantity ?? 1;
                                                                $line = ($unit ?? 0) * $qty;
                                                                $sum += $line;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $srv->name }}</td>
                                                                <td>{{ number_format($orig, 2) }} TL</td>
                                                                <td>{{ $unit !== null ? number_format($unit, 2) . ' TL' : '-' }}</td>
                                                                <td>{{ $qty }}</td>
                                                                <td>{{ number_format($line, 2) }} TL</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <th colspan="4" class="text-right">Toplam</th>
                                                            <th>{{ number_format($sum, 2) }} TL</th>
                                                        </tr>
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
                    <div class="text-muted">Bu evcil hayvana ait randevu kaydı bulunamadı.</div>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop
