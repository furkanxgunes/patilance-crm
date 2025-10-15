@props(['user'])

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
        <i class="far fa-bell"></i>
        <span id="notificationBadge" class="badge badge-warning navbar-badge" style="display: none;">
            0
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header" id="notificationCount">
            <i class="fas fa-tasks mr-1"></i> Günlük Görevler
        </span>
        <div class="dropdown-divider"></div>
        <div id="notificationList">
            <a href="#" class="dropdown-item">
                <i class="fas fa-spinner fa-spin mr-2"></i> Yükleniyor...
            </a>
        </div>
        <div class="dropdown-divider"></div>
        <a href="{{ route('dashboard2') }}" class="dropdown-item dropdown-footer">
            <i class="fas fa-calendar-day mr-1"></i> Bugünün Tamamını Gör
        </a>
    </div>
</li>

@push('js')
<script>
console.log('Notification script loaded');
console.log('jQuery available:', typeof $ !== 'undefined');
console.log('Fetch URL:', '{{ route("notifications.fetch") }}');

// jQuery varsa kullan, yoksa vanilla JS
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        console.log('Document ready with jQuery');
        initNotifications();
    });
} else {
    // Vanilla JS fallback
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document ready with vanilla JS');
            initNotificationsVanilla();
        });
    } else {
        console.log('Document already ready with vanilla JS');
        initNotificationsVanilla();
    }
}

function initNotifications() {
    console.log('Initializing notifications with jQuery');
    
    function updateNotifications() {
        console.log('Fetching notifications...');
        
        $.ajax({
            url: '{{ route("notifications.fetch") }}',
            method: 'GET',
            dataType: 'json',
            success: function(notifications) {
                console.log('Notifications received:', notifications);
                renderNotifications(notifications);
            },
            error: function(xhr, status, error) {
                console.error('Notification fetch failed:', {xhr, status, error});
                $('#notificationList').html(`
                    <a href="#" class="dropdown-item text-center text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i> 
                        Hata: ${error || 'Bilinmeyen hata'}
                    </a>
                `);
            }
        });
    }
    
    function renderNotifications(notifications) {
        const $list = $('#notificationList');
        const $badge = $('#notificationBadge');
        const $count = $('#notificationCount');
        
        if (notifications.length === 0) {
            $list.html(`
                <a href="{{ route('dashboard2') }}" class="dropdown-item text-center text-muted">
                    <i class="fas fa-check-circle mr-2 text-success"></i> 
                    Tüm görevler tamamlandı!
                </a>
            `);
            $badge.hide();
            $count.html('<i class="fas fa-check-circle mr-1 text-success"></i> Tüm Görevler Tamamlandı');
            return;
        }

        const urgentCount = notifications.filter(n => n.priority === 'high').length;
        if (urgentCount > 0) {
            $badge.text(urgentCount).show();
            $count.html(`<i class="fas fa-exclamation-circle mr-1 text-danger"></i> ${urgentCount} Acil Görev`);
        } else {
            $badge.hide();
            $count.html(`<i class="fas fa-tasks mr-1"></i> ${notifications.length} Görev`);
        }

        let html = '';
        notifications.forEach(notif => {
            let priorityClass = '';
            let priorityIcon = '';
            
            if (notif.priority === 'high') {
                priorityClass = 'border-left border-danger';
                priorityIcon = '<i class="fas fa-exclamation-circle text-danger mr-1"></i>';
            } else if (notif.priority === 'normal') {
                priorityClass = 'border-left border-warning';
                priorityIcon = '<i class="fas fa-circle text-warning mr-1" style="font-size: 8px;"></i>';
            } else {
                priorityClass = 'border-left border-info';
                priorityIcon = '<i class="fas fa-circle text-info mr-1" style="font-size: 8px;"></i>';
            }

            html += `
                <a href="${notif.url}" class="dropdown-item ${priorityClass}" style="border-left-width: 3px !important;">
                    <div class="media">
                        <div class="media-body">
                            <h3 class="dropdown-item-title" style="font-size: 14px;">
                                ${priorityIcon}${notif.title}
                                <span class="float-right text-sm text-muted">
                                    <i class="far fa-clock mr-1"></i> ${notif.time}
                                </span>
                            </h3>
                            <p class="text-sm text-muted mb-0">${notif.message}</p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            `;
        });
        
        $list.html(html);
    }

    // İlk yüklemede çalıştır
    updateNotifications();

    // Dropdown açıldığında güncelle
    $('#notificationDropdown').on('click', function() {
        console.log('Notification dropdown clicked');
        updateNotifications();
    });

    // Her 60 saniyede bir otomatik güncelle
    setInterval(updateNotifications, 60000);
}

