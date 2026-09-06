<div>
    <h2 class="mb-1 text-lg font-medium">Create an account</h2>
    <p class="mb-6 text-sm tms-muted">Register to get started with your workspace.</p>

    <form wire:submit="register" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1 block text-sm font-medium">First name</label>
                <input id="first_name" type="text" wire:model="first_name" class="tms-input" placeholder="Jane" required>
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="mb-1 block text-sm font-medium">Last name</label>
                <input id="last_name" type="text" wire:model="last_name" class="tms-input" placeholder="Doe" required>
                @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

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

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm password</label>
            <input id="password_confirmation" type="password" wire:model="password_confirmation" class="tms-input" required>
        </div>

        <button type="submit" class="tms-btn-primary w-full">Register</button>
    </form>

    <p class="mt-4 text-center text-sm tms-muted">
        Already have an account?
        <a href="{{ route('login') }}" class="tms-link" wire:navigate>Log in</a>
    </p>
</div>
