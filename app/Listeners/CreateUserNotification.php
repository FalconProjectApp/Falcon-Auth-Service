<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\CreatingDatabase;
use FalconERP\Skeleton\Enums\People\PeopleContactEnum;
use FalconERP\Skeleton\Enums\People\PeopleTypeEnum;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\Type;
use FalconERP\Skeleton\Models\User;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class CreateUserNotification // implements ShouldQueueAfterCommit
{
    // use InteractsWithQueue;

    public const CHARACTERS_NUMBER = 6;

    /**
     * Handle the event.
     */
    public function handle(CreatingDatabase $event): void
    {
        auth()->setDatabase($event->model);

        data([
            'types_id' => Type::query()
                ->where('description', PeopleTypeEnum::TYPE_ADMIN)
                ->first()
                ?->id,
        ]);

        $people = People::create(
            data()->only([
                'name',
                'display_name',
                'types_id',
            ], toArray: true)
        );

        $people->peopleDocuments()
            ->create([
                'type'          => data()->document_type,
                'value'         => data()->document,
                'is_accessible' => true,
            ]);

        $people->peopleContacts()
            ->create([
                'type'  => PeopleContactEnum::TYPE_EMAIL,
                'main'  => true,
                'value' => data()->email,
            ]);

        $databaseCreated = Database::create(data()->only(['base'])->toArray());
        $userCreated     = User::create(data()->only(['email', 'gift_code_id'])->toArray());

        if (data()->has('gift_code_id')) {
            $giftCodeUsed = $userCreated->giftCodeUsed;
            ++$giftCodeUsed->uses;
            $giftCodeUsed->used_at = now();
            $giftCodeUsed->save();
        }

        $userCreated->giftCodes()
            ->create([
                'code'         => Str::upper(Str::random(self::CHARACTERS_NUMBER)),
                'owner_bonus'  => 1,
                'client_bonus' => 5,
            ]);

        $userCreated->databasesAccess()->attach(
            $databaseCreated->id, [
                'is_active'      => true,
                'base_people_id' => $people->id,
            ]);

        auth()->setUser($userCreated);
    }
}
