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

   
        <x-adminlte-card title="Hizmet Güncelle" theme="warning" icon="fas fa-edit" collapsible>
        <div class="row">
        <div class="col-md-5">    
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
                                  placeholder="Dakika cinsinden süre girin..." :value="old('duration_minutes', $service->duration_minutes)" />

                {{-- Açıklama --}}
                <x-adminlte-text-editor name="description" label="Açıklama (İsteğe Bağlı)"
                                        placeholder="Hizmetle ilgili detayları buraya yazın...">
                    {{ old('description', $service->description) }}
                </x-adminlte-text-editor>
            </div>

             
                {{-- Irka Özel Fiyatlar --}}
            <div class="col-md-4">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Özel Fiyatlandırma</h5>
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
                                        @php
                                            $price = $service->breeds->find($breed->id)?->pivot->price ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $breed->name }}</td>
                                            <td>
                                                <input type="number"
                                                       step="0.01"
                                                       class="form-control breed-price"
                                                       name="breed_prices[{{ $breed->id }}]"
                                                       id="breed_price_{{ $breed->id }}"
                                                       placeholder="Varsayılan fiyatı kullan"
                                                       value="{{ old('breed_prices.'.$breed->id, $price) }}"
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
                 
            </div>
            <div class="d-end mt-3">
                    <x-adminlte-button type="submit" label="Hizmeti Güncelle" theme="warning" icon="fas fa-save"/>
                    <x-adminlte-button type="button" label="Sil" theme="danger" icon="fas fa-trash" onclick="confirmDelete({{ $service->id }})"/>

                </div>
            </form>

        </x-adminlte-card>
    </div>
    <form hidden id="deleteServiceForm-{{ $service->id }}" method="POST" action="{{ route('services.destroy', $service) }}" class="d-inline">
        @csrf
        @method('DELETE')
    </form>
</div>

@section('js')
<script>
    document.querySelectorAll('.btn-clear').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.target);
            if(target) target.value = '';
        });
    });

    function confirmDelete(serviceId) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu hizmeti silmek istediğinizden emin misiniz?",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'Vazgeç'
        }).then((result) => {
            console.log(result.valur);
            if (result.value) {
                document.getElementById('deleteServiceForm-' + serviceId).submit();
            }
        })
    }
</script>
@stop

@endsection
