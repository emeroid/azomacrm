<?php

namespace App\Services;

use App\Models\User;

class UpdateUsername
{
    /**
     * Log an order status change
     *
     * @param Order $order
     * @param string $newStatus
     * @param string|null $notes
     * @return OrderCommunication
     */
    public static function exec(User $user)
    {
        if (empty($user->username)) {
            $baseUsername = strtolower(substr($user->first_name, 0, 2) . substr($user->last_name, 0, 2));
            $username = $baseUsername;
            $counter = 1;

            // Ensure uniqueness across all users
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user->username = strtoupper($username);
        }
    }
}