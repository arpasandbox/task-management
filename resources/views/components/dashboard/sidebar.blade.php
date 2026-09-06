@props(['active' => 'board'])

<aside
    x-data="{
        tucked: false,
        init() {
            this.tucked = localStorage.getItem('sidebar-tucked') === 'true';
        },
        toggle() {
            this.tucked = ! this.tucked;
            localStorage.setItem('sidebar-tucked', this.tucked);
        },
    }"
    :class="tucked ? 'md:w-16 md:px-2' : 'md:w-60 md:px-6'"
    class="relative hidden shrink-0 flex-col border-r border-brand-light/60 bg-white py-8 shadow-[4px_0_12px_-4px_rgba(251,146,60,0.12)] transition-[width,padding] duration-200 ease-in-out md:flex"
>
    <div class="group absolute top-1/2 right-0 z-10 -translate-y-1/2 translate-x-1/2">
        <button
            type="button"
            @click="toggle()"
            class="tms-circle size-7 border border-brand-light bg-surface text-ink/70 shadow-sm transition-colors hover:border-brand hover:text-ink"
            :aria-label="tucked ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="tucked ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>

        <span
            role="tooltip"
            class="pointer-events-none absolute left-[calc(100%+0.5rem)] top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink px-2 py-1 text-xs font-medium text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100 group-focus-within:opacity-100"
            x-text="tucked ? 'Expand sidebar' : 'Collapse sidebar'"
        ></span>
    </div>

    <div class="flex justify-center overflow-hidden">
        <img
            x-show="! tucked"
            x-cloak
            src="{{ asset('images/arpa-logo.png') }}"
            alt="ARPA"
            class="h-8 w-auto transition-all duration-200"
        >
        <img
            x-show="tucked"
            x-cloak
            src="{{ asset('images/arpa-a-logo.png') }}"
            alt="ARPA"
            class="h-8 w-auto transition-all duration-200"
        >
    </div>

    <div class="my-8 flex justify-center">
        <div x-show="! tucked" x-cloak>
            <x-dashboard.user-indicator size="lg" />
        </div>

        <div x-show="tucked" x-cloak>
            <x-dashboard.user-indicator size="sm" />
        </div>
    </div>

    <div x-show="! tucked" x-cloak class="mb-4">
        @livewire('workspaces.switcher')
    </div>

    <hr
        class="mb-4 border-brand-light"
        :class="tucked ? 'mx-1' : ''"
    >

    <nav class="flex flex-col gap-1">
        <x-dashboard.nav-item
            label="Board"
            :href="route('dashboard')"
            :active="$active === 'board'"
        >
            <x-slot:icon>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10" />
                </svg>
            </x-slot:icon>
        </x-dashboard.nav-item>

        <x-dashboard.nav-item
            label="List"
            :href="route('tasks.list')"
            :active="$active === 'list'"
        >
            <x-slot:icon>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
            </x-slot:icon>
        </x-dashboard.nav-item>

        <x-dashboard.nav-item
            label="Settings"
            :href="route('workspaces.settings')"
            :active="$active === 'settings'"
        >
            <x-slot:icon>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </x-slot:icon>
        </x-dashboard.nav-item>
    </nav>

    <div class="mt-auto pt-8">
        <div class="group relative">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-center rounded-md border border-red-500 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:border-red-600 hover:bg-red-50 hover:text-red-700"
                    :class="tucked ? 'px-2' : ''"
                >
                    <svg
                        x-show="tucked"
                        x-cloak
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>

                    <span x-show="! tucked" x-cloak>Log out</span>
                </button>
            </form>

            <span
                x-show="tucked"
                role="tooltip"
                class="pointer-events-none absolute left-[calc(100%+0.5rem)] top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink px-2 py-1 text-xs font-medium text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100 group-focus-within:opacity-100"
                x-cloak
            >
                Log out
            </span>
        </div>
    </div>
</aside>
