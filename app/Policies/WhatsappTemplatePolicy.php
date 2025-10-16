<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Auth\Access\Response;

class WhatsappTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return  in_array($user->role, [
            Role::ADMIN->value
        ]);
    }
    
}
