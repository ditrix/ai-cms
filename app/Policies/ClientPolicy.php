<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Менеджер видит только своих клиентов (проверка в контроллере)
        // Супер-менеджер и админ видят всех клиентов
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        // Менеджер только своих клиентов
        if ($user->isManager()) {
            return $client->manager_id === $user->id;
        }

        // Супер-менеджер и админ - всех
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Менеджер может создавать клиентов (они автоматически привязываются к нему)
        // Супер-менеджер и админ могут создавать клиентов
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // Менеджер только своих клиентов
        if ($user->isManager()) {
            return $client->manager_id === $user->id;
        }

        // Супер-менеджер и админ - всех
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // Менеджер только своих клиентов
        if ($user->isManager()) {
            return $client->manager_id === $user->id;
        }

        // Супер-менеджер и админ - всех
        return in_array($user->role, [UserRole::SUPER_MANAGER, UserRole::ADMIN], true);
    }
}
