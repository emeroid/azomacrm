<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\FundRequest; 

class FundRequestPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user) {
        return  in_array($user->role, [
            Role::ADMIN->value,
            Role::MANAGER->value,
            Role::MARKETER->value,
            Role::CALL_AGENT->value,
        ]);
    }
    
    
    public function view(User $user, FundRequest $model)
    {
        return  in_array($user->role, [
            Role::ADMIN->value,
            Role::MANAGER->value,
            Role::MARKETER->value,
            Role::CALL_AGENT->value,
        ]);
    }

    public function create(User $user)
    {
        return  in_array($user->role, [
            Role::MARKETER->value,
            Role::CALL_AGENT->value,
        ]);
    }

    public function update(User $user, FundRequest $model)
    {
        return $user->id == $model->user_id;
    }

    public function delete(User $user, FundRequest $model)
    {
        return $user->id == $model->user_id;
    }
}
