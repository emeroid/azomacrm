<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use App\Models\OrderCommunication;

class OrderCommunicationPolicy
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

    public function view(User $user, OrderCommunication $communication)
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Marketers can only see communications for orders they created
        return $user->role === 'marketer' && $communication->order->marketer_id === $user->id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'marketer']);
    }

    public function update(User $user, OrderCommunication $communication)
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Marketers can only update their own communications
        return $user->role === 'marketer' && 
               $communication->agent_id === $user->id;
    }

    public function delete(User $user, OrderCommunication $communication)
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, OrderCommunication $communication)
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, OrderCommunication $communication)
    {
        return $user->role === 'admin';
    }
}
