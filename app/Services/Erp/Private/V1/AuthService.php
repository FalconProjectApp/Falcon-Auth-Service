<?php

declare(strict_types = 1);

namespace App\Services\Erp\Private\V1;

use FalconERP\Skeleton\Falcon;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\User;
use QuantumTecnology\ServiceBasicsExtension\BaseService;
use QuantumTecnology\ValidateTrait\Data;

class AuthService extends BaseService
{
    protected $databaseUserAccessModel = DatabasesUsersAccess::class;

    protected $model = User::class;

    protected $initializedAutoDataTrait = [
        'activeDatabase',
    ];

    public function activeDatabase(): bool
    {
        $this->databaseUserAccessModel::query()
            ->where('user_id', auth()->id())
            ->update([
                'is_active' => false,
            ]);

        auth()->user()
            ->databasesAccess()
            ->updateExistingPivot(
                request()->data()->database_id, [
                    'is_active' => true,
                ]);

        return true;
    }

    public function datahubLogin(): Data
    {
        $result = Falcon::bigDataService('auth')->login();

        if (!$result->success) {
            $this->unprocessableEntityResponse(
                $result->message,
                $result->errors,
            );
        }

        return new Data([
            'message' => $result->message,
            'data'    => $result->data,
        ]);
    }
}
