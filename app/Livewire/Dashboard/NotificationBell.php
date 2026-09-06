<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public bool $compact = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $notificationId): void
    {
        Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.dashboard.notification-bell', [
            'notifications' => $user?->notifications()->latest()->limit(10)->get() ?? collect(),
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
        ]);
    }
}
