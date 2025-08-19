<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 10px; }
        h2 { font-size: 16px; margin: 20px 0 8px; }
        .muted { color: #666; }
        .row { display: flex; gap: 16px; }
        .col { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .mb-8 { margin-bottom: 8px; }
        .mb-16 { margin-bottom: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #eee; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Randevu Bilgileri <span class="muted">#{{ $appointment->id }}</span></h1>
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

    <div class="mb-16">
        <strong>Durum:</strong>
        <span class="badge">{{ ucfirst(__($appointment->status->value ?? (string)$appointment->status)) }}</span>
    </div>

    <h2>Hizmetler</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Hizmet Adı</th>
                <th class="right">Birim Fiyat</th>
                <th class="right">Adet</th>
                <th class="right">Tutar</th>
            </tr>
        </thead>
        <tbody>
            @php $grand = 0; @endphp
            @forelse ($appointment->services as $idx => $service)
                @php
                    $price = $service->pivot->unit_price ?? $service->base_price;
                    $qty = $service->pivot->quantity ?? 1;
                    $line = (float) $price * (int) $qty;
                    $grand += $line;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $service->name }}</td>
                    <td class="right">{{ number_format((float)$price, 2, ',', '.') }} ₺</td>
                    <td class="right">{{ $qty }}</td>
                    <td class="right">{{ number_format($line, 2, ',', '.') }} ₺</td>
                </tr>
            @empty
                <tr><td colspan="5">Hizmet seçilmemiş.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Genel Toplam</th>
                <th class="right">{{ number_format($grand, 2, ',', '.') }} ₺</th>
            </tr>
        </tfoot>
    </table>

    @if(!empty($appointment->notes))
        <h2>Notlar</h2>
        <div>{!! $appointment->notes !!}</div>
    @endif
</body>
</html>
