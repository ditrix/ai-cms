<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $manager): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $manager): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $manager): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can change password for the manager.
     */
    public function changePassword(User $user, User $manager): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can change manager for clients.
     */
    public function changeManager(User $user): bool
    {
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }
}
