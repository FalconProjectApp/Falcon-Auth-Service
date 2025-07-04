<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseServiceInterface
{
    public function index(): LengthAwarePaginator|Collection;

    public function show(int $id): Model;

    public function store(): Model;

    public function update(int $id): Model;

    public function destroy(int $id): bool;

    public function restore(int $id): bool;
}
