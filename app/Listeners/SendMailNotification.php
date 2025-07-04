<?php

namespace App\Listeners;

use App\Events\SendEmail;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendMailNotification implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SendEmail $event): void
    {
        if (!$event->qaTest && !is_null($event->email)) {
            Mail::to($event->email)
                ->send($event->mailable);
        }

        if ($event->qaTest) {
            Mail::to(config('mail.development.address'))
                ->send($event->mailable);
        }
    }
}
