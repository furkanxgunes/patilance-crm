@extends('adminlte::page')

@section('title', 'Evcil Hayvan Yönetimi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Evcil Hayvan Yönetimi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Petler</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Evcil Hayvan Listesi" theme="orange" icon="fas fa-paw" collapsible>
                
                {{-- Başarı Mesajı --}}
                @if (session('success'))
                    <x-adminlte-alert theme="success" title="Başarılı">
                        {{ session('success') }}
                    </x-adminlte-alert>
                @endif
                
                <div class="d-flex justify-content-between mb-3">
                    <form method="GET" action="{{ route('pets.index') }}" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Pet adı, tür, ırk veya müşteri adı">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                <a href="{{ route('pets.index') }}" class="btn btn-outline-light">Temizle</a>
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('pets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Pet Ekle
                    </a>
                </div>

                {{-- Evcil Hayvanlar Tablosu --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead> 
                            <tr>
                                <th>Adı</th>
                                <th>Tür</th>
                                <th>Irk</th>
                                <th>Yaş</th>
                                <th>Müşteri</th>
                                <th style="width: 180px;">İşlemler</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            @forelse ($pets as $pet)
                                <tr>
                                    <td>{{ $pet->name }}</td>
                                    <td>{{ $pet->species }}</td>
                                    <td>{{ $pet->breed }}</td>
                                    <td>{{ $pet->age ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('customers.show', $pet->customer->id) }}" class="text-info">
                                            {{ $pet->customer->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('pets.edit', $pet) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('pets.show', $pet) }}" class="btn btn-sm btn-info" title="Detaylar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                        <form method="POST" action="{{ route('pets.destroy', $pet) }}" class="d-inline" onsubmit="return confirm('Bu peti silmek istediğinizden emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Henüz hiç evcil hayvan eklenmemiş.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $pets->links() }}
                </div>

            </x-adminlte-card>
        </div>
    </div>
@stop