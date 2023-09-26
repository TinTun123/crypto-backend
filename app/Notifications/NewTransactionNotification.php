<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NewTransactionNotification extends Notification
{

    private $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

        /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
