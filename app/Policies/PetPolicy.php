<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

/**
 * Policy: PetPolicy
 * 
 * Controle de acesso: apenas dono do pet pode modificar dados de saúde.
 */
class PetPolicy
{
    /**
     * Determine whether the user can update the model.
     * Usado para todas as ações de saúde (health, vet, records, schedules)
     */
    public function update(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }
}
