@extends('adminlte::page')

@section('title', 'Ciro Raporu')

@section('content_header')
    <h1>Ciro Raporu</h1>
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
            <form action="{{ route('raporlar.ciro-raporu') }}" method="get" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="tarih_araligi" class="mr-2">Rapor Periyodu:</label>
                    <select name="tarih_araligi" id="tarih_araligi" class="form-control" onchange="this.form.submit()">
                        <option value="gunluk" {{ $tarihAraligi == 'gunluk' ? 'selected' : '' }}>Son 30 Gün</option>
                        <option value="aylik" {{ $tarihAraligi == 'aylik' ? 'selected' : '' }}>Son 12 Ay</option>
                        <option value="yillik" {{ $tarihAraligi == 'yillik' ? 'selected' : '' }}>Yıllık</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ciro Grafiği</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="ciroChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hizmetlere Göre Ciro Dağılımı</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="hizmetDagilimChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Ciro Özeti</h3>
            <button type="button" class="btn btn-tool" onclick="window.print()">
                <i class="fas fa-print"></i> Yazdır
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Tarih</th>
                            <th class="text-right">Toplam Ciro (₺)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $toplamCiro = 0;
                            $toplamIslem = 0;
                        @endphp
                        @foreach($ciroVerileri as $tarih => $tutar)
                            @php
                                $toplamCiro += $tutar;
                                $toplamIslem++;
                            @endphp
                            <tr>
                                <td>{{ $tarih }}</td>
                                <td class="text-right">{{ number_format($tutar, 2, ',', '.') }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-weight-bold">
                        <tr>
                            <td>TOPLAM ({{ $toplamIslem }} işlem)</td>
                            <td class="text-right">{{ number_format($toplamCiro, 2, ',', '.') }} ₺</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ciro Grafiği
            var ciroCtx = document.getElementById('ciroChart').getContext('2d');
            var ciroChart = new Chart(ciroCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($ciroVerileri)) !!},
                    datasets: [{
                        label: 'Ciro (₺)',
                        data: {!! json_encode(array_values($ciroVerileri)) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: '{{ $tarihAraligi == "gunluk" ? "Son 30 Günlük Ciro" : ($tarihAraligi == "aylik" ? "Son 12 Aylık Ciro" : "Yıllık Ciro") }}'
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.parsed.y.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 });
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 3,
                            hoverRadius: 5
                        }
                    }
                }
            });

            // Hizmet Dağılım Grafiği
            var hizmetCtx = document.getElementById('hizmetDagilimChart').getContext('2d');
            var hizmetChart = new Chart(hizmetCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($hizmetCiroDagilimi->pluck('name')) !!},
                    datasets: [{
                        data: {!! json_encode($hizmetCiroDagilimi->pluck('toplam_ciro')) !!},
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                            '#9966FF', '#FF9F40', '#8AC24A', '#7E57C2',
                            '#FF7043', '#5C6BC0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Hizmetlere Göre Ciro',
                            font: { size: 14 }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = Math.round((value / total) * 100);
                                    return ` ${label}: ${value.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' })} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
