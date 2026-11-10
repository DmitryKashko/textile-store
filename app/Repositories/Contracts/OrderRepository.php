<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepository
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function create(User $user, array $items, int $total): Order;

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator;
}
