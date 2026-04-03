<?php

namespace App\Livewire\Admin\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bell extends Component
{
    public array $knownUnreadIds = [];

    public function mount(): void
    {
        if (! Auth::check()) {
            return;
        }

        abort_unless(Auth::user()->can('read panel notifications'), 403);

        $this->knownUnreadIds = Auth::user()
            ->visibleUnreadNotifications()
            ->latest()
            ->limit(20)
            ->pluck('id')
            ->all();
    }

    public function refreshNotifications(): void
    {
        if (! Auth::check()) {
            return;
        }

        abort_unless(Auth::user()->can('read panel notifications'), 403);

        $unread = Auth::user()
            ->visibleUnreadNotifications()
            ->latest()
            ->limit(20)
            ->get(['id', 'data']);

        $currentIds = $unread->pluck('id')->all();
        $newIds = array_values(array_diff($currentIds, $this->knownUnreadIds));

        if (!empty($newIds)) {
            $newNotifications = $unread
                ->filter(fn ($n) => in_array($n->id, $newIds, true))
                ->take(5)
                ->map(function ($n) {
                    $raw = $n->data ?? [];
                    $data = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

                    return [
                        'id' => $n->id,
                        'title' => $data['title'] ?? 'Notificación',
                        'message' => $data['message'] ?? '',
                        'url' => route('admin.notifications.open', $n->id),
                    ];
                })
                ->values()
                ->all();

            $this->dispatch('internal-notifications-new', notifications: $newNotifications);
        }

        $this->knownUnreadIds = $currentIds;
    }

    public function render()
    {
        $unreadCount = 0;
        $unreadNotifications = collect();
        if (Auth::check()) {
            $unreadCount = Auth::user()->visibleUnreadNotifications()->count();
            $unreadNotifications = Auth::user()->visibleUnreadNotifications()->latest()->take(8)->get();
        }

        return view('livewire.admin.notifications.bell', [
            'unreadCount' => $unreadCount,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}

