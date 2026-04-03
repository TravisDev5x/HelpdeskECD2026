<div class="nav-item dropdown" wire:poll.30s="refreshNotifications">
    <div id="internal-notification-toast-host" class="internal-notification-toast-host" aria-live="polite"></div>
    <a class="nav-link helpdesk-bell-trigger" data-toggle="dropdown" href="#" title="Notificaciones">
        <i class="fas fa-bell helpdesk-bell-icon" aria-hidden="true"></i>
        @if($unreadCount > 0)
            <span class="badge badge-warning navbar-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">{{ $unreadCount }} notificaciones sin leer</span>
        <div class="dropdown-divider"></div>
        @forelse($unreadNotifications as $notification)
            @php
                $raw = $notification->data ?? [];
                $data = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
                $title = $data['title'] ?? 'Notificación';
                $message = $data['message'] ?? '';
            @endphp
            <a href="{{ route('admin.notifications.open', $notification->id) }}" class="dropdown-item text-wrap" style="white-space: normal;">
                <i class="fas fa-info-circle mr-2 text-primary"></i> {{ $title }}
                @if($message)
                    <span class="d-block text-muted small">{{ $message }}</span>
                @endif
            </a>
            <div class="dropdown-divider"></div>
        @empty
            <span class="dropdown-item text-muted">No hay notificaciones nuevas.</span>
            <div class="dropdown-divider"></div>
        @endforelse

        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item dropdown-footer">Ver todas las notificaciones</a>
        <button type="button" class="dropdown-item dropdown-footer text-left border-top helpdesk-enable-desktop-notifs" id="helpdesk-desktop-notifs-btn">
            <i class="fas fa-desktop mr-1 text-muted"></i> Activar avisos de escritorio
        </button>
    </div>

    <style>
        .internal-notification-toast-host {
            position: fixed;
            top: 72px;
            right: 12px;
            z-index: 9999;
            max-width: 360px;
            pointer-events: none;
        }
        .internal-notification-toast-host .alert {
            pointer-events: auto;
        }

        @keyframes helpdesk-bell-ring {
            0% { transform: rotate(0deg); }
            12% { transform: rotate(15deg); }
            28% { transform: rotate(-13deg); }
            44% { transform: rotate(10deg); }
            60% { transform: rotate(-6deg); }
            76% { transform: rotate(3deg); }
            100% { transform: rotate(0deg); }
        }

        .helpdesk-bell-icon {
            display: inline-block;
            transform-origin: 50% 12%;
        }

        .helpdesk-bell-icon.helpdesk-bell-pulse {
            animation: helpdesk-bell-ring 0.75s ease-in-out 1;
        }

        @keyframes helpdesk-bell-badge-pop {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .navbar-badge.helpdesk-bell-badge-pulse {
            animation: helpdesk-bell-badge-pop 0.4s ease-out 2;
            transform-origin: center;
        }

        @media (prefers-reduced-motion: reduce) {
            .helpdesk-bell-icon.helpdesk-bell-pulse,
            .navbar-badge.helpdesk-bell-badge-pulse {
                animation: none;
            }
        }
    </style>
    <script>
        (function () {
            if (window.__internalNotificationsBound) return;
            window.__internalNotificationsBound = true;

            window.__internalNotificationSeen = window.__internalNotificationSeen || {};

            function escapeHtml(s) {
                if (s === null || s === undefined) return '';
                var d = document.createElement('div');
                d.textContent = String(s);
                return d.innerHTML;
            }

            function showPopupToast(item) {
                var host = document.getElementById('internal-notification-toast-host');
                if (!host || !item) return;
                var el = document.createElement('div');
                el.className = 'alert alert-light border-left border-info shadow-sm mb-2 py-2 px-3';
                el.setAttribute('role', 'alert');
                el.innerHTML =
                    '<strong class="d-block text-dark">' + escapeHtml(item.title || 'Notificación') + '</strong>' +
                    (item.message
                        ? '<span class="small text-muted d-block mt-1">' + escapeHtml(item.message) + '</span>'
                        : '');
                host.appendChild(el);
                window.setTimeout(function () {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity .35s ease';
                    window.setTimeout(function () {
                        if (el.parentNode) el.parentNode.removeChild(el);
                    }, 400);
                }, 5000);
            }

            function showDesktopNotification(item) {
                if (!item) return;
                if (!('Notification' in window) || Notification.permission !== 'granted') return;

                var n = new Notification(item.title || 'Notificación', {
                    body: item.message || '',
                    icon: '/favicon.ico',
                    tag: 'helpdesk-' + item.id
                });

                n.onclick = function () {
                    window.focus();
                    if (item.url) window.location.href = item.url;
                };
            }

            function playBellAttentionAnimation() {
                var icon = document.querySelector('.helpdesk-bell-icon');
                if (!icon) return;

                icon.classList.remove('helpdesk-bell-pulse');
                void icon.offsetWidth;
                icon.classList.add('helpdesk-bell-pulse');
                icon.addEventListener('animationend', function onIconEnd() {
                    icon.removeEventListener('animationend', onIconEnd);
                    icon.classList.remove('helpdesk-bell-pulse');
                });

                var link = icon.closest('.helpdesk-bell-trigger');
                var badge = link ? link.querySelector('.navbar-badge') : null;
                if (badge) {
                    badge.classList.remove('helpdesk-bell-badge-pulse');
                    void badge.offsetWidth;
                    badge.classList.add('helpdesk-bell-badge-pulse');
                    badge.addEventListener('animationend', function onBadgeEnd() {
                        badge.removeEventListener('animationend', onBadgeEnd);
                        badge.classList.remove('helpdesk-bell-badge-pulse');
                    });
                }
            }

            document.addEventListener('livewire:init', function () {
                document.body.addEventListener('click', function (e) {
                    var btn = e.target && e.target.closest
                        ? e.target.closest('#helpdesk-desktop-notifs-btn')
                        : null;
                    if (!btn || !('Notification' in window)) return;
                    Notification.requestPermission().then(function (p) {
                        if (p === 'granted') {
                            btn.classList.add('text-success');
                            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Avisos de escritorio activados';
                            btn.disabled = true;
                        }
                    });
                });

                window.addEventListener('internal-notifications-new', function (event) {
                    var list =
                        event.detail && event.detail.notifications
                            ? event.detail.notifications
                            : [];
                    var hadNew = false;
                    list.forEach(function (item) {
                        if (!item || !item.id || window.__internalNotificationSeen[item.id]) return;
                        window.__internalNotificationSeen[item.id] = true;
                        hadNew = true;
                        showPopupToast(item);
                        showDesktopNotification(item);
                    });
                    if (hadNew) {
                        playBellAttentionAnimation();
                    }
                });
            });
        })();
    </script>
</div>

