<?php

declare(strict_types = 1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureTelescope();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
    }

    protected function configureTelescope(): void
    {
        if (in_array(config('app.env'), explode(',', (string) config('telescope.enabled_env')), true)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
