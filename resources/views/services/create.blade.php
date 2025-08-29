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
    <div class="col-md-12">
        <x-adminlte-card title="Yeni Hizmet Kayıt Formu" theme="primary" icon="fas fa-plus-circle" collapsible>
            <form method="POST" action="{{ route('services.store') }}" id="serviceForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
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

                        {{-- Varsayılan Fiyat --}}
                        <x-adminlte-input name="base_price" type="number" step="0.01" label="Varsayılan Birim Fiyat (TL)" placeholder="0.00" required />

                        {{-- Süre --}}
                        <x-adminlte-input name="duration_minutes" type="number" label="Tahmini Süre (Dakika)" placeholder="Dakika cinsinden süre girin..." />

                        {{-- Açıklama --}}
                        <x-adminlte-textarea name="description" label="Açıklama (İsteğe Bağlı)" placeholder="Hizmetle ilgili detayları buraya yazın..." rows="3" />
                    </div>
    
                            {{-- Irka Özel Fiyatlar --}}
                    <div class="col-md-4">
                        <div class="card mt-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Fiyatlandırma Tablosu</h5>
                                <small class="text-muted">Varsayılan fiyat kullanılacaksa boş bırakın.</small>
                            </div>
                            <div class="card-body table-responsive p-0" style="max-height: 400px;">
                                @php $breeds = \App\Models\Breed::all(); @endphp

                                @if($breeds->isEmpty())
                                    <div class="alert alert-info m-3">
                                        <i class="fas fa-info-circle"></i> Henüz hiç ırk tanımlanmamış.
                                    </div>
                                @else
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Irk</th>
                                                <th>Fiyat (TL)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($breeds as $breed)
                                        
                                                <tr>
                                                    <td>{{ $breed->name }}</td>
                                                    <td>
                                                        <input type="number"
                                                            step="0.01"
                                                            class="form-control breed-price"
                                                            name="breed_prices[{{ $breed->id }}]"
                                                            id="breed_price_{{ $breed->id }}"
                                                            placeholder="Varsayılan fiyatı kullan"
                                                            value=""
                                                            data-breed-id="{{ $breed->id }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    
                        </div>
                    
                    </div

                </div>

                <x-adminlte-button class="d-flex justify-content-end mt-3" type="submit" label="Hizmeti Kaydet" theme="success" icon="fas fa-save"/>
            </form>
        </x-adminlte-card>
    </div>
</div>

{{-- Temizle butonu JS --}}
@section('js')
<script>
    document.querySelectorAll('.btn-clear').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.target);
            if(target) target.value = '';
        });
    });
</script>
@stop
@endsection
