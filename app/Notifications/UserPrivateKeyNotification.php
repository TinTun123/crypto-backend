<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserPrivateKeyNotification extends Notification
{
    use Queueable;

    public $privateKey;
    /**
     * Create a new notification instance.
     */
    public function __construct($key)
    {
        //
        $this->privateKey = $key;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Here is your private key, please do not share or send to anyone for security reasons.')
                    ->line($this->privateKey)
                    ->line('Please upload the key to your wallet contract page.')
                    ->line('Thank you for using our Wallet!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
