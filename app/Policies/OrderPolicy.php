<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can see the list (scope handled in Resource)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'server']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // Servers can only update their own unpaid orders
        return $user->role === 'server' 
            && $order->user_id === $user->id 
            && $order->status !== 'paid';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only Admins can delete orders (audit trail)
        return $user->role === 'admin';
    }
}