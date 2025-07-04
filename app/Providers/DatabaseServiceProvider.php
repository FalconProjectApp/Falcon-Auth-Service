<?php

declare(strict_types = 1);

namespace App\Providers;

use Carbon\Carbon;
use FalconERP\Skeleton\Models\BackOffice\Shop;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\HandlerBasicsExtension\Traits\ApiResponseTrait;

class DatabaseServiceProvider extends ServiceProvider
{
    use ApiResponseTrait;

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $isAuth = match (true) {
            $this->routeIs('/erp/private/') => $this->erpRoute(),
            $this->routeIs('/user/')        => $this->userRoute(),
            $this->routeIs('/erp/public/')  => true,
            $this->routeIs('/backoffice/')  => $this->backofficeRoute(),
            $this->routeIs('/telescope/')   => true,
            default                         => true,
        };

        abort_if(
            false === $isAuth && 'OPTIONS' !== request()->server->get('REQUEST_METHOD'),
            Response::HTTP_UNAUTHORIZED,
            'Não autorizado!'
        );

        return true;
    }

    private function userRoute()
    {
        abort_if(
            !request()->has('shop'),
            Response::HTTP_BAD_REQUEST,
            'Shop não informado!'
        );

        abort_if(
            request()->has('shop') && is_null(request()->shop),
            Response::HTTP_BAD_REQUEST,
            'Shop não informado!'
        );

        $shop = Shop::query()
            ->where('slug', request()->shop)
            ->first();

        abort_if(
            null === $shop,
            Response::HTTP_NOT_FOUND,
            'Loja não encontrada!'
        );

        $shop->update([
            'searched'         => $shop->searched + 1,
            'last_searched_at' => Carbon::now(),
        ]);
        auth()->check();
        auth()->setDatabase($shop->databases);

        return true;
    }

    private function erpRoute()
    {
        if (!auth()->check()) {
            return false;
        }

        auth()->database();

        return true;
    }

    private function backofficeRoute()
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->database();
    }

    private function routeIs(string $route): bool
    {
        $requestUri = request()->server->get('REQUEST_URI');

        return str_starts_with($requestUri, $route);
    }
}
