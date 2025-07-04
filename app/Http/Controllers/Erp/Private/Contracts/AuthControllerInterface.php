<?php

namespace App\Http\Controllers\Erp\Private\Contracts;

use Illuminate\Http\JsonResponse;

interface AuthControllerInterface
{
    public function me(): JsonResponse;

    public function logout(): JsonResponse;

    public function refresh(): JsonResponse;
}
