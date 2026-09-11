<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\PetMedia;
use App\Models\User;

/**
 * Policy: PetPolicy
 *
 * Controle centralizado de acesso a recursos de pets.
 * Substitui verificações manuais espalhadas nos controladores.
 */
class PetPolicy
{
    /**
     * Determine se o usuário pode ver a listagem ou um pet específico.
     * (Usado em index/edit — a página pública não requer autenticação.)
     */
    public function view(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determine se o usuário pode criar um novo pet.
     * Qualquer usuário autenticado e verificado pode criar.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine se o usuário pode atualizar os dados do pet.
     * Usado em update(), health(), updateVet(), e como base para records/schedules.
     */
    public function update(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determine se o usuário pode gerenciar as mídias do pet (adicionar/remover fotos e vídeos).
     */
    public function manageMedia(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determine se o usuário pode excluir o pet.
     */
    public function delete(User $user, Pet $pet): bool
    {
        return $user->id === $pet->user_id;
    }

    /**
     * Determine se o usuário pode remover uma mídia específica.
     * Verifica ownership do pet e que a mídia pertence ao pet correto.
     */
    public function destroyMedia(User $user, Pet $pet, PetMedia $media): bool
    {
        return $user->id === $pet->user_id && $media->pet_id === $pet->id;
    }
}
