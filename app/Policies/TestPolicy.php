<?php

namespace App\Policies;

use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Str;

class TestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('admin.' . Str::snake(Str::pluralStudly('Test')) . '.view');
    }

    public function view(User $user, Test $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('admin.' . Str::snake(Str::pluralStudly('Test')) . '.add');
    }

    public function update(User $user, Test $model): bool
    {
        return $user->can('admin.' . Str::snake(Str::pluralStudly('Test')) . '.edit');
    }

    public function delete(User $user, Test $model): bool
    {
        return $user->can('admin.' . Str::snake(Str::pluralStudly('Test')) . '.delete');
    }
}