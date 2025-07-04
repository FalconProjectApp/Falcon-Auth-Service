<?php

declare(strict_types = 1);

namespace App\Services\Erp\Public\V1;

use App\Mail\Auth\SendTokenEmail;
use App\Notifications\UserCreatedNotification;
use Carbon\Carbon;
use FalconERP\Skeleton\Enums\People\PeopleContactEnum;
use FalconERP\Skeleton\Enums\People\PeopleDocumentEnum;
use FalconERP\Skeleton\Enums\People\PeopleTypeEnum;
use FalconERP\Skeleton\Enums\Shop\ShopEnum;
use FalconERP\Skeleton\Falcon;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DataBase\PgDataBase;
use FalconERP\Skeleton\Models\BackOffice\GiftCode;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\Type;
use FalconERP\Skeleton\Models\Erp\Setting;
use FalconERP\Skeleton\Models\PersonalAccessToken;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use QuantumTecnology\ServiceBasicsExtension\BaseService;
use QuantumTecnology\ValidateTrait\Data;

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
        People::disableAuditing();
        GiftCode::disableAuditing();

        try {
            data([
                'base_raw' => Str::lower(removeSpecialChar(data()->name)),
            ]);
            data([
                'base'          => Str::lower(sprintf('%s_%s', data()->base_raw, str()->random(self::CHARACTERS_NUMBER))),
                'password'      => Str::random(self::SECRET_CHARS),
                'document_type' => match (mb_strlen(preg_replace('/[^0-9]/', '', data()->document))) {
                    9       => PeopleDocumentEnum::TYPE_PASSPORT,
                    11      => PeopleDocumentEnum::TYPE_CPF,
                    14      => PeopleDocumentEnum::TYPE_CNPJ,
                    default => $this->unauthorizedResponse('Documento invalido!'),
                },
                'display_name' => data()->name,
            ]);

            if (data()->has('gift_code')) {
                data([
                    'gift_code_id' => GiftCode::query()
                        ->where('code', data()->gift_code)
                        ->first()
                        ?->id,
                ]);
            }

            abort_if(
                $this->pgDatabaseModel::byDatname(data()->base)->exists(),
                Response::HTTP_OK,
                __("Base ':base' already exists", ['base' => data()->base_raw])
            );

            $this->pgDatabaseModel::createDatabase(sprintf('bc_%s', data()->base));

            auth()->setDatabase(new Database([
                'base' => data()->base,
            ]));

            data([
                'types_id' => Type::query()
                    ->where('description', PeopleTypeEnum::TYPE_ADMIN)
                    ->first()
                    ?->id,
            ]);

            Falcon::bigDataService('auth')
                ->createUser(
                    data()->only([
                        'name',
                        'email',
                        'password',
                    ])
                );

            $stored = DB::transaction(function () {
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

                Setting::updateOrCreate([
                    'name' => 'datahub_access',
                ], [
                    'name'        => 'datahub_access',
                    'value'       => data()->only(['email', 'password'], toJson: true),
                    'description' => 'Acesso ao DataHub',
                ]);

                return $databaseCreated->refresh();
            });

            $token = auth()->user()->createToken(
                name: 'authentication',
                expiresAt: Carbon::now()->addMinutes(config('auth.passwords.expire'))
            );

            Falcon::shopService('shop', [
                'authorization' => $token->plainTextToken,
            ])->store(new Data([
                'name'                  => data()->name,
                'type'                  => ShopEnum::TYPES_SYSTEM,
                'issuer_people_id'      => people()->id,
                'responsible_people_id' => people()->id,
            ]));

            Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL_NOTIFICATIONS'))
                ->notify(new UserCreatedNotification(auth()->user(), $stored));

            return $stored;
        } finally {
            People::enableAuditing();
            GiftCode::enableAuditing();
        }
    }

    public function sendToken(): string | false
    {
        $data = $this->validate();

        $people = $this->defaultModel::query()
            ->where('email', $data->credential)
            ->firstOrFail();

        $tokens = $people
            ->tokens()
            ->where('name', 'password_less')
            ->where('expires_at', '<', Carbon::now())
            ->whereNull('last_used_at');

        if ($tokens->exists()) {
            $tokens->delete();
        }

        $token = $people
            ->tokens()
            ->create([
                'name'       => 'password_less',
                'token'      => Str::upper(Str::random(self::CHARACTERS_NUMBER)),
                'expires_at' => Carbon::now()
                    ->addMinutes(self::EXPIRE_MINUTES),
            ])->token;

        if (!in_array(config('app.env'), ['local', 'staging'])) {
            Mail::to($people->email)
                ->send(new SendTokenEmail($token));
        }

        return $token;
    }

    public function checkToken(): object
    {
        $data = $this->validate();

        $result = DB::transaction(function () use ($data) {
            $personalAccessToken = $this->personalAccessTokenModel::query()
                ->where('token', $data->token)
                ->where('expires_at', '>', Carbon::now())
                ->whereNull('last_used_at')
                ->firstOrFail();

            $personalAccessToken->update([
                'last_used_at' => Carbon::now(),
            ]);

            auth()->guard('sanctum')->setUser($personalAccessToken->tokenable);

            $tokens = auth()->user()
                ->tokens()
                ->where('name', 'authentication')
                ->where('expires_at', '<', Carbon::now())
                ->whereNull('last_used_at');

            if ($tokens->exists()) {
                $tokens->delete();
            }

            $token = auth()->user()->createToken(
                name: 'authentication',
                expiresAt: Carbon::now()->addMinutes(config('auth.passwords.expire'))
            );

            return (object) [
                'access_token' => $token->plainTextToken,
                'expires_in'   => config('auth.passwords.expire') * 60,
                'user'         => auth()->user()->load('databasesAccess'),
            ];
        });

        $this->checkIp();

        return $result;
    }

    private function checkIp(): void
    {
        database(refresh: true);

        if (Setting::byName('datahub_access')->isEmpty()) {
            $secret = Str::random(self::SECRET_CHARS);

            Falcon::bigDataService('auth', false)
                ->createUser(
                    new Data([
                        'name'     => auth()->people()->name,
                        'email'    => auth()->user()->email,
                        'password' => $secret,
                    ])
                );

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

        Falcon::bigDataService('ip')?->search(request()->ip());
    }
}
