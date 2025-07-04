<?php

declare(strict_types = 1);

namespace App\Providers;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'FalconERP\Skeleton\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::macro('setDatabase', function (
            Database $database,
        ) {
            Config::set(
                'database.connections.pgsql_bases.database',
                sprintf('bc_%s', $database->base)
            );

            Config::set(
                'database.default',
                'pgsql_bases'
            );

            request()->merge([
                'database' => $database,
            ]);
        });

        Auth::macro('database', function (
            bool $active = true,
            bool $refresh = false,
        ) {
            /*
             * Se o usuário estiver logado, então ele tem acesso a um ou mais bancos de dados.
             */
            if (static::check()) {
                $database = Database::byActiveAndUser($active, static::user())->get();

                if (0 === $database->count() && $database = Database::byActiveAndUser(!$active, static::user())->get()) {
                    $databasesUsersAccess            = $database->first()->databasesUsersAccess->first();
                    $databasesUsersAccess->is_active = true;
                    $databasesUsersAccess->save();
                }

                if ($database->count() > 1) {
                    return $database;
                }

                if (0 === $database->count()) {
                    $database = false;
                }

                if (1 === $database->count()) {
                    $database = $database->first();
                }
            }

            if ($refresh || (isset($database) && !request()->database)) {
                static::setDatabase($database);
            }

            return $database ?? false;
        });

        Auth::macro('people', function (
            bool $active = true,
            bool $refresh = false,
        ) {
            return People::find(
                static::database(
                    active: $active,
                    refresh: $refresh
                )->databasesUsersAccess->first()->base_people_id
            );
        });

        /*
         * Credentials
         */
        Auth::macro('is_master', function () {
            return static::check() ? static::user()->is_master : false;
        });

        Auth::macro('level', function () {
            return database(active: true)->databaseGroup->slug;
        });
    }
}
