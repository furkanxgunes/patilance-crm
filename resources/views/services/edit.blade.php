{{-- services/edit.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Hizmeti Düzenle: ' . $service->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Hizmeti Düzenle: {{ $service->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Hizmetler</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Hizmet Güncelleme Formu" theme="warning" icon="fas fa-edit" collapsible>

                <form method="POST" action="{{ route('services.update', $service) }}">
                    @csrf
                    @method('PUT')

                    {{-- Hizmet Adı --}}
                    <x-adminlte-input name="name" label="Hizmet Adı" placeholder="Hizmet adını girin..."
                                      :value="old('name', $service->name)" required />

                    {{-- Kategori --}}
                    <x-adminlte-input name="category" label="Kategori" placeholder="Hizmet kategorisini girin..."
                                      :value="old('category', $service->category)" required />

                    {{-- Birim --}}
                    <x-adminlte-select2 name="unit" label="Birim" required>
                        @foreach(\App\Models\Service::getUnits() as $value => $label)
                            <option value="{{ $value }}" {{ old('unit', $service->unit) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-adminlte-select2>

                    {{-- Temel Fiyat --}}
                    <x-adminlte-input name="base_price" type="number" step="0.01" label="Temel Fiyat (TL)"
                                      placeholder="0.00" :value="old('base_price', $service->base_price)" required />

                    {{-- Süre --}}
                    <x-adminlte-input name="duration_minutes" type="number" label="Tahmini Süre (Dakika)"
                                      placeholder="Dakika cinsinden süre girin..." :value="old('duration_minutes', $service->duration_minutes)" required />

                    {{-- Açıklama --}}
                    <x-adminlte-text-editor name="description" label="Açıklama (İsteğe Bağlı)"
                                            placeholder="Hizmetle ilgili detayları buraya yazın...">
                        {{ old('description', $service->description) }}
                    </x-adminlte-text-editor>

                    <div class="d-flex justify-content-end mt-3">
                        <x-adminlte-button type="submit" label="Hizmeti Güncelle" theme="warning" icon="fas fa-save"/>
                    </div>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop