<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param string $url
     *
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(__('mail_auth.forgot.subject'))
            ->line(__('mail_auth.forgot.line.1'))
            ->action(__('mail_auth.forgot.action'), $url)
            ->line(__('mail_auth.forgot.line.2', [
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]))
            ->line(__('mail_auth.forgot.line.3'));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(
            Str::replace('{token}', $this->token, config('auth.passwords.front_url')), [
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
    }
}
