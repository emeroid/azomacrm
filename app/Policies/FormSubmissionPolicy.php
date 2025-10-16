<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
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
    public function view(User $user, FormSubmission $template): bool
    {
        // Users can view their own forms or template forms
        return $user->id === $template->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FormSubmission $template): bool
    {
        return $user->id === $template->user_id;
    }

    /**
     * Determine whether the user can update the model.
    */
    public function delete(User $user, FormSubmission $template): bool
    {
        return $user->id === $template->user_id;
    }

}
