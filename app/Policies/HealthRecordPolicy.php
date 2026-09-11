<?php

namespace App\Policies;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;

/**
 * Policy: HealthRecordPolicy
 *
 * Controle centralizado de autorização para fichas clínicas e exames.
 * Garante validação estrita de ownership cruzada (Pet -> HealthRecord).
 */
class HealthRecordPolicy
{
    /**
     * Determina se o usuário pode criar uma ficha para o pet.
     */
    public function create(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determina se o usuário pode visualizar o arquivo da ficha clínica.
     * Permite se:
     * 1. O registro pertence ao pet especificado; E
     * 2. (Usuário é o dono do pet) OU (Ficha está marcada como pública).
     */
    public function view(User $user, HealthRecord $record, Pet $pet): bool
    {
        if ($record->pet_id !== $pet->id) {
            return false;
        }

        if ($user->id === $pet->user_id) {
            return true;
        }

        return (bool) $record->is_public;
    }

    /**
     * Determina se o usuário pode atualizar a ficha clínica (ou alterar privacidade).
     */
    public function update(User $user, HealthRecord $record, Pet $pet): bool
    {
        return $record->pet_id === $pet->id && $user->id === $pet->user_id;
    }

    /**
     * Determina se o usuário pode excluir a ficha clínica.
     */
    public function delete(User $user, HealthRecord $record, Pet $pet): bool
    {
        return $record->pet_id === $pet->id && $user->id === $pet->user_id;
    }
}
