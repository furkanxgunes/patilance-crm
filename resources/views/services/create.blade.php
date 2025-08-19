{{-- services/create.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Yeni Hizmet Ekle')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Yeni Hizmet Ekle</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Hizmetler</a></li>
                <li class="breadcrumb-item active" aria-current="page">Yeni</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Formu bir kart (card) içine alarak daha düzenli bir görünüm sağlayalım --}}
            <x-adminlte-card title="Yeni Hizmet Kayıt Formu" theme="primary" icon="fas fa-plus-circle" collapsible>
                
                <form method="POST" action="{{ route('services.store') }}">
                    @csrf

                    {{-- Hizmet Adı --}}
                    <x-adminlte-input name="name" label="Hizmet Adı" placeholder="Hizmet adını girin..." required />
                    
                    {{-- Kategori --}}
                    <x-adminlte-input name="category" label="Kategori" placeholder="Hizmet kategorisini girin..." required />

                    {{-- Birim --}}
                    <x-adminlte-select2 name="unit" label="Birim" required>
                        @foreach(\App\Models\Service::getUnits() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    {{-- Birim Fiyat --}}
                    <x-adminlte-input name="base_price" type="number" step="0.01" label="Birim Fiyat (TL)" placeholder="0.00" required />

                    {{-- Süre --}}
                    <x-adminlte-input name="duration_minutes" type="number" label="Tahmini Süre (Dakika)" placeholder="Dakika cinsinden süre girin..."  />

                    {{-- Açıklama --}}
                    <x-adminlte-text-editor name="description" label="Açıklama (İsteğe Bağlı)" placeholder="Hizmetle ilgili detayları buraya yazın..." />

                    <x-adminlte-button class="d-flex justify-content-end mt-3" type="submit" label="Hizmeti Kaydet" theme="success" icon="fas fa-save"/>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop