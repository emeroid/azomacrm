<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model)
    {
        return $user->role === 'admin';
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $model)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, User $model)
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, User $model)
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, User $model)
    {
        return $user->role === 'admin';
    }
}
