<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return  in_array($user->role, [
            Role::ADMIN->value,
        ]);
        
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NotificationSetting $notificationSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NotificationSetting $notificationSetting): bool
    {
        return  in_array($user->role, [
            Role::ADMIN->value,
        ]);
    }

}
