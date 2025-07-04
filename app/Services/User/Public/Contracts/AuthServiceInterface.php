<?php

namespace App\Services\User\Public\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthServiceInterface
{
    public function store(): Model;

    public function login(): object;

    public function grantPermission(): bool;

    public function forgot(string $email): bool;

    public function reset(string $token, string $email, string $password): bool;
}
