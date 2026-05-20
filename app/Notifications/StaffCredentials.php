<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffCredentials extends Notification
{
    use Queueable;

    public function __construct(
        public string $username,
        public string $password,
        public string $firstName,
        public string $loginUrl = 'https://hrp.stjosephhospitalmoshi.or.tz/auth/login'
    ) {}

    /**
     * Get the notification's delivery channels.
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
            ->subject('Your HRP System Login Credentials')
            ->greeting('Dear '.$this->firstName.',')
            ->line('A user account has been created for you on the HRP System.')
            ->line('**Username:** '.$this->username)
            ->line('**Password:** '.$this->password)
            ->action('Login to HRP System', $this->loginUrl)
            ->line('For your security, please change your password after your first login.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'username' => $this->username,
            'first_name' => $this->firstName,
        ];
    }
}
