@extends('adminlte::page')

@section('title', ($customer ? $customer->name . ' ' . $customer->surname : $wa_id) . ' Sohbet')

@section('content_header')
    <h1>
        @if ($customer)
            {{ $customer->name }} {{ $customer->surname }} ({{ $customer->phone }})
        @else
            {{ $wa_id }}
        @endif
        ile Sohbet
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline direct-chat direct-chat-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        @if ($customer)
                            {{ $customer->name }} {{ $customer->surname }}
                            <small class="text-muted ml-2">{{ $customer->phone }}</small>
                        @else
                            {{ $wa_id }}
                        @endif
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('whatsapp-messages.index') }}" class="btn btn-tool" data-toggle="tooltip" title="Sohbetlere Geri Dön">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages" style="height: 600px; overflow-y: auto;">

                        @forelse ($finalMessages as $message) {{-- $mergedMessages yerine $finalMessages kullanıyoruz --}}
                            {{-- Gelen mesaj (Müşteriden) --}}
                            @if ($message->direction === 'inbound')
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">
                                            @if ($customer)
                                                {{ $customer->name }}
                                            @else
                                                Müşteri ({{ $wa_id }})
                                            @endif
                                        </span>
                                        <span class="direct-chat-timestamp float-right">{{ $message->timestamp->diffForHumans() }}</span>
                                    </div>
                                    <!-- /.direct-chat-infos -->
                                    <img class="direct-chat-img" src="{{ asset('vendor/adminlte/dist/img/user1-128x128.jpg') }}" alt="Message User Image">
                                    <!-- /.direct-chat-img -->
                                    <div class="direct-chat-text">
                                        @if($message->log_type === 'text')
                                            {{ $message->content }}
                                        @elseif($message->log_type === 'sticker')
                                            <i class="far fa-sticky-note"></i> {{ $message->content }}
                                        @elseif($message->log_type === 'image')
                                            <i class="far fa-image"></i> {{ $message->content }}
                                        @elseif($message->log_type === 'video')
                                            <i class="far fa-video"></i> {{ $message->content }}
                                        @elseif($message->log_type === 'document')
                                            <i class="far fa-file-alt"></i> {{ $message->content }}
                                        @elseif($message->log_type === 'audio')
                                            <i class="fas fa-microphone"></i> {{ $message->content }}
                                        @else
                                            {{ $message->content }}
                                        @endif

                                        {{-- Bu mesaja verilen reaksiyonları göster --}}
                                        @if($message->reactions->isNotEmpty())
                                            <div class="message-reactions mt-1">
                                                @foreach($message->reactions as $reaction)
                                                    <span class="badge badge-light p-1 mr-1" data-toggle="tooltip" title="{{ $reaction->customer_name }} {{ $reaction->timestamp->diffForHumans() }}">
                                                        {{ $reaction->emoji }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <!-- /.direct-chat-text -->
                                </div>
                                <!-- /.direct-chat-msg -->
                            @else
                                {{-- Giden mesaj (Bizim sistemimizden) --}}
                                <div class="direct-chat-msg right">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-right">Ben</span>
                                        <span class="direct-chat-timestamp float-left">{{ $message->timestamp->diffForHumans() }}</span>
                                    </div>
                                    <!-- /.direct-chat-infos -->
                                    <img class="direct-chat-img" src="{{ asset('vendor/adminlte/dist/img/user8-128x128.jpg') }}" alt="Message User Image">
                                    <!-- /.direct-chat-img -->
                                    <div class="direct-chat-text">
                                        {{ $message->content }}

                                        {{-- Bu mesaja verilen reaksiyonları göster --}}
                                        @if($message->reactions->isNotEmpty())
                                            <div class="message-reactions mt-1 text-right">
                                                @foreach($message->reactions as $reaction)
                                                    <span class="badge badge-light p-1 ml-1" data-toggle="tooltip" title="{{ $reaction->customer_name }} {{ $reaction->timestamp->diffForHumans() }}">
                                                        {{ $reaction->emoji }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <!-- /.direct-chat-text -->
                                    <small class="text-muted float-left mt-1">
                                        @if ($message->status)
                                            @php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($message->status) {
                                                    case 'sent': $statusClass = 'text-primary'; $statusText = 'Gönderildi'; break;
                                                    case 'delivered': $statusClass = 'text-info'; $statusText = 'Teslim Edildi'; break;
                                                    case 'read': $statusClass = 'text-success'; $statusText = 'Okundu'; break;
                                                    case 'failed': $statusClass = 'text-danger'; $statusText = 'Başarısız'; break;
                                                    case 'pending': $statusClass = 'text-warning'; $statusText = 'Beklemede'; break;
                                                    case 'scheduled': $statusClass = 'text-secondary'; $statusText = 'Planlandı'; break;
                                                    default: $statusClass = 'text-muted'; $statusText = ucfirst($message->status); break;
                                                }
                                            @endphp
                                            <span class="{{ $statusClass }}"><i class="fas fa-check-double"></i> {{ $statusText }}</span>
                                        @endif
                                    </small>
                                </div>
                                <!-- /.direct-chat-msg -->
                            @endif
                        @empty
                            <div class="text-center text-muted mt-5">Bu sohbet için henüz mesaj bulunamadı.</div>
                        @endforelse

                    </div>
                    <!--/.direct-chat-messages-->
                </div>
                <!-- /.card-body -->
            </div>
            <!--/.direct-chat -->
        </div>
    </div>
@stop

@section('css')
    <style>
        .direct-chat-text {
            white-space: pre-wrap;
        }
        .message-reactions {
            display: inline-block; /* Reaksiyonları yan yana tutmak için */
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var chatBody = $('.direct-chat-messages');
            chatBody.scrollTop(chatBody.prop("scrollHeight"));

            // Tooltip'leri etkinleştir (reaksiyonlar için)
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop