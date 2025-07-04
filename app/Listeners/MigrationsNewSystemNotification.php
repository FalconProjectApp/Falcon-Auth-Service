<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\CreatingDatabase;
use FalconERP\Skeleton\Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class MigrationsNewSystemNotification // implements ShouldQueueAfterCommit
{
    // use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CreatingDatabase $event): void
    {
        Config::set(
            'database.connections.pgsql_bases.database',
            $event->model->base
        );

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/finance'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/fiscal'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/people'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/service'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/shop'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
        ]);

        Artisan::call('migrate', [
            '--force'          => true,
            '--path'           => realpath(__DIR__ . '/../../vendor/falconerp/skeleton/database/migrations/stock'),
            '--database'       => 'pgsql_bases',
            '--realpath'       => true,
            '--no-interaction' => true,
            '--seed'           => true,
        ]);

        Artisan::call('db:seed', [
            '--class'    => DatabaseSeeder::class,
            '--force'    => true,
            '--database' => 'pgsql_bases',
        ]);
    }
}
