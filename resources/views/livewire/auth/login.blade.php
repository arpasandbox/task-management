<div>
    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email</label>
            <input id="email" type="email" wire:model="email" class="tms-input" placeholder="you@example.com" required>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" type="password" wire:model="password" class="tms-input" required>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm tms-muted">
            <input type="checkbox" wire:model="remember" class="rounded border-brand-light text-brand focus:ring-brand">
            Remember me
        </label>

        <button type="submit" class="tms-btn-primary w-full">Log in</button>
    </form>

    <p class="mt-4 text-center text-sm tms-muted">
        Need an account?
        <a href="{{ route('register') }}" class="tms-link" wire:navigate>Register</a>
    </p>
</div>
