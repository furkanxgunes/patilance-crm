@extends('adminlte::page')

@section('title', 'Hizmet Analiz Raporu')

@section('content_header')
    <h1>Hizmet Analiz Raporu</h1>
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
            <form action="{{ route('raporlar.hizmet-analizi') }}" method="get" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="baslangic" class="mr-2">Başlangıç Tarihi:</label>
                    <input type="date" class="form-control" id="baslangic" name="baslangic" value="{{ $baslangic }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="bitis" class="mr-2">Bitiş Tarihi:</label>
                    <input type="date" class="form-control" id="bitis" name="bitis" value="{{ $bitis }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Filtrele</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hizmet İstatistikleri</h3>
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
                            <th>Hizmet Adı</th>
                            <th>Toplam İşlem Sayısı</th>
                            <th>Toplam Tutar (₺)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hizmetler as $hizmet)
                            <tr>
                                <td>{{ $hizmet->name}} {{ $hizmet->trashed() ? '(Silinmiş)' : '' }}</td>
                                <td>{{ $hizmet->toplam_islem }}</td>
                                <td>{{ number_format($hizmet->toplam_tutar, 2, ',', '.') }} ₺</td>
                                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Kayıt bulunamadı</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">* {{ $baslangic }} - {{ $bitis }} tarihleri arasındaki veriler gösterilmektedir.</small>
        </div>
    </div>

    @if($hizmetler->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hizmet Dağılımı</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px; width:100%;">
                    <canvas id="hizmetDagilimChart"></canvas>
                </div>
            </div>
        </div>
    @endif
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($hizmetler->isNotEmpty())
                var ctx = document.getElementById('hizmetDagilimChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($hizmetler->pluck('name')) !!},
                        datasets: [{
                            data: {!! json_encode($hizmetler->pluck('toplam_islem')) !!},
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
                            legend: {
                                position: 'bottom',
                                align: 'center',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw || 0;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} işlem (${percentage}%)`;
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
