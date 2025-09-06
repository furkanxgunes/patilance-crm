@extends('adminlte::page')

@section('title', 'WhatsApp Mesajları')

@section('content_header')
    <h1>Gönderilen WhatsApp Mesajları</h1>
@stop

@section('content')
    <div class="card">
    <div class="card-header">
        <h3 class="card-title">Toplam: {{ $messages->total() }} mesaj</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Ara">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tarih</th>
                <th>Alıcı</th>
                <th>Telefon</th>
                <th>Randevu</th>
                <th>Mesaj Türü</th>
                <th>Durum</th>
                <th>İşlemler</th>
            </tr>
            </thead>
                                <tbody>
                                @forelse($messages as $message)
                                    <tr>
                            <td>{{ $message->id }}</td>
                            <td>{{ $message->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($message->appointment?->customer)
                                    {{ $message->appointment->customer->name }}
                                    <small class="d-block text-muted">{{ $message->to }}</small>
                                @else
                                    <span class="text-muted">Müşteri bulunamadı</span>
                                @endif
                            </td>
                            <td>{{ $message->to }}</td>
                            <td>
                                @if($message->appointment)
                                    <a href="{{ route('appointments.show', $message->appointment) }}" class="text-primary">
                                        #{{ $message->appointment->id }}
                                    </a> - {{ $message->appointment->status->value }}
                                @else
                                    <span class="text-muted">Randevu bulunamadı</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $types = [
                                        'appointment_scheduled' => 'Randevu Oluşturuldu',
                                        'appointment_updated' => 'Randevu Güncellendi',
                                        'appointment_cancelled' => 'Randevu İptal Edildi',
                                    ];
                                @endphp
                                <span class="badge bg-info">
                                    {{ $types[$message->type] ?? $message->type }}
                                </span>
                            </td>
                            <td>
                                @if($message->status === 'sent')
                                    <span class="badge bg-success">Gönderildi</span>
                                @elseif($message->status === 'failed')
                                    <span class="badge bg-danger">Hata</span>
                                @else
                                    <span class="badge bg-secondary">{{ $message->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('whatsapp-messages.show', $message) }}" 
                                   class="btn btn-sm btn-info"
                                   title="Detayları Görüntüle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="far fa-comment-dots fa-2x text-muted mb-2 d-block"></i>
                            <span class="text-muted">Henüz mesaj bulunmamaktadır.</span>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $messages->links() }}
            </div>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
                },
                "responsive": true,
                "autoWidth": false,
            });
        });
    </script>
@stop
