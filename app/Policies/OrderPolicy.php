<?php

namespace App\Policies;

// app/Policies/OrderPolicy.php
namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return Gate::allows('view-any-order');
    }

    public function view(User $user, Order $order)
    {
        if ($user->role === Role::ADMIN->value) {
            return true;
        }
        
        if ($user->role === Role::MARKETER->value) {
            return $order->marketer_id === $user->id;
        }
        
        if ($user->role === Role::DELIVERY_AGENT->value) {
            return $order->delivery_agent_id === $user->id;
        }

        if ($user->role === Role::CALL_AGENT->value) {
            return $order->call_agent_id === $user->id;
        }
        
        return false;
    }

    public function edit(User $user, Order $order) {

        if ($user->role === Role::MARKETER->value) {
            return $order->marketer_id === $user->id && $order->status !== Order::STATUS_RETURNED && $order->status !== Order::STATUS_DELIVERED;
        }
        return false;
    }

    public function create(User $user)
    {
        return Gate::allows('create-order');
    }

    public function update(User $user, Order $order)
    {
        if ($user->role === Role::MARKETER->value) {
            return $order->marketer_id === $user->id;
        }
        return false;
    }

    public function delete(User $user, Order $order)
    {
        if ($user->role === Role::ADMIN->value) {
            return true;
        }
        
        if ($user->role === Role::MARKETER->value) {
            return $order->marketer_id === $user->id && 
                   $order->status === Order::STATUS_PROCESSING;
        }
        
        return false;

    }

    public function updateStatus(User $user, Order $order)
    {
        return Gate::allows('update-order-status');
    }
}
