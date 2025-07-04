<?php

namespace App\Listeners;

use App\Services\Erp\People\NotificationService;

class CreateNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        $this->notificationService
            ->setData($event)
            ->storeQuitly();
    }
}
