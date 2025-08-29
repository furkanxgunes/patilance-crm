{{-- profile/edit.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Profil Bilgileri')

@section('content_header')
    <h1>Profil Bilgileri</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            {{-- Profil Bilgilerini Güncelleme Formu --}}
            <x-adminlte-card title="Profil Bilgilerini Güncelle" theme="primary" icon="fas fa-user-edit" class="mb-4">
                @include('profile.partials.update-profile-information-form')
            </x-adminlte-card>
        </div>
        <div class="col-md-6">
            {{-- Şifre Güncelleme Formu --}}
            <x-adminlte-card title="Şifreyi Güncelle" theme="warning" icon="fas fa-lock" class="mb-4">
                @include('profile.partials.update-password-form')
            </x-adminlte-card>
        </div>
        <!-- <div class="col-md-6">
            {{-- Hesabı Silme Formu --}}
            <x-adminlte-card title="Hesabı Sil" theme="danger" icon="fas fa-trash-alt" class="mb-4">
                @include('profile.partials.delete-user-form')
            </x-adminlte-card>
        </div> -->
    </div>
@stop