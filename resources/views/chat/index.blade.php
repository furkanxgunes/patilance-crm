{{-- resources/views/chat/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'WhatsApp Sohbetleri')

@section('content_header')
    <h1>WhatsApp Sohbetleri</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Numara (wa_id)</th>
          <th>Son Aktivite</th>
          <th>Gelen</th>
          <th>Giden</th>
          <th style="width:100px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($threads as $t)
          <tr>
            <td>{{ $t->wa_id }}</td>
            <td>{{ \Carbon\Carbon::parse($t->last_at)->format('Y-m-d H:i') }}</td>
            <td><span class="badge bg-success">{{ $t->inbound_count }}</span></td>
            <td><span class="badge bg-primary">{{ $t->outbound_count }}</span></td>
            <td>
              <a href="{{ route('whatsapp-messages.show', $t->wa_id) }}" class="btn btn-sm btn-outline-primary">
                Görüntüle
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted p-4">Sohbet bulunamadı.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($threads->hasPages())
    <div class="card-footer clearfix">
      {{ $threads->onEachSide(1)->links() }}
    </div>
  @endif
</div>
@stop
