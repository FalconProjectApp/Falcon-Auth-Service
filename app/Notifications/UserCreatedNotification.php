<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $base;

    public function __construct($user, $base)
    {
        $this->user = $user;
        $this->base = $base;
    }

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->success()
            ->content('A new user has been created!')
            ->from(
                config('logging.channels.slack_notifications.username'),
                config('logging.channels.slack_notifications.emoji')
            )
            ->attachment(function ($attachment) {
                $attachment->title('User Details')
                    ->fields([
                        'Id'    => $this->user->id,
                        'Email' => $this->user->email,
                        'base'  => $this->base->base,
                    ]);
            });
    }
}
