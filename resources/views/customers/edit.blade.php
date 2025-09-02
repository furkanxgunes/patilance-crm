{{-- customers/edit.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Müşteri Düzenle: ' . $customer->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Müşteri Düzenle: {{ $customer->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Müşteriler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Düzenle</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Müşteri Güncelleme Formu" theme="warning" icon="fas fa-user-edit" collapsible>

                <form method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf
                    @method('PUT')
 

                    {{-- Müşteri Adı --}}
                    <x-adminlte-input name="name" label="Müşteri Adı" placeholder="Müşteri adını girin..."
                                      :value="old('name', $customer->name)" required />
                    {{-- Email --}}
                    <x-adminlte-input name="email" type="email" label="Email" placeholder="E-posta adresini girin..."
                                      :value="old('email', $customer->email)"  />

                    {{-- Telefon --}}
                    <x-adminlte-input name="phone" type="tel" label="Telefon" placeholder="Telefon numarasını girin..."
                                      :value="old('phone', $customer->phone)" required />

                    {{-- Adres --}}
                    <x-adminlte-input name="address" label="Adres" placeholder="Adresi girin..."
                                      :value="old('address', $customer->address)"  />

                                    <label for="segment_id">Müşteri Segmenti</label>
                                    <x-adminlte-select2 name="segment_id" label-class="text-primary" igroup-size="md">
                                        <option value="">Seçiniz</option>
                                        @foreach($segments as $segment)
                                            <option value="{{ $segment->id }}"
                                                {{ old('segment_id', $customer->segment_id ?? '') == $segment->id ? 'selected' : '' }}>
                                                <i class="{{ $segment->icon }}"></i> {{ $segment->name }}
                                            </option>
                                        @endforeach
                                    </x-adminlte-select2>
                                                    {{-- Notlar --}}
                    <x-adminlte-text-editor name="notes" label="Notlar (İsteğe Bağlı)"
                                            placeholder="Müşteriyle ilgili notlarınızı yazın...">
                        {{ old('notes', $customer->notes) }}
                    </x-adminlte-text-editor>

                    <div class="d-flex justify-content-end mt-3">
                        <x-adminlte-button type="submit" label="Müşteriyi Güncelle" theme="warning" icon="fas fa-save"/>
                    </div>
                </form>
                <form id="deleteCustomerForm-{{ $customer->id }}" method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger" title="Sil" onclick="confirmDelete({{ $customer->id }})">
                        <i class="fas fa-trash"></i> Müşteriyi Sil
                    </button>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@stop

<script>
    function confirmDelete(customerId) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu müşteriyi silmek istediğinizden emin misiniz? Tüm randevuları ve pet bilgileri de silinebilir!",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'Vazgeç'
        }).then((result) => {
            if (result.value) {
                document.getElementById('deleteCustomerForm-' + customerId).submit();
            }
        })
    }
</script>