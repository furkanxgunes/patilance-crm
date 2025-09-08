<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Detayları</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 6px 0 10px; text-align: center; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        .muted { color: #666; }
        .row { display: flex; gap: 16px; }
        .col { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
        .mb-16 { margin-bottom: 16px; }
        .block { display: block; }
        .header { text-align: center; margin-bottom: 12px; }
        .logo { max-height: 60px; display: inline-block; margin-top:-16px}
    </style>
</head>
<body>
<div class="header">
        <img width="25%"class="logo" src="{{ public_path('vendor/adminlte/dist/img/patilance_logo.png') }}" alt="Logo">
        <h1>Randevu Detayları</h1>
    </div>
    <div class="row mb-16">
        <div class="col">
            <strong>Müşteri:</strong> {{ $appointment->customer?->name ?? '-' }}<br>
            <strong>Telefon:</strong> {{ $appointment->customer?->phone ?? '-' }}<br>
            <strong>E-posta:</strong> {{ $appointment->customer?->email ?? '-' }}
        </div>
        <div class="col">
            <strong>Evcil Hayvan:</strong> {{ $appointment->pet?->name ?? '-' }}<br>
            <strong>Tür/Irk:</strong> {{ $appointment->pet?->species ?? '-' }} / {{ $appointment->pet?->breed ?? '-' }}<br>
            <strong>Ağırlık:</strong> {{ $appointment->pet?->weight_kg ? $appointment->pet->weight_kg . ' kg' : '-' }}
        </div>
        <div class="col">
            <strong>Planlanan Giriş:</strong> {{ optional($appointment->planned_at)->format('d.m.Y H:i') ?? '-' }}<br>
            <strong>Planlanan Çıkış:</strong> {{ optional($appointment->planned_exit)->format('d.m.Y H:i') ?? '-' }}<br>
            <strong>Check-in:</strong> {{ optional($appointment->checkin_at)->format('d.m.Y H:i') ?? '-' }}<br>
            <strong>Check-out:</strong> {{ optional($appointment->checkout_at)->format('d.m.Y H:i') ?? '-' }}
        </div>
    </div>

    <h2>Hizmetler</h2>
    <table>
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
        </thead>
        <tbody>
                                                @php 
                                                    $originalGrand = 0;
                                                    $discountedGrand = 0;
                                                    $breedPrice = 0;
                                                    $breedId = $appointment->pet->breed_id;
                                                @endphp
                                                @foreach ($appointment->services as $service)
                                                    @php
                                                        $breedFind = $service->breeds->where('id', $breedId)->first();
                                                        if($breedFind)
                                                        {
                                                            $breedPrice = $breedFind->pivot->price;
                                                            $originalPrice = $breedPrice > 0 ? $breedPrice : $service->pivot->unit_price;
                                                        }
                                                        else{
                                                            $originalPrice = $service->pivot->unit_price ?? $service->base_price;
                                                        }
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
                                                        $extraTotal = 0;
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
                                                @if ($appointment->extraItems->isNotEmpty())
                                                    @foreach ($appointment->extraItems as $extraItem)
                                                
                                                        <tr>
                                                            <td>{{ $extraItem->name }} <small>(Ürün/Ek)</small></td>
                                                            <td class="text-nowrap">{{ number_format($extraItem->price, 2) }} TL </td>
                                                            <td class="text-nowrap">{{ number_format($extraItem->price, 2) }} TL </td>
                                                            <td class="text-nowrap">#</td>
                                                            <td class="text-nowrap">{{ number_format($extraItem->price, 2) }} TL </td>
                                                            <td class="text-nowrap">{{ number_format($extraItem->price, 2) }} TL </td>
                                                        </tr>
                                                    @php $extraTotal += $extraItem->price @endphp
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <tfoot class="bg-light">
                                                @php
                                                    $totalDiscount = $originalGrand - $discountedGrand;
                                                    $totalDiscountPercent = $originalGrand > 0 ? round(($totalDiscount / $originalGrand) * 100, 2) : 0;
                                                @endphp
                                                <tr class="font-weight-bold">
                                                    <td colspan="4" class="text-right">GENEL TOPLAM:</td>
                                                    <td class="text-nowrap">{{ number_format($originalGrand + $extraTotal, 2) }} TL</td>
                                                    <td class="text-nowrap text-success">
                                                        {{ number_format($discountedGrand + $extraTotal, 2) }} TL
                                                        @if($totalDiscount > 0)
                                                            <div class="text-muted small">
                                                                <span class="text-success">Toplam İndirim: -{{ number_format($totalDiscount, 2) }} TL (%{{ $totalDiscountPercent }})</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tfoot>
    </table>
    <div class="d-flex justify-content-between align-items-center mb-1 ml-3">
                                                <strong>Toplam:</strong>
                                                <span class="font-weight-bold">{{ number_format($originalGrand + $extraTotal, 2) }} TL</span>
                                            </div>
                                            @if($totalDiscount > 0)
                                                <div class="d-flex justify-content-between align-items-center text-success ml-3">
                                                    <strong>Toplam İndirim:</strong>
                                                    <span class="font-weight-bold">-{{ number_format($totalDiscount, 2) }} TL ({{ $totalDiscountPercent }}%)</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1 ml-3">
                                                    <strong>Ödenecek Tutar:</strong>
                                                    <span class="font-weight-bold text-success">{{ number_format($discountedGrand + $extraTotal, 2) }} TL</span>
                                                </div>
                                            @endif
                                        </div>
    <!-- @if(!empty($appointment->notes))
        <h2>Notlar</h2>
        <div>{!! $appointment->notes !!}</div>
    @endif -->
</body>
</html>
