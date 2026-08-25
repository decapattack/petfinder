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
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('pets.public', $this->pet->uuid);

        return (new MailMessage)
            ->subject('🚨 Alerta PetFinder: pet perdido na sua região')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Um pet foi reportado como desaparecido perto da sua localização.")
            ->line("Pet: {$this->pet->nome}")
            ->line("Espécie: {$this->pet->especie} | Raça: {$this->pet->raca}")
            ->action('Ver página do pet', $url)
            ->line('Se você tiver informações, entre em contato pelo WhatsApp na página do pet.');
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
