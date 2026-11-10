<?php

namespace App\Data;

final readonly class OrderLine
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
    }
}
