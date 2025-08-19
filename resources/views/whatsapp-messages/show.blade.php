@extends('adminlte::page')

@section('title', 'Mesaj Detayı #' . $message->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Mesaj Detayı #{{ $message->id }}</h1>
        <a href="{{ route('whatsapp-messages.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Genel Bilgiler
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Gönderim Tarihi:</th>
                        <td>{{ $message->created_at->format('d.m.Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Durum:</th>
                        <td>
                            @if($message->status === 'sent')
                                <span class="badge bg-success">Gönderildi</span>
                            @elseif($message->status === 'failed')
                                <span class="badge bg-danger">Hata</span>
                            @else
                                <span class="badge bg-secondary">{{ $message->status }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Mesaj Türü:</th>
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
                    </tr>
                </table>
            </div>
        </div>

        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Alıcı Bilgileri
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Telefon:</th>
                        <td>{{ $message->to }}</td>
                    </tr>
                    @if($message->appointment?->customer)
                        <tr>
                            <th>Müşteri:</th>
                            <td>{{ $message->appointment->customer->name }}</td>
                        </tr>
                    @endif
                    @if($message->appointment)
                        <tr>
                            <th>Randevu:</th>
                            <td>
                                <a href="{{ route('appointments.show', $message->appointment) }}" class="text-primary">
                                    #{{ $message->appointment->id }}
                                </a> - {{ $message->appointment->status->value }}
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comment-dots"></i>
                    Mesaj İçeriği
                </h3>
            </div>
            <div class="card-body">
                <div class="direct-chat-msg">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-left">
                            <i class="fab fa-whatsapp text-success"></i> 
                            {{ $message->appointment?->customer?->name ?? 'Sistem' }}
                        </span>
                        <span class="direct-chat-timestamp float-right">
                            {{ $message->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="direct-chat-text p-3 bg-light">
                        {!! nl2br(e($message->content)) !!}
                    </div>
                </div>
            </div>
        </div>

        @if($message->error_message)
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Hata Detayı
                    </h3>
                </div>
                <div class="card-body">
                    <pre class="mb-0">{{ $message->error_message }}</pre>
                </div>
            </div>
        @endif

        @if($message->metadata)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-code"></i>
                        Ham Veri
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <pre class="p-3 mb-0">{{ json_encode(json_decode($message->metadata), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@push('css')
<style>
    pre {
        background: #f4f6f9;
        padding: 10px;
        border-radius: 4px;
        max-height: 300px;
        overflow-y: auto;
    }
    .direct-chat-text {
        border-radius: 0.5rem;
        position: relative;
        padding: 0.5rem 1rem;
        background: #d2d6de;
        border: 1px solid #d2d6de;
        margin: 5px 0 0 0;
        color: #444;
    }
    .direct-chat-name {
        font-weight: 600;
    }
    .direct-chat-timestamp {
        color: #999;
        font-size: 0.8em;
    }
</style>
@endpush

@push('js')
<script>
    $(function () {
        // Enable tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize any other plugins if needed
    });
</script>
@endpush
