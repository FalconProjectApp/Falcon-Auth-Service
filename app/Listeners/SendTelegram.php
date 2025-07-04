<?php

namespace App\Listeners;

use GustavoSantarosa\HandlerBasicsExtension\Traits\ApiResponseTrait;
use Telegram\Bot\Api;

class SendTelegram
{
    use ApiResponseTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public \stdClass $stdclass)
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
        $telegram = new Api();

        $telegram->sendMessage($event->stdclass->params);
    }
}
