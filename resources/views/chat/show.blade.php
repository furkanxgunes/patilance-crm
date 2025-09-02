{{-- resources/views/chat/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Sohbet: '.$wa_id)

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">Sohbet: {{ $wa_id }}</h1>
    <a href="{{ route('whatsapp-messages.index') }}" class="btn btn-sm btn-secondary">← Listeye Dön</a>
  </div>
@stop

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card card-primary card-outline direct-chat direct-chat-primary">
      <div class="card-header">
        <h3 class="card-title">Mesajlar</h3>
        <div class="card-tools">
          <span title="Mesaj sayısı" class="badge bg-primary">{{ $messages->count() }}</span>
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="card-body">
        <div class="direct-chat-messages" style="min-height:420px; max-height:520px; overflow-y:auto;">
          @forelse($messages as $m)
            @php
              $isInbound = $m->direction === 'inbound';
              $bubbleClass = $isInbound ? '' : 'right';
              $time = optional($m->created_at)->format('Y-m-d H:i');
              $text = $m->body ?: '['.($m->type ?? 'message').']';
            @endphp

            <div class="direct-chat-msg {{ $bubbleClass }}">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name {{ $isInbound ? 'float-left' : 'float-right' }}">
                  {{ $isInbound ? 'Müşteri' : 'Biz' }}
                </span>
                <span class="direct-chat-timestamp {{ $isInbound ? 'float-right' : 'float-left' }}">
                  {{ $time }}
                  @if($m->status) • {{ $m->status }} @endif
                </span>
              </div>
              <img class="direct-chat-img" src="https://ui-avatars.com/api/?name={{ $isInbound ? 'M' : 'B' }}" alt="img">
              <div class="direct-chat-text">
                {{ $text }}
              </div>
            </div>
          @empty
            <div class="text-muted p-4 text-center">Bu sohbette mesaj yok.</div>
          @endforelse
        </div>
      </div>

      {{-- (Opsiyonel) Gönderim formu: kendi send endpoint'in varsa ekleyebilirsin
      <div class="card-footer">
        <form action="{{ route('whatsapp.send', $wa_id) }}" method="POST">
          @csrf
          <div class="input-group">
            <input type="text" name="body" placeholder="Mesaj yazın..." class="form-control">
            <span class="input-group-append">
              <button type="submit" class="btn btn-primary">Gönder</button>
            </span>
          </div>
        </form>
      </div>
      --}}
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title">Detay</h3></div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-5">Numara</dt>
          <dd class="col-sm-7">{{ $wa_id }}</dd>

          <dt class="col-sm-5">Toplam Mesaj</dt>
          <dd class="col-sm-7">{{ $messages->count() }}</dd>

          @php
            $in = $messages->where('direction','inbound')->count();
            $out = $messages->where('direction','outbound')->count();
            $st = $messages->where('direction','status')->count();
          @endphp

          <dt class="col-sm-5">Gelen</dt>
          <dd class="col-sm-7"><span class="badge bg-success">{{ $in }}</span></dd>

          <dt class="col-sm-5">Giden (log)</dt>
          <dd class="col-sm-7"><span class="badge bg-primary">{{ $out }}</span></dd>

          <dt class="col-sm-5">Durum</dt>
          <dd class="col-sm-7"><span class="badge bg-info">{{ $st }}</span></dd>
        </dl>
      </div>
    </div>
  </div>
</div>
@stop
