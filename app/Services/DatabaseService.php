<?php

namespace App\Services;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\PgDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\ServiceBasicsExtension\BaseService;
use Telegram\Bot\Api;

class DatabaseService extends BaseService
{
    public function createDatabase(): Model
    {
        $this->validate($this->data, 'store');

        /*
         * Checa se a base ja existe.
         */
        if (!is_null(PgDatabase::byDatname($this->data['base'])->first())) {
            $this->notFoundResponse("Base '{$this->data['base']}' already exists");
        }

        PgDatabase::createDatabase($this->data['base']);

        $stored = DB::transaction(function () {
            Config::set(
                'database.connections.pgsql_bases.database',
                "bc_{$this->data['base']}"
            );

            Config::set(
                'database.default',
                'pgsql_bases'
            );

            $people = People::create([
                'name'     => $this->data['user']['name'],
                'types_id' => 0,
                'cnpj_cpf' => '00000000000',
            ]);

            $this->data['user']['base_people_id'] = $people->id;

            /*
             *  Transformando o password em Hash.
             */
            $this->data['user']['password'] = password_hash($this->data['user']['password'], PASSWORD_DEFAULT);

            Config::set(
                'database.default',
                'pgsql'
            );

            $callback = $this->defaultModel->create($this->data);
            $callback->user()->create($this->data['user']);

            return $callback->refresh();
        });

        (new Api())->sendMessage([
            'chat_id'    => config('telegram.chats.falcon'),
            'text'       => "<span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span>\n<span class='tg-spoiler'>spoiler</span>Nova base criada***\n<span class='tg-spoiler'>spoiler</span>Nome: <b>{$stored->base}</b>\n<span class='tg-spoiler'>spoiler</span>Usuario: <b>{$stored->user->name}</b>\n<span class='tg-spoiler'>spoiler</span>Email: <b>{$stored->user->email}</b>\n<span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span><span class='tg-spoiler'>spoiler</span>",
            'parse_mode' => 'HTML',
        ]);

        return $stored;
    }
}
