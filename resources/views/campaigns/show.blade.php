@extends('adminlte::page')

@section('title', 'Kampanya Detayı: ' . $campaign->name)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Kampanya Detayı: {{ $campaign->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Kampanyalar</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $campaign->name }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-end">
                <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
                <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="ml-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" 
                            onclick="return confirm('Bu kampanyayı silmek istediğinize emin misiniz?')">
                        <i class="fas fa-trash"></i> Sil
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Genel Bilgiler</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%;">Durum</th>
                            <td>
                                @if($campaign->is_active && $campaign->start_date <= now() && $campaign->end_date >= now())
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Pasif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Başlangıç Tarihi</th>
                            <td>{{ $campaign->start_date->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Bitiş Tarihi</th>
                            <td>{{ $campaign->end_date->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>İndirim Türü</th>
                            <td>{{ $campaign->discount_type === 'percentage' ? 'Yüzde (%)' : 'Sabit Tutar (₺)' }}</td>
                        </tr>
                        <tr>
                            <th>İndirim Değeri</th>
                            <td>
                                {{ $campaign->discount_value }}
                                {{ $campaign->discount_type === 'percentage' ? '%' : '₺' }}
                            </td>
                        </tr>
                        @if($campaign->max_uses)
                        <tr>
                            <th>Maksimum Kullanım</th>
                            <td>{{ $campaign->max_uses }} kez</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Kampanya Açıklaması</h5>
                    <div class="p-3 bg-light rounded">
                        {!! nl2br(e($campaign->description)) ?: '<span class="text-muted">Açıklama eklenmemiş</span>' !!}
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Kapsamdaki Hizmetler</h5>
                @if($campaign->services->isEmpty())
                    <div class="alert alert-info">
                        Bu kampanyaya henüz hiç hizmet eklenmemiş.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Hizmet Adı</th>
                                    <th>Birim Fiyat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campaign->services as $service)
                                    <tr>
                                        <td>{{ $service->name }}</td>
                                        <td>{{ number_format($service->base_price, 2) }}₺</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <small class="text-muted">
                    Oluşturulma: {{ $campaign->created_at->format('d.m.Y H:i') }}
                </small>
                <small class="text-muted">
                    Son Güncelleme: {{ $campaign->updated_at->format('d.m.Y H:i') }}
                </small>
            </div>
        </div>
    </div>
@stop
