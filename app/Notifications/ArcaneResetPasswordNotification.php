<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ArcaneResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your Arcane password')
            ->greeting('Password reset requested')
            ->line('We received a request to reset the password for your Arcane account.')
            ->action('Reset password', $url)
            ->line('If you did not request a password reset, no further action is required.')
            ->line('For security, this link will expire soon.');
    }
}
