@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bildirimlerim</span>
                    <button id="markAllAsRead" class="btn btn-sm btn-outline-primary">
                        Tümünü Okundu İşaretle
                    </button>
                </div>

                <div class="card-body">
                    @if($notifications->isEmpty())
                        <div class="alert alert-info">
                            Hiç bildiriminiz bulunmuyor.
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <a href="{{ $notification->data['url'] ?? '#' }}" 
                                   class="list-group-item list-group-item-action {{ is_null($notification->read_at) ? 'bg-light' : '' }}"
                                   data-id="{{ $notification->id }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} mr-2"></i>
                                            {{ $notification->data['title'] ?? 'Bildirim' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                                </a>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Mark as read when clicked
    $('.list-group-item').on('click', function(e) {
        const $item = $(this);
        const notificationId = $item.data('id');
        
        if (!$item.hasClass('read')) {
            $.post(`/notifications/mark-as-read/${notificationId}`, {
                _token: '{{ csrf_token() }}'
            });
        }
    });

    // Mark all as read
    $('#markAllAsRead').on('click', function(e) {
        e.preventDefault();
        
        $.post('{{ route("notifications.mark-all-read") }}', {
            _token: '{{ csrf_token() }}'
        }, function() {
            $('.list-group-item').removeClass('bg-light');
            $('.unread-badge').hide();
            $('#markAllAsRead').prop('disabled', true);
        });
    });
});
</script>
@endpush
@endsection
