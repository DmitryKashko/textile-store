<?php

namespace App\Data;

final readonly class PlaceOrderData
{
    /**
     * @param  list<OrderLine>  $lines
     */
    public function __construct(public array $lines)
    {
    }

    /**
     * @param  list<array{product_id: int|string, quantity: int|string}>  $items
     */
    public static function fromArray(array $items): self
    {
        return new self(array_map(
            fn (array $item) => new OrderLine((int) $item['product_id'], (int) $item['quantity']),
            $items,
        ));
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        return array_map(fn (OrderLine $line) => $line->productId, $this->lines);
    }
}
