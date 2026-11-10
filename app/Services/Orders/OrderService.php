<?php

namespace App\Services\Orders;

use App\Data\PlaceOrderData;
use App\Events\OrderPlaced;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepository;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly OrderRepository $orders,
        private readonly PriceCalculator $prices,
    ) {
    }

    public function place(User $user, PlaceOrderData $data): Order
    {
        // retried on deadlock; OrderPlaced is dispatched only after commit
        return DB::transaction(function () use ($user, $data) {
            $products = $this->products->lockForUpdate($data->productIds());

            $items = [];
            $total = 0;

            foreach ($data->lines as $line) {
                $product = $products->get($line->productId);

                if ($product === null || ! $product->is_active) {
                    throw new ProductUnavailableException($line->productId);
                }

                if ($product->stock < $line->quantity) {
                    throw new InsufficientStockException($product, $line->quantity);
                }

                $lineTotal = $this->prices->lineTotal($product->price, $line->quantity);
                $total += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $line->quantity,
                    'discount_percent' => $this->prices->discountPercent($line->quantity),
                    'line_total' => $lineTotal,
                ];
            }

            $order = $this->orders->create($user, $items, $total);

            foreach ($data->lines as $line) {
                $this->products->decrementStock($products[$line->productId], $line->quantity);
            }

            OrderPlaced::dispatch($order);

            return $order;
        }, attempts: 3);
    }

    public function listFor(User $user): LengthAwarePaginator
    {
        return $this->orders->paginateForUser($user);
    }
}
