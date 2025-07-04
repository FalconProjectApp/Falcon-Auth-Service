<?php

namespace App\Services\User\Public\Auth\V1;

use App\Enums\People\PeopleContactEnum;
use App\Enums\People\PeopleDocumentEnum;
use App\Mail\Auth\SendTokenEmail;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\PeopleContact;
use FalconERP\Skeleton\Models\Erp\People\Type;
use FalconERP\Skeleton\Models\PersonalAccessToken;
use FalconERP\Skeleton\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use QuantumTecnology\ServiceBasicsExtension\BaseService;

class AuthService extends BaseService
{
    public const EXPIRE_MINUTES         = 5;
    public const CHARACTERS_NUMBER      = 6;
    protected $model                    = User::class;
    protected $personalAccessTokenModel = PersonalAccessToken::class;
    protected $databaseUserAccessModel  = DatabasesUsersAccess::class;

    public function sendToken()
    {
        $data = !$this->existsData ? $this->validate() : $this->data;

        $user = request()
            ->database
            ->userAccess()
            ->where('email', $data->credential)
            ->firstOrFail();

        $tokens = $user
            ->tokens()
            ->where('name', 'password_less')
            ->where('expires_at', '<', Carbon::now())
            ->whereNull('last_used_at');

        if ($tokens->exists()) {
            $tokens->delete();
        }

        $token = $user
            ->tokens()
            ->create([
                'name'       => 'password_less',
                'token'      => Str::upper(Str::random(self::CHARACTERS_NUMBER)),
                'expires_at' => Carbon::now()
                        ->addMinutes(self::EXPIRE_MINUTES),
            ])->token;

        // if (!auth()->qaTest) {
        Mail::to($user->email)
            ->send(new SendTokenEmail($token));
        // }

        return true;
    }

    public function checkToken()
    {
        $data = !$this->existsData ? $this->validate() : $this->data;

        return DB::transaction(function () use ($data) {
            $personalAccessToken = $this->personalAccessTokenModel::query()
                ->where('token', $data->token)
                ->where('expires_at', '>', Carbon::now())
                ->whereNull('last_used_at')
                ->firstOrFail();

            $personalAccessToken->update([
                'last_used_at' => Carbon::now(),
            ]);

            auth()->setUser($personalAccessToken->tokenable);

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
                'user'         => User::query()
                    ->where('id', auth()->user()->id)
                    // ->with(['people', 'databasesAccess'])
                    ->first(),
            ];
        });
    }

    public function store(): Model
    {
        $data = !$this->existsData ? $this->validate() : $this->data;

        $user = User::query()->where('email', $data->email)->first();

        abort_if(
            $user && $user->databasesAccess()->where('database_id', request()->database->id)->exists(),
            Response::HTTP_BAD_REQUEST,
            'Email já esta em uso.'
        );

        $userCreated = DB::transaction(function () use ($data, $user) {
            $peopleContact = PeopleContact::query()
                ->where('value', $data->email)
                ->first();

            if (!$peopleContact) {
                $people = People::create([
                    'name'         => $data->name,
                    'types_id'     => Type::where('description', 'Funcionario')->first()->id,
                    'display_name' => $data->display_name,
                ]);

                $people->peopleContacts()
                    ->create([
                        'value' => $data->email,
                        'type'  => PeopleContactEnum::TYPE_EMAIL,
                        'main'  => true,
                    ]);

                $people->peopleContacts()
                    ->create([
                        'value' => $data->phone,
                        'type'  => PeopleContactEnum::TYPE_PHONE,
                        'main'  => true,
                    ]);

                $people->peopleDocuments()
                    ->create([
                        'value' => $data->document,
                        'type'  => PeopleDocumentEnum::TYPE_CPF,
                        'main'  => true,
                    ]);
            } else {
                $people = $peopleContact->people;
            }

            if (!$user) {
                $userCreated = request()->database->user()
                    ->create([
                        'email' => $data->email,
                    ]);
            } else {
                $userCreated = $user;
            }

            if ($userCreated->databasesAccess) {
                $this->databaseUserAccessModel::query()
                ->where('user_id', $userCreated->id)
                ->update([
                    'is_active' => false,
                ]);
            }

            $userCreated->databasesAccess()->attach(
                request()->database->id, [
                    'is_active'      => true,
                    'base_people_id' => $people->id,
                ]);

            return $userCreated;
        });

        $this->setData((object) [
            'credential' => $data->email,
        ]);

        $this->sendToken();

        return $userCreated;
    }
}
