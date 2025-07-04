<?php

declare(strict_types = 1);

namespace App\Services\Erp\Public\V2;

use App\Events\CreatingDatabase;
use App\Notifications\UserCreatedNotification;
use FalconERP\Skeleton\Enums\People\PeopleDocumentEnum;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DataBase\PgDataBase;
use FalconERP\Skeleton\Models\BackOffice\GiftCode;
use FalconERP\Skeleton\Models\PersonalAccessToken;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use QuantumTecnology\ServiceBasicsExtension\BaseService;

/**
 * @attribute PgDatabase $pgDatabaseModel
 */
class AuthService extends BaseService
{
    public const EXPIRE_MINUTES    = 5;
    public const CHARACTERS_NUMBER = 6;
    public const SECRET_CHARS      = 8;

    protected $model                    = User::class;
    protected $personalAccessTokenModel = PersonalAccessToken::class;
    protected $pgDatabaseModel          = PgDataBase::class;

    protected $initializedAutoDataTrait = [
        'store',
    ];

    public function store(): Model
    {
        data([
            'display_name' => data()->name,
        ]);

        // Executa as migrations do pacote skeleton
        $this->checkDocument();
        $this->checkGiftCode();
        $this->checkBase();

        /*  Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL_NOTIFICATIONS'))
             ->notify(new UserCreatedNotification(auth()->user(), database())); */
        dd(database());
        return database();
    }

    private function checkDocument(): void
    {
        if (!data()->has('document')) {
            return;
        }

        $document = preg_replace('/[^0-9]/', '', data()->document);

        $result = match (mb_strlen($document)) {
            9       => PeopleDocumentEnum::TYPE_PASSPORT,
            11      => PeopleDocumentEnum::TYPE_CPF,
            14      => PeopleDocumentEnum::TYPE_CNPJ,
            default => false,
        };

        abort_unless(
            $result,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            __('Documento inválido!')
        );

        data([
            'document_type' => $result,
        ]);
    }

    private function checkGiftCode(): void
    {
        if (!data()->has('gift_code')) {
            return;
        }

        $giftCode = GiftCode::query()
            ->where('code', data()->gift_code)
            ->first();

        abort_if(
            !$giftCode,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            __('Gift code not found')
        );

        data([
            'gift_code_id' => $giftCode->id,
        ]);
    }

    private function checkBase(): void
    {
        $name = Str::lower(removeSpecialChar(data()->name));
        $name = Str::lower(sprintf('%s_%s', $name, str()->random(self::CHARACTERS_NUMBER)));
        $name = sprintf('bc_%s', $name);

        abort_if(
            PgDataBase::byDatname($name)->exists(),
            Response::HTTP_OK,
            __("Base ':base' already exists", ['base' => $name])
        );

        $database = Database::create([
            'base' => $name,
        ]);

        PgDataBase::createDatabase($name);

        data([
            'base'        => $name,
            'database_id' => $database->id,
        ]);

        event(new CreatingDatabase($database));
    }
}
