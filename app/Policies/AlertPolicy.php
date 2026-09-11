<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;

/**
 * Policy: AlertPolicy
 *
 * Controle centralizado de autorização para alertas de desaparecimento.
 */
class AlertPolicy
{
    /**
     * Determina se o usuário pode emitir um alerta para o pet.
     */
    public function create(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determina se o usuário pode encerrar/resolver o alerta.
     * Apenas o dono do pet vinculado ao alerta pode resolvê-lo.
     */
    public function resolve(User $user, Alert $alert): bool
    {
        $alert->loadMissing('pet');

        return $alert->pet && $user->id === $alert->pet->user_id;
    }
}
