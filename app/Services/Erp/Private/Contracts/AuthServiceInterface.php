<?php

namespace App\Services\Erp\Private\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthServiceInterface
{
    public function store(): Model;

    public function login(): array;

    public function grantPermission(): bool;

    public function forgot(string $email): bool;

    public function reset(string $token, string $email, string $password): bool;
}
