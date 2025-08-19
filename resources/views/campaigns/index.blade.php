@extends('adminlte::page')

@section('title', 'Kampanyalar')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Kampanyalar</h1>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Kampanya
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kampanya Adı</th>
                        <th>İndirim</th>
                        <th>Tarih Aralığı</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->id }}</td>
                            <td>{{ $campaign->name }}</td>
                            <td>
                                {{ $campaign->discount_value }}
                                {{ $campaign->discount_type === 'percentage' ? '%' : '₺' }}
                            </td>
                            <td>
                                {{ $campaign->start_date->format('d.m.Y') }} - 
                                {{ $campaign->end_date->format('d.m.Y') }}
                            </td>
                            <td>
                                @if($campaign->is_active && $campaign->start_date <= now() && $campaign->end_date >= now())
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Pasif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('campaigns.show', $campaign) }}" 
                                   class="btn btn-sm btn-info" title="Görüntüle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('campaigns.edit', $campaign) }}" 
                                   class="btn btn-sm btn-primary" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('campaigns.destroy', $campaign) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('Bu kampanyayı silmek istediğinize emin misiniz?');">
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
                            <td colspan="6" class="text-center">Henüz kampanya bulunmamaktadır.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
@stop
