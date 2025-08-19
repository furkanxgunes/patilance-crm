{{-- services/index.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Hizmet Yönetimi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Hizmet Yönetimi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Hizmetler</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')

    {{-- Başarı Mesajı --}}
    @if (session('success'))
        <x-adminlte-alert theme="success" title="Başarılı">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    {{-- Ana içerik kutusu --}}
    <x-adminlte-card title="Hizmet Listesi" theme="info" icon="fas fa-bullhorn" collapsible>
        
        <div class="d-flex justify-content-between mb-3">
            <form method="GET" action="{{ route('services.index') }}" class="form-inline">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Hizmet adı veya kategori">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        <a href="{{ route('services.index') }}" class="btn btn-outline-light">Temizle</a>
                    </div>
                </div>
            </form>
            <a href="{{ route('services.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Hizmet Ekle
            </a>
        </div>

        {{-- Hizmetler Tablosu --}}
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Hizmet Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Birim</th>
                        <th>Süre</th>
                        <th style="width: 150px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->category }}</td>
                            <td>{{ number_format($service->base_price, 2) }} ₺</td>
                            <td>{{ \App\Models\Service::getUnits()[$service->unit] ?? $service->unit }}</td>
                            <td></td>
                            <td>
                                <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('Bu hizmeti silmek istediğinizden emin misiniz?');">
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
                            <td colspan="4" class="text-center">Henüz hiç hizmet eklenmemiş.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $services->links() }}
        </div>

    </x-adminlte-card>
@stop