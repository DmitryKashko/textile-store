<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepository
{
    public function create(User $user, array $items, int $total): Order
    {
        $order = $user->orders()->create(['total' => $total]);
        $order->items()->createMany($items);

        return $order->load('items');
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->orders()
            ->with('items')
            ->latest()
            ->latest('id')
            ->paginate($perPage);
    }
}
