@extends('adminlte::page')

@section('title', 'WhatsApp Sohbetleri')

@section('content_header')
    <h1>WhatsApp Sohbetleri</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tüm Sohbetler</h3>
                    <div class="card-tools">
                        <form action="{{ route('whatsapp-messages.index') }}" method="GET">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control float-right" placeholder="Müşteri Adı/No Ara" value="{{ $search ?? '' }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Müşteri</th>
                                <th>Telefon</th>
                              
                                <th>Son Mesaj Durumu</th>
                                <th>Son İletişim</th>
                                <th style="width: 40px">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($threads as $thread)
                                <tr>
                                    <td>{{ $loop->iteration + ($threads->currentPage() - 1) * $threads->perPage() }}</td>
                                    <td>
                                        @if ($thread->customer)
                                            <a href="{{ route('customers.show', $thread->customer->id) }}">
                                                {{ $thread->customer->name }} {{ $thread->customer->surname }}
                                            </a>
                                        @else
                                            Bilinmeyen Müşteri
                                        @endif
                                    </td>
                                    <td>
                                        @if ($thread->customer)
                                            {{ $thread->customer->phone }}
                                        @else
                                            {{ $thread->wa_id }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $displayStatus = '';
                                            $statusBadge = '';

                                            if ($thread->last_status_direction === 'inbound') {
                                                $displayStatus = 'Müşteriden Yanıt';
                                                $statusBadge = 'badge-success'; // Yeşil, yeni bir yanıt var
                                            } else { // outbound mesaj
                                                switch ($thread->last_status) {
                                                    case 'sent':
                                                        $displayStatus = 'Gönderildi';
                                                        $statusBadge = 'badge-primary';
                                                        break;
                                                    case 'delivered':
                                                        $displayStatus = 'Teslim Edildi';
                                                        $statusBadge = 'badge-info';
                                                        break;
                                                    case 'read':
                                                        $displayStatus = 'Okundu';
                                                        $statusBadge = 'badge-success';
                                                        break;
                                                    case 'failed':
                                                        $displayStatus = 'Başarısız';
                                                        $statusBadge = 'badge-danger';
                                                        break;
                                                    default:
                                                        $displayStatus = 'Bilinmiyor';
                                                        $statusBadge = 'badge-light';
                                                        break;
                                                }
                                            }
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ $displayStatus }}</span>
                                    </td>
                                    <td>{{ $thread->last_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('chat.show', $thread->wa_id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Görüntüle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Gösterilecek sohbet bulunamadı.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    @if($threads->hasPages())
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $threads->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop