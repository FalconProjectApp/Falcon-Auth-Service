<?php

namespace App\Listeners;

use FalconERP\Skeleton\Models\User;
use App\Repositories\EmailRepository;
use GustavoSantarosa\HandlerBasicsExtension\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class SendEmail
{
    use ApiResponseTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public User $userSession)
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
        if (!$event->userSession->accepted_at) {
            (new EmailRepository())
                ->From('bolota_xd@hotmail.com', 'Equipe Falcon')
                ->dest([
                    Auth::user()->email,
                ])
                ->subject('Um novo dispositivo tentou acessar sua conta, é você?')
                ->htmlBody("
                        Dispositivo: <b>{$event->userSession->agent}</b> <br>
                        Ip: <b>{$event->userSession->ip}</b>
                        <br>
                        <br>
                        Este novo dispositivo tentou acessar a sua conta com o usuario e senha corretos, caso seja voce,
                        permita o acesso, clicando <a href='".config('app.url')."/login/v1/device-permission-granted?code={$event->userSession->uuid}'>aqui</a>, caso não seja, sujerimos trocar sua senha aqui.
                    ")
                ->send();

            $this->unauthorizedResponse(
                'Este dispositivo ainda é desconhecido, foi enviado um email para notificar o responsavel e poder ser inserido aos despositivos validos!'
            );
        }
    }
}
