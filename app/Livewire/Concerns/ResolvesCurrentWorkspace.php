<?php

namespace App\Livewire\Concerns;

use App\Models\Workspace;
use App\Support\CurrentWorkspace;

trait ResolvesCurrentWorkspace
{
    protected function currentWorkspace(): ?Workspace
    {
        return CurrentWorkspace::resolve();
    }
}
