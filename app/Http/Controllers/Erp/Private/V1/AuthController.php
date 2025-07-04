<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Erp\Private\V1;

use App\Http\Resources\BackOffice\Private\Database\DatabaseDefaultResource;
use App\Services\Erp\Private\V1\AuthService;
use Illuminate\Http\JsonResponse;
use QuantumTecnology\ControllerBasicsExtension\Controllers\BaseController;

/**
 * AuthController class.
 *
 * @method AuthService getService
 */
class AuthController extends BaseController
{
    protected string $service  = AuthService::class;
    protected string $resource = DatabaseDefaultResource::class;

    protected array $allowedIncludes = [
        'databasesAccess',
        'people',
        'people.peopleDocuments',
        'people.peopleContacts',
        'people.peopleImages',
    ];

    public function activeDatabase(): JsonResponse
    {
        $this->getService()->activeDatabase();

        return $this->okResponse(
            message: __('messages.success.updated'),
        );
    }

    public function datahubLogin(): JsonResponse
    {
        $result = $this->getService()->datahubLogin();

        return $this->okResponse(
            message: $result->message,
            data: $result->data,
        );
    }
}
