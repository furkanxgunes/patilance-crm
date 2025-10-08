@extends('adminlte::page')

@section('plugins.Summernote', true)
@section('plugins.Select2', true)

{{-- Eğer customer değişkeni varsa, başlık müşterinin adını içerecek. Yoksa, genel bir başlık olacak. --}}
@section('title', isset($customer) ? $customer->name . ' için Yeni Pet Ekle' : 'Yeni Pet Ekle')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ isset($customer) ? $customer->name . ' için Yeni Pet Ekle' : 'Yeni Pet Ekle' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                @if(isset($customer))
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Müşteriler</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Petler</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">Yeni</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Zorunlu Alanlar" theme="orange" icon="fas fa-paw" collapsible>

                <form method="POST" action="{{ route('pets.store') }}" class="form-horizontal">
                    @csrf
                    
                    {{-- Eğer müşteri bilgisi varsa, hidden input olarak ekle. Yoksa, tüm müşterileri listeleyen bir select box göster. --}}
                    @if(isset($customer))
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <div class="alert alert-info">
                            <strong>Müşteri:</strong> {{ $customer->name }} için yeni pet ekliyorsunuz.
                        </div>
                    @else
                        <div class="form-group row">
                            <label for="customer_id" class="col-sm-3 col-form-label">Müşteri</label>
                            <div class="col-sm-9">
                                <select id="customer_id" name="customer_id" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Müşteri Seçiniz --</option>
                                    @foreach($customers as $customerItem)
                                        <option value="{{ $customerItem->id }}" {{ old('customer_id') == $customerItem->id ? 'selected' : '' }}>
                                            {{ $customerItem->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div class="form-group row">
                        <label for="name" class="col-sm-3 col-form-label">Pet Adı</label>
                        <div class="col-sm-9">
                            <input type="text" id="name" name="name" class="form-control" placeholder="Pet adını girin..." value="{{ old('name') }}" required />
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="species" class="col-sm-3 col-form-label">Tür</label>
                        <div class="col-sm-9">
                            <select id="species" name="species" class="form-control" required>
                                <option value="Köpek" {{ old('species') == 'Köpek' ? 'selected' : '' }}>Köpek</option>
                                <option value="Kedi" {{ old('species') == 'Kedi' ? 'selected' : '' }}>Kedi</option>
                            </select>
                            @error('species')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="breed" class="col-sm-3 col-form-label">Irk</label>
                        <div class="col-sm-9">
                            <select name="breed_id" id="breed_id" class="form-control select2" style="width: 100%;" required>
                                @foreach($breeds as $breed)
                                    <option value="{{ $breed->id }}" {{ old('breed_id') == $breed->id ? 'selected' : '' }}>
                                        {{ $breed->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('breed_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="age" class="col-sm-3 col-form-label">Yaş</label>
                        <div class="col-sm-9">
                            <input type="number" step="1" id="age" name="age" class="form-control" placeholder="Pet'in yaşını girin..." value="{{ old('age') }}" />
                            @error('age')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="gender" class="col-sm-3 col-form-label">Cinsiyet</label>
                        <div class="col-sm-9">
                            <select id="gender" name="gender" class="form-control">
                                <option value="" disabled @if(!old('gender')) selected @endif>Seçiniz</option>
                                <option value="Erkek" {{ old('gender') == 'Erkek' ? 'selected' : '' }}>Erkek</option>
                                <option value="Dişi" {{ old('gender') == 'Dişi' ? 'selected' : '' }}>Dişi</option>
                            </select>
                            @error('gender')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="weight_kg" class="col-sm-3 col-form-label">Kilo (kg)</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" id="weight_kg" name="weight_kg" class="form-control" placeholder="Pet'in kilosunu girin..." value="{{ old('weight_kg') }}" />
                            @error('weight_kg')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Sadece zorunlu alanları istiyoruz. Diğer alanlar düzenleme ekranında doldurulacak. --}}

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Pet'i Kaydet
                            </button>
                        </div>
                    </div>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
<style>
    /* Custom styling for Select2 */
    .select2-container--default .select2-selection--single {
        height: auto !important;
        min-height: 38px;
        padding: 6px 12px;
        border: 1px solid #d2d6de;
        border-radius: 4px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize Select2 for customer and breed selection
        $('.select2').select2({
            placeholder: 'Seçiniz...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return 'Sonuç bulunamadı';
                },
                searching: function() {
                    return 'Aranıyor...';
                },
                inputTooShort: function(args) {
                    return 'En az ' + args.minimum + ' karakter giriniz';
                }
            }
        });

        // Initialize Select2 for customer selection with custom placeholder
        $('#customer_id').data('select2').$container.find('.select2-selection').attr('title', 'Müşteri ara...');
        
        // Initialize Select2 for breed selection with custom placeholder
        $('#breed_id').data('select2').$container.find('.select2-selection').attr('title', 'Irk ara...');
    });
</script>
@stop