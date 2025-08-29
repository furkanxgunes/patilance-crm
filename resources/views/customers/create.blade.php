{{-- customers/create.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Yeni Müşteri Ekle')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Yeni Müşteri Ekle</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Müşteriler</a></li>
                <li class="breadcrumb-item active" aria-current="page">Yeni</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Yeni Müşteri Kayıt Formu" theme="primary" icon="fas fa-user-plus" collapsible>
                
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf

                    {{-- Müşteri Adı --}}
                    <x-adminlte-input name="name" label="Müşteri Adı" placeholder="Müşteri adını girin..." required />
                    
                    {{-- Email --}}
                    <x-adminlte-input name="email" type="email" label="Email" placeholder="E-posta adresini girin..." required />
                    
                    {{-- Telefon --}}
                    <x-adminlte-input name="phone" type="tel" label="Telefon" placeholder="Telefon numarasını girin..." required />

                    {{-- Adres --}}
                    <x-adminlte-input name="address" label="Adres" placeholder="Adresi girin..." required />
                    <div class="form-group">
    <label for="segment_id">Müşteri Segmenti</label>
    <x-adminlte-select2 name="segment_id" label-class="text-primary" igroup-size="md">
        <option value="">Seçiniz</option>
        @foreach(\App\Models\Segment::all() as $segment)
            <option value="{{ $segment->id }}"
                {{ old('segment_id', $customer->segment_id ?? '') == $segment->id ? 'selected' : '' }}>
                <i class="{{ $segment->icon }}"></i> {{ $segment->name }}
            </option>
        @endforeach
    </x-adminlte-select2>
</div>


                    {{-- Notlar --}}
                    <x-adminlte-text-editor name="notes" label="Notlar (İsteğe Bağlı)" placeholder="Müşteriyle ilgili notlarınızı yazın..." />

                    <x-adminlte-button class="d-flex justify-content-end mt-3" type="submit" label="Müşteriyi Kaydet" theme="success" icon="fas fa-save"/>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop