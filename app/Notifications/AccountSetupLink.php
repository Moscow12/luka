<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** New-account e-mail with a one-time link to choose a password (no password is ever sent). */
class AccountSetupLink extends Notification
{
    use Queueable;

    public function __construct(
        public string $url,
        public string $username,
        public string $firstName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hours = (int) config('auth.account_setup_expire_hours', 72);

        return (new MailMessage)
            ->subject('Set up your HRP System account')
            ->greeting('Dear '.$this->firstName.',')
            ->line('A user account has been created for you on the HRP System.')
            ->line('**Username:** '.$this->username)
            ->action('Choose your password', $this->url)
            ->line("This link can be used once and expires in {$hours} hours.")
            ->line('If it has expired, use "Forgot password" on the login page or ask HR to send a new link.');
    }

    /** Never includes the link: notification arrays can end up in databases and logs. */
    public function toArray(object $notifiable): array
    {
        return [
            'username' => $this->username,
            'first_name' => $this->firstName,
        ];
    }
}
