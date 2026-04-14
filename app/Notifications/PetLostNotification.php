<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PetLostNotification extends Notification
{
    use Queueable;

    protected $pet;

    public function __construct($pet)
    {
        $this->pet = $pet;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'pet_id' => $this->pet->id,
            'pet_nome' => $this->pet->nome,
            'pet_uuid' => $this->pet->uuid,
            'mensagem' => "⚠️ Alerta! O pet {$this->pet->nome} desapareceu perto de você!",
        ];
    }
}
