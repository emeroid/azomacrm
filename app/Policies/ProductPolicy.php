<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class ProductPolicy
{
    use HandlesAuthorization;


    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return Gate::allows('view-product');
    }

    public function view(User $user)
    {
        return Gate::allows('view-product');
    }

    public function create(User $user)
    {
        return Gate::allows('manage-product');
    }

    public function update(User $user)
    {
        return Gate::allows('manage-product');
    }

    public function delete(User $user)
    {
        return Gate::allows('manage-product');
    }
}
