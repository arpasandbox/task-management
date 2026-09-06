@php
    $compact = $compact ?? false;
@endphp

<div class="relative" x-data="{ open: @entangle('open') }">
    <button
        type="button"
        wire:click="toggle"
        class="relative flex items-center justify-center rounded-md border border-brand-light bg-white p-2 text-ink/70 transition-colors hover:border-brand hover:text-ink"
        aria-label="Notifications"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if ($unreadCount > 0)
            <span class="tms-circle-count-sm absolute -right-1 -top-1 bg-brand text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if (! $compact)
        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-brand-light bg-surface shadow-lg"
        >
            <div class="flex items-center justify-between border-b border-brand-light/60 px-4 py-3">
                <p class="text-sm font-semibold text-ink">Notifications</p>
                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead" class="text-xs text-brand hover:underline">
                        Mark all read
                    </button>
                @endif
            </div>

            <div class="max-h-72 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <button
                        type="button"
                        wire:click="markAsRead('{{ $notification->id }}')"
                        wire:key="notification-{{ $notification->id }}"
                        @class([
                            'block w-full border-b border-brand-light/40 px-4 py-3 text-left transition-colors hover:bg-brand-light/20',
                            'bg-brand-light/30' => is_null($notification->read_at),
                        ])
                    >
                        <p class="text-sm text-ink">{{ $notification->data['message'] ?? 'Update' }}</p>
                        <p class="mt-1 text-xs text-ink/60">{{ $notification->created_at->diffForHumans() }}</p>
                    </button>
                @empty
                    <p class="px-4 py-6 text-sm text-ink/60">No notifications yet.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
