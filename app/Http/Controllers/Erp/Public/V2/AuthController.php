<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Erp\Public\V2;

use App\Http\Resources\Erp\Public\Auth\LoginResource;
use App\Services\Erp\Public\V2\AuthService;
use Illuminate\Http\JsonResponse;
use QuantumTecnology\ControllerBasicsExtension\Controllers\BaseController;

/**
 * @method AuthService getService()
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
}
