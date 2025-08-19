<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hayvan Teslim Tutanağı</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 16px; margin: 6px 0 10px; text-align: center; }
        h2 { font-size: 12px; margin: 16px 0 8px; }
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
        <h1>Hayvan Teslim Tutanağı <span class="muted"></span></h1>
    </div>

    <div class="row">
        <div class="col">
            <h2>Hayvan Bilgileri</h2>
            <table>
                <tr><th>Adı</th><td>{{ $appointment->pet?->name ?? '-' }}</td></tr>
                <tr><th>Yaşı</th><td>{{ $appointment->pet?->age ?? '-' }}</td></tr>
                <tr><th>Türü</th><td>{{ $appointment->pet?->species ?? '-' }}</td></tr>
                <tr><th>Irkı</th><td>{{ $appointment->pet?->breed ?? '-' }}</td></tr>
                <tr><th>Cinsiyet</th><td>{{ $appointment->pet?->gender ?? '-' }}</td></tr>
                <tr><th>Eşgali</th><td>{{ $appointment->pet?->appearance ?? '-' }}</td></tr>
                <tr><th>Özel İşaretleri</th><td>{{ $appointment->pet?->special_marks ?? '-' }}</td></tr>
                <tr><th>Huyları ve Tuvalet Alışkanlığı</th><td>{{ $appointment->pet?->habits_toilet ?? '-' }}</td></tr>
                <tr><th>Planlanan Giriş Tarihi</th><td>{{ optional($appointment->planned_at)->format('d.m.Y H:i') ?? '-' }}</td></tr>
                <tr><th>Planlanan Çıkış Tarihi</th><td>{{ optional($appointment->planned_exit)->format('d.m.Y H:i') ?? '-' }}</td></tr>
                <tr><th>Check-in Tarihi</th><td>{{ optional($appointment->checkin_at)->format('d.m.Y H:i') ?? '-' }}</td></tr>
                <tr><th>Check-out Tarihi</th><td>{{ optional($appointment->checkout_at)->format('d.m.Y H:i') ?? '-' }}</td></tr>
            
                <tr>
                    <th>Aşılar ve Tarihleri</th>
                    <td>
                        @php
                            $vaccines = $appointment->pet?->vaccines;
                            if (is_string($vaccines)) {
                                $decoded = json_decode($vaccines, true);
                                if (json_last_error() === JSON_ERROR_NONE) { $vaccines = $decoded; }
                            }
                        @endphp
                        @if (is_array($vaccines) && !empty($vaccines))
                            <ul>
                                @foreach ($vaccines as $v)
                                    <li>
                                        @if (is_array($v))
                                            {{ $v['name'] ?? ($v['title'] ?? '-') }} - {{ $v['date'] ?? ($v['when'] ?? '-') }}
                                        @else
                                            {{ (string)$v }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr><th>Alerjiler</th><td>{!! nl2br(e($appointment->pet?->allergies ?? '')) ?: '-' !!}</td></tr>
                <tr><th>Preparatlar</th><td>{!! $appointment->pet?->medications_text ? $appointment->pet->medications_text : '-' !!}</td></tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h2>Hayvan Sahibinin Bilgileri</h2>
            <table>
                <tr><th>Adı</th><td>{{ $appointment->customer?->name ?? '-' }}</td></tr>
                <tr><th>Adres</th><td>{{ $appointment->customer?->address ?? '-' }}</td></tr>
                <tr><th>Telefon</th><td>{{ $appointment->customer?->phone ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="col">
            <h2>Veteriner Hekim Bilgisi</h2>
            <div class="block">{!! nl2br(e($appointment->pet?->veterinarian_info ?? '')) ?: '-' !!}</div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h2>Hayvan Sahibinin Özel İstekleri</h2>
            <div class="block">{!! $appointment->owner_requests ? $appointment->owner_requests : '-' !!}</div>
        </div>
    </div>


    <div class="row">
        <table>
            <tr>
                <th>#</th>
                <th>İşyeri Sahibi</th>
                <th>İşyeri Veteriner Hekimi</th>
                <th>Hayvan Sahibi</th>
            </tr>
            <tr>
                <th>Ad Soyad</th>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th>İmza</th>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
</body>
</html>
