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
                                      :value="old('email', $customer->email)" required />

                    {{-- Telefon --}}
                    <x-adminlte-input name="phone" type="tel" label="Telefon" placeholder="Telefon numarasını girin..."
                                      :value="old('phone', $customer->phone)" required />

                    {{-- Adres --}}
                    <x-adminlte-input name="address" label="Adres" placeholder="Adresi girin..."
                                      :value="old('address', $customer->address)" required />

                    {{-- Notlar --}}
                    <x-adminlte-text-editor name="notes" label="Notlar (İsteğe Bağlı)"
                                            placeholder="Müşteriyle ilgili notlarınızı yazın...">
                        {{ old('notes', $customer->notes) }}
                    </x-adminlte-text-editor>

                    <div class="d-flex justify-content-end mt-3">
                        <x-adminlte-button type="submit" label="Müşteriyi Güncelle" theme="warning" icon="fas fa-save"/>
                    </div>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop