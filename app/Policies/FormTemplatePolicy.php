<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FormTemplatePolicy
{
    public function viewAny(User $user) {
        return  in_array($user->role, [
            Role::ADMIN->value,
            Role::MARKETER->value,
        ]);
    }
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FormTemplate $template): bool
    {
        // Users can view their own forms or template forms
        return $user->id === $template->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FormTemplate $template): bool
    {
        return !$template->is_template && $user->id === $template->user_id;
    }

    public function createField(User $user, FormTemplate $template): bool
    {
        return $this->update($user, $template);
    }

    /**
     * Determine whether the user can delete the model.
    */
    public function delete(User $user, FormTemplate $template): bool
    {
        return $this->update($user, $template);
    }

}
