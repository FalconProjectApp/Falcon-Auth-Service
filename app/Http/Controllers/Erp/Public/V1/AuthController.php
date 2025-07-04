<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Erp\Public\V1;

use App\Http\Resources\Erp\Public\Auth\LoginResource;
use App\Services\Erp\Public\V1\AuthService;
use Illuminate\Http\JsonResponse;
use QuantumTecnology\ControllerBasicsExtension\Controllers\BaseController;

/**
 * AuthController class.
 *
 * @property AuthService defaultService
 */
class AuthController extends BaseController
{
    protected string $service  = AuthService::class;
    protected string $resource = LoginResource::class;

    protected array $allowedIncludes = [];

    public function store(): JsonResponse
    {
        $stored = $this->getService()->store();

        return $this->okResponse(
            message: sprintf('Conta criada com sucesso. Seja bem-vindo, %s!', auth()->people()->name),
        );
    }

    public function sendToken()
    {
        $token = $this->getService()->sendToken();

        return $this->okResponse(
            message: __('auth.success.token.sent'),
            data: in_array(config('app.env'), ['local', 'staging']) ? ['token' => $token] : null,
        );
    }

    public function checkToken()
    {
        $resource = $this->getResource();

        return $this->okResponse(
            message: 'User logged in successfully.',
            data: new $resource($this->getService()->checkToken()),
        );
    }
}
