<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard\Board;
use App\Livewire\Dashboard\ListView;
use App\Livewire\Workspaces\Create;
use App\Livewire\Workspaces\Edit;
use App\Livewire\Workspaces\ManageStatuses;
use App\Support\CurrentWorkspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::livewire('register', Register::class)->name('register');
    Route::livewire('login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::livewire('workspaces/create', Create::class)->name('workspaces.create');
});

Route::middleware(['auth', 'workspace.required'])->group(function () {
    Route::livewire('workspaces/settings', Edit::class)->name('workspaces.settings');
    Route::livewire('workspaces/settings/statuses', ManageStatuses::class)->name('workspaces.statuses');
    Route::livewire('dashboard', Board::class)->name('dashboard');
    Route::livewire('tasks', ListView::class)->name('tasks.list');
});
