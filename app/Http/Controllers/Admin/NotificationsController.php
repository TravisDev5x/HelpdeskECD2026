<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Notifications\InternalNotificationTypeRegistry;
use App\Support\Notifications\SafeInternalNotificationUrl;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read panel notifications']);
    }

    public function index()
    {
        abort_unless(Auth::check(), 403);

        $type = InternalNotificationTypeRegistry::normalizedFilter(
            request()->query('type')
        );

        $query = Auth::user()
            ->visibleNotifications();

        InternalNotificationTypeRegistry::applyTypeFilter($query, $type);

        $notifications = $query
            ->latest()
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications', 'type'));
    }

    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->visibleUnreadNotifications()->update(['read_at' => now()]);

        return back()->with('flash', 'Notificaciones marcadas como leídas.');
    }

    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $raw = $notification->data ?? [];
        $data = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
        $target = SafeInternalNotificationUrl::redirectTarget(
            isset($data['url']) ? (string) $data['url'] : null,
            'admin.notifications.index'
        );

        return redirect()->to($target);
    }
}

