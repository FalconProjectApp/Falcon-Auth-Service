<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class ExceptionNotification extends Notification
{
    use Queueable;

    protected $exception;

    /**
     * Create a new notification instance.
     */
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->error()
            ->content('An exception occurred: '.$this->exception->getMessage())
            ->attachment(function ($attachment) {
                $attachment->title('Exception Details')
                    ->fields([
                        'Exception' => get_class($this->exception),
                        'Message'   => $this->exception->getMessage(),
                        'File'      => $this->exception->getFile(),
                        'Line'      => $this->exception->getLine(),
                    ]);
            });
    }
}