function initNotificationsVanilla() {
    console.log('Initializing notifications with vanilla JS');
    
    function updateNotifications() {
        console.log('Fetching notifications with fetch API...');
        
        fetch('{{ route("notifications.fetch") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(notifications => {
                console.log('Notifications received:', notifications);
                renderNotificationsVanilla(notifications);
            })
            .catch(error => {
                console.error('Notification fetch failed:', error);
                document.getElementById('notificationList').innerHTML = `
                    <a href="#" class="dropdown-item text-center text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i> 
                        Hata: ${error.message}
                    </a>
                `;
            });
    }
    
    function renderNotificationsVanilla(notifications) {
        const list = document.getElementById('notificationList');
        const badge = document.getElementById('notificationBadge');
        const count = document.getElementById('notificationCount');
        
        if (notifications.length === 0) {
            list.innerHTML = `
                <a href="{{ route('dashboard2') }}" class="dropdown-item text-center text-muted">
                    <i class="fas fa-check-circle mr-2 text-success"></i> 
                    Tüm görevler tamamlandı!
                </a>
            `;
            badge.style.display = 'none';
            count.innerHTML = '<i class="fas fa-check-circle mr-1 text-success"></i> Tüm Görevler Tamamlandı';
            return;
        }

        const urgentCount = notifications.filter(n => n.priority === 'high').length;
        if (urgentCount > 0) {
            badge.textContent = urgentCount;
            badge.style.display = 'inline';
            count.innerHTML = `<i class="fas fa-exclamation-circle mr-1 text-danger"></i> ${urgentCount} Acil Görev`;
        } else {
            badge.style.display = 'none';
            count.innerHTML = `<i class="fas fa-tasks mr-1"></i> ${notifications.length} Görev`;
        }

        let html = '';
        notifications.forEach(notif => {
            let priorityClass = '';
            let priorityIcon = '';
            
            if (notif.priority === 'high') {
                priorityClass = 'border-left border-danger';
                priorityIcon = '<i class="fas fa-exclamation-circle text-danger mr-1"></i>';
            } else if (notif.priority === 'normal') {
                priorityClass = 'border-left border-warning';
                priorityIcon = '<i class="fas fa-circle text-warning mr-1" style="font-size: 8px;"></i>';
            } else {
                priorityClass = 'border-left border-info';
                priorityIcon = '<i class="fas fa-circle text-info mr-1" style="font-size: 8px;"></i>';
            }

            html += `
                <a href="${notif.url}" class="dropdown-item ${priorityClass}" style="border-left-width: 3px !important;">
                    <div class="media">
                        <div class="media-body">
                            <h3 class="dropdown-item-title" style="font-size: 14px;">
                                ${priorityIcon}${notif.title}
                                <span class="float-right text-sm text-muted">
                                    <i class="far fa-clock mr-1"></i> ${notif.time}
                                </span>
                            </h3>
                            <p class="text-sm text-muted mb-0">${notif.message}</p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            `;
        });
        
        list.innerHTML = html;
    }

    // İlk yüklemede çalıştır
    updateNotifications();

    // Dropdown açıldığında güncelle
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.addEventListener('click', function() {
            console.log('Notification dropdown clicked');
            updateNotifications();
        });
    }

    // Her 60 saniyede bir otomatik güncelle
    setInterval(updateNotifications, 60000);
}
</script>
@endpush
