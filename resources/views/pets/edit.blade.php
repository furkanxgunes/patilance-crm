@extends('adminlte::page')

@section('plugins.Summernote', true)

@section('title', $pet->name . ' Adlı Peti Düzenliyorsun')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $pet->name }} Adlı Peti Düzenliyorsun</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Petler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pets.show', $pet) }}">{{ $pet->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Düzenle</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <form method="POST" action="{{ route('pets.update', $pet) }}" class="form-horizontal">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12 col-lg-6">
                <x-adminlte-card title="Genel Bilgiler (Zorunlu)" theme="warning" icon="fas fa-list-check" collapsible>
            <div class="form-group row">
                <label for="customer_id" class="col-sm-3 col-form-label">Müşteri</label>
                <div class="col-sm-9">
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $pet->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="name" class="col-sm-3 col-form-label">Pet Adı</label>
                <div class="col-sm-9">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Pet adını girin..." value="{{ old('name', $pet->name) }}" required />
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="species" class="col-sm-3 col-form-label">Tür</label>
                <div class="col-sm-9">
                    <select id="species" name="species" class="form-control" required>
                        <option value="Köpek" @if(old('species', $pet->species) == 'Köpek') selected @endif>Köpek</option>
                        <option value="Kedi" @if(old('species', $pet->species) == 'Kedi') selected @endif>Kedi</option>
                    </select>
                    @error('species')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="breed" class="col-sm-3 col-form-label">Irk</label>
                <div class="col-sm-9">
                <select name="breed_id" id="breed_id" class="form-control" required>
                            @foreach($breeds as $breed)
                                <option value="{{ $breed->id }}" {{ old('breed_id', $pet->breed_id) == $breed->id ? 'selected' : '' }}>
                                    {{ $breed->name }}
                                </option>
                            @endforeach
                        </select>                    @error('breed')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="gender" class="col-sm-3 col-form-label">Cinsiyet</label>
                <div class="col-sm-9">
                    <select id="gender" name="gender" class="form-control">
                        <option value="" disabled @if(!old('gender', $pet->gender)) selected @endif>Seçiniz</option>
                        <option value="Erkek" @if(old('gender', $pet->gender) == 'Erkek') selected @endif>Erkek</option>
                        <option value="Dişi" @if(old('gender', $pet->gender) == 'Dişi') selected @endif>Dişi</option>
                    </select>
                    @error('gender')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="age" class="col-sm-3 col-form-label">Yaş</label>
                <div class="col-sm-9">
                    <input type="number" step="1" id="age" name="age" class="form-control" placeholder="Pet'in yaşını girin..." value="{{ old('age', $pet->age) }}" />
                    @error('age')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="weight_kg" class="col-sm-3 col-form-label">Kilo (kg)</label>
                <div class="col-sm-9">
                    <input type="number" step="0.01" id="weight_kg" name="weight_kg" class="form-control" placeholder="Pet'in kilosunu girin..." value="{{ old('weight_kg', $pet->weight_kg) }}" />
                    @error('weight_kg')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

                </x-adminlte-card>
            </div>

            <div class="col-12 col-lg-6">
                <x-adminlte-card title="Diğer Bilgiler" theme="light" icon="fas fa-info-circle" collapsible>

            

            <div class="form-group row">
                <label for="veterinarian_info" class="col-sm-3 col-form-label">Veteriner Bilgileri</label>
                <div class="col-sm-9">
                    <input type="text" id="veterinarian_info" name="veterinarian_info" class="form-control" placeholder="Veteriner bilgilerini girin..." value="{{ old('veterinarian_info', $pet->veterinarian_info) }}" />
                    @error('veterinarian_info')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group row">
                <label for="chip_no" class="col-sm-3 col-form-label">Çip Numarası</label>
                <div class="col-sm-9">
                    <input type="text" id="chip_no" name="chip_no" class="form-control" placeholder="Çip numarasını girin..." value="{{ old('chip_no', $pet->chip_no) }}" />
                    @error('chip_no')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group row">
                <label for="appearance" class="col-sm-3 col-form-label">Eşgali</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="appearance" name="appearance" placeholder="Eşgali (isteğe bağlı)" rows="3">{{ old('appearance', $pet->appearance) }}</textarea>
                    @error('appearance')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="special_marks" class="col-sm-3 col-form-label">Özel işaretleri</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="special_marks" name="special_marks" placeholder="Özel işaretleri (isteğe bağlı)" rows="3">{{ old('special_marks', $pet->special_marks) }}</textarea>
                    @error('special_marks')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="habits_toilet" class="col-sm-3 col-form-label">Huyları, tuvalet alışkanlığı</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="habits_toilet" name="habits_toilet" placeholder="Huyları ve tuvalet alışkanlığı (isteğe bağlı)" rows="3">{{ old('habits_toilet', $pet->habits_toilet) }}</textarea>
                    @error('habits_toilet')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="vaccines" class="col-sm-3 col-form-label">Aşılar ve tarihleri</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="vaccines" name="vaccines" placeholder="Aşılar ve tarihleri (isteğe bağlı)" rows="3">{{ old('vaccines', $pet->vaccines) }}</textarea>
                    @error('vaccines')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group row">
                <label for="allergies" class="col-sm-3 col-form-label">Alerjiler</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="allergies" name="allergies" placeholder="Alerjileri buraya yazın..." rows="3">{{ old('allergies', $pet->allergies) }}</textarea>
                    @error('allergies')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Kullandığı preparatlar</label>
                <div class="col-sm-9">
                    <x-adminlte-text-editor 
                        name="medications_text" 
                        placeholder="Kullandığı preparatları buraya yazın..."
                    >
                        {{ old('medications_text', $pet->medications_text) }}
                    </x-adminlte-text-editor>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Bilgileri Güncelle
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $pet->id }})">
                        <i class="fas fa-trash"></i> Peti Sil
                    </button>
                </div>
            </div>
            
                </x-adminlte-card>
            </div>
        </div>
    </form>
  
    <form hidden id="deletePetForm-{{ $pet->id }}" method="POST" action="{{ route('pets.destroy', $pet) }}" class="d-inline">
        @csrf
        @method('DELETE')
    </form>
@stop

<script>
    function confirmDelete(petId) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu peti silmek istediğinizden emin misiniz? Tüm randevuları da silinebilir!",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'Vazgeç'
        }).then((result) => {
            console.log(result.value);
            if (result.value) {
                document.getElementById('deletePetForm-' + petId).submit();
            }
        })
    }
</script>