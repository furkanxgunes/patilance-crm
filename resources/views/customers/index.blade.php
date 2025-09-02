{{-- customers/index.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

@extends('adminlte::page')

@section('title', 'Müşteri Yönetimi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Müşteri Yönetimi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Müşteriler</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
<style>
        /* Simple pagination styles */
        .pagination {
            justify-content: center;
            margin: 1rem 0;
        }
        
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
            text-align: center;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #3490dc;
            border-color: #3490dc;
        }
    </style>
    {{-- Başarı Mesajı --}}
    @if (session('success'))
        <x-adminlte-alert theme="success" title="Başarılı">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    {{-- Ana içerik kutusu --}}
    <x-adminlte-card title="Müşteri Listesi" theme="info" icon="fas fa-users" collapsible>

        <div class="d-flex justify-content-between mb-3">
            <form method="GET" action="{{ route('customers.index') }}" class="form-inline">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="İsim, e-posta, telefon ara...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-light">Temizle</a>
                    </div>
                </div>
            </form>
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Yeni Müşteri Ekle
            </a>
        </div>

        {{-- Müşteriler Tablosu --}}
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Müşteri Adı</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th style="width: 180px;">İşlemler</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td class="text-center">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info" title="Detaylar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                           
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Henüz hiç müşteri eklenmemiş.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                {{ $customers->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </x-adminlte-card>
@stop
