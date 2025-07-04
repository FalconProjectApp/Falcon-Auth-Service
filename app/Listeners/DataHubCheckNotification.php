<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\CreatingDatabase;
use FalconERP\Skeleton\Falcon;
use FalconERP\Skeleton\Models\Erp\Setting;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class DataHubCheckNotification // implements ShouldQueueAfterCommit
{
    // use InteractsWithQueue;

    public const SECRET_CHARS = 8;

    /**
     * Handle the event.
     */
    public function handle(CreatingDatabase $event): void
    {
        if (!auth()->check()) {
            return;
        }

        if (Setting::byName('datahub_access')->isEmpty()) {
            $secret = Str::random(self::SECRET_CHARS);

            Falcon::bigDataService('auth')
                ->createUser(
                    data([
                        'password' => $secret,
                    ])->only([
                        'name',
                        'email',
                        'password',
                    ])
                );

            /*
             * TODO: Setting vai ser substituido por loja.
             */
            /* Falcon::shopService('shop', [
                'authorization' => $token->plainTextToken,
            ])->store(new Data([
                'name'                  => data()->name,
                'type'                  => ShopEnum::TYPES_SYSTEM,
                'issuer_people_id'      => people()->id,
                'responsible_people_id' => people()->id,
            ])); */

            Setting::updateOrCreate([
                'name' => 'datahub_access',
            ], [
                'name'  => 'datahub_access',
                'value' => json_encode([
                    'email'    => auth()->user()->email,
                    'password' => $secret,
                ]),
                'description' => 'Acesso ao DataHub',
            ]);
        }
    }
}
