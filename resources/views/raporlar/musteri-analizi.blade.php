@extends('adminlte::page')

@section('title', 'Müşteri Analiz Raporu')

@section('content_header')
    <h1>Müşteri Analiz Raporu</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtreler</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('raporlar.musteri-analizi') }}" method="get" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="baslangic" class="mr-2">Başlangıç Tarihi:</label>
                    <input type="date" name="baslangic" id="baslangic" class="form-control" 
                           value="{{ $baslangic }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="bitis" class="mr-2">Bitiş Tarihi:</label>
                    <input type="date" name="bitis" id="bitis" class="form-control" 
                           value="{{ $bitis }}" max="{{ date('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-filter"></i> Filtrele
                </button>
                <a href="{{ route('raporlar.musteri-analizi') }}" class="btn btn-default mb-2 ml-2">
                    <i class="fas fa-sync"></i> Sıfırla
                </a>
            </form>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">En Çok Hizmet Alan Müşteriler ({{ $baslangic }} - {{ $bitis }})</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="window.print()">
                            <i class="fas fa-print"></i> Yazdır
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Müşteri Adı</th>
                                    <th>Toplam İşlem Sayısı</th>
                                    <th>Toplam Harcama (₺)</th>
                                    <th>Son İşlem Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sadikMusteriler as $index => $musteri)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $musteri->name }}</td>
                                        <td>{{ $musteri->appointments_count }}</td>
                                        <td class="text-right">
                                            {{ number_format($musteri->toplam_harcama ?? 0, 2, ',', '.') }} ₺
                                        </td>
                                        <td>
                                            @if($musteri->son_randevu)
                                                {{ $musteri->son_randevu->tarih->format('d.m.Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Kayıt bulunamadı</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">En Çok Harcama Yapan Müşteriler</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Müşteri Adı</th>
                                    <th>Toplam Harcama (₺)</th>
                                    
                              
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($harcamaYapanMusteriler as $index => $musteri)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $musteri->name }}</td>
                                        <td class="text-right">
                                            {{ number_format($musteri->toplam_harcama, 2, ',', '.') }} ₺
                                        </td>
                                      
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Kayıt bulunamadı</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($sadikMusteriler->isNotEmpty())
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">En Çok İşlem Yapılan Müşteriler</h3>
                    </div>
                    <div class="card-body p-2">
                        <div style="height: 200px;">
                            <canvas id="musteriIslemChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">En Çok Harcama Yapan Müşteriler</h3>
                    </div>
                    <div class="card-body p-2">
                        <div style="height: 200px;">
                            <canvas id="musteriHarcamaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($sadikMusteriler->isNotEmpty())
                // En çok işlem yapılan müşteriler grafiği
                var ctx1 = document.getElementById('musteriIslemChart').getContext('2d');
                var chart1 = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($sadikMusteriler->take(5)->pluck('name')) !!},
                        datasets: [{
                            label: 'İşlem Sayısı',
                            data: {!! json_encode($sadikMusteriler->take(5)->pluck('appointments_count')) !!},
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            barThickness: 20,
                            maxBarThickness: 25
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'En Çok İşlem Yapılan Müşteriler',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 10
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // En çok harcama yapan müşteriler grafiği
                var ctx2 = document.getElementById('musteriHarcamaChart').getContext('2d');
                var chart2 = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($harcamaYapanMusteriler->take(5)->pluck('name')) !!},
                        datasets: [{
                            label: 'Toplam Harcama (₺)',
                            data: {!! json_encode($harcamaYapanMusteriler->take(5)->pluck('toplam_harcama')) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            barThickness: 20,
                            maxBarThickness: 25
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'En Çok Harcama Yapan Müşteriler'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Toplam: ' + context.raw.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('tr-TR') + ' ₺';
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endpush
